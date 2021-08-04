<?php
/**
 * Exporter Class.
 */

namespace OpenLab\ImportExport\Export;

use WP_Error;
use ZipArchive;
use OpenLab\ImportExport\Iterator\UploadsIterator;

class Exporter {

	/**
	 * Files to export.
	 *
	 * @var array
	 */
	protected $files = [];

	/**
	 * Post types and their options.
	 *
	 * @var array
	 */
	protected $post_types = [];

	/**
	 * Custom text to be appended to the readme file.
	 *
	 * @var string
	 */
	protected $readme_custom_text;

	/**
	 * Custom text for the auto-generated Acknowledgements page.
	 *
	 * @var string
	 */
	protected $acknowledgements_text;

	/**
	 * ID of the auto-generated Acknowledgements page.
	 *
	 * @var int
	 */
	protected $acknowledgements_page_id;

	/**
	 * Text of the readme file.
	 *
	 * @var string
	 */
	protected $readme_text;

	/**
	 * Cached value of `wp_upload_dir()`.
	 *
	 * @var array
	 */
	public $uploads_dir = [];

	/**
	 * Exports directory.
	 *
	 * @var string
	 */
	public $exports_dir;

	/**
	 * Exports URL
	 *
	 * @var string
	 */
	public $exports_url;

	/**
	 * Create export object.
	 *
	 * @param array $upload_dir
	 */
	public function __construct( array $upload_dir ) {
		$this->uploads_dir = $upload_dir;
		$this->exports_dir = trailingslashit( $upload_dir['basedir'] ) . 'ol-portfolio-exports/';
		$this->exports_url = trailingslashit( $upload_dir['baseurl'] ) . 'ol-portfolio-exports/';
	}

	/**
	 * Start export process.
	 *
	 * @return \WP_Error|string
	 */
	public function run() {
		$dest = $this->create_dest();

		if ( is_wp_error( $dest ) ) {
			return $dest;
		}

		$this->create_acknowledgements_page();

		$export = $this->create_wxp();
		if ( is_wp_error( $export ) ) {
			return $export;
		}

		$this->delete_acknowledgements_page();

		$this->prepare_files( $this->uploads_dir['basedir'] );

		$this->prepare_readme();

		return $this->archive();
	}

	/**
	 * Adds a post type to the list of those to be exported.
	 *
	 * @param string $post_type Post type.
	 * @param array  $options
	 * @return void
	 */
	public function add_post_type( $post_type, $options = [] ) {
		$this->post_types[ $post_type ] = $options;
	}

	/**
	 * Adds custom text for the end of the readme file.
	 *
	 * @param string
	 * @return void
	 */
	public function add_readme_custom_text( $text ) {
		$this->readme_custom_text = $text;
	}

	/**
	 * Adds acknowledgements text for the auto-generated Acknowledgements page.
	 *
	 * @param string
	 * @return void
	 */
	public function add_acknowledgements_text( $text ) {
		$this->acknowledgements_text = $text;
	}

	/**
	 * Create export destination.
	 *
	 * @return \WP_Error|bool
	 */
	protected function create_dest() {
		if ( ! wp_mkdir_p( $this->exports_dir ) ) {
			return new WP_Error( 'ol.exporter.create.dest', 'Unable to create export folder.' );
		}

		return true;
	}

	/**
	 * Creates an Acknowledgements page to be included in the export.
	 *
	 * @since 1.0.0
	 *
	 * @return null
	 */
	protected function create_acknowledgements_page() {
		if ( empty( $this->acknowledgements_text ) ) {
			return;
		}

		$post_id = wp_insert_post(
			[
				'post_type'    => 'page',
				'post_name'    => 'acknowledgements',
				'post_status'  => 'publish',
				'post_title'   => __( 'Acknowledgements', 'openlab-import-export' ),
				'post_content' => $this->acknowledgements_text,
			]
		);

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			return;
		}

		$this->acknowledgements_page_id = $post_id;
	}

	/**
	 * Deletes the auto-generated Acknowledgements page.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	protected function delete_acknowledgements_page() {
		if ( empty( $this->acknowledgements_page_id ) ) {
			return;
		}

		wp_delete_post( $this->acknowledgements_page_id, true );
	}

	/**
	 * Prepare backups files. Image uploads, etc.
	 *
	 * @return \WP_Error|void
	 */
	protected function prepare_files( $folder ) {
		$folder = trailingslashit( $folder );

		if ( ! is_dir( $folder ) ) {
			return new WP_Error(
				'ol.exporter.prepare.files',
				sprintf( 'Folder %s does not exist.', $folder )
			);
		}

		if ( ! is_readable( $folder ) ) {
			return new WP_Error(
				'ol.exporter.prepare.files',
				sprintf( 'Folder %s is not readable.', $folder )
			);
		}

		try {
			$iterator = UploadsIterator::create( $folder );

			foreach ( $iterator as $file ) {
				$this->files[] = $file->getPathname();
			}
		} catch ( UnexpectedValueException $e ) {
			return new WP_Error(
				'ol.exporter.prepare.files',
				sprintf( 'Could not open path: %', $e->getMessage() )
			);
		}
	}

	/**
	 * Generates the text of the readme file.
	 *
	 * @return void
	 */
	protected function prepare_readme() {
		$admin_names = \OpenLab\ImportExport\get_site_admin_names();

		$text = esc_html__( 'Acknowledgements', 'openlab-import-export' );

		$converter = new \League\HTMLToMarkdown\HtmlConverter();

		$text .= "\n\n";
		$text .= $converter->convert( $this->acknowledgements_text );

		if ( ! empty( $this->readme_custom_text ) ) {
			$text .= "\n\n";
			$text .= '# ' . esc_html__( 'Note from Exporter', 'openlab-import-export' );
			$text .= "\n\n";
			$text .= $this->readme_custom_text;
		};

		$text .= "\n\n";

		$text .= '# ' . esc_html__( 'Theme and Plugins', 'openlab-import-export' );
		$text .= "\n\n";

		$text .= esc_html__( 'To best replicate the setup of that site, and to include any content types associated with its theme or plugins, activate the following theme and plugins on your site:', 'openlab-import-export' );

		$active_theme = wp_get_theme( get_stylesheet() );

		$theme_uri = $this->get_theme_uri( get_stylesheet() );
		if ( ! $theme_uri ) {
			$theme_uri = $active_theme->get( 'ThemeURI' );
		}

		$text .= "\n\n";
		$text .= sprintf(
			'* %s: %s',
			esc_html( $active_theme->name ),
			esc_html( $theme_uri )
		);
		$text .= "\n\n";

		$all_plugins = get_plugins();
		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			if ( ! is_plugin_active( $plugin_file ) ) {
				continue;
			}

			if ( is_plugin_active_for_network( $plugin_file ) ) {
				continue;
			}

			$plugin_uri = $this->get_plugin_uri( $plugin_file );
			if ( ! $plugin_uri && ! empty( $plugin_data['PluginURI'] ) ) {
				$plugin_uri = $plugin_data['PluginURI'];
			}

			if ( ! empty( $plugin_uri ) ) {
				$text .= sprintf(
					'* %s: %s',
					esc_html( $plugin_data['Name'] ),
					esc_html( $plugin_uri )
				);
			} else {
				$text .= sprintf(
					'* %s',
					esc_html( $plugin_data['Name'] ),
				);
			}
			$text .= "\n";
		}

		$this->readme_text = $text;
	}

	/**
	 * Gets a wordpress.org download URI for a plugin file.
	 *
	 * @param string $plugin_file
	 */
	protected function get_plugin_uri( $plugin_file ) {
		$pf_parts    = explode( '/', $plugin_file );
		$plugin_slug = $pf_parts[0];

		return $this->get_download_uri( $plugin_slug, 'plugins' );
	}

	/**
	 * Gets a wordpress.org download URI for a theme.
	 *
	 * @param string $theme
	 */
	protected function get_theme_uri( $theme ) {
		return $this->get_download_uri( $theme, 'themes' );
	}

	/**
	 * Gets a wordpress.org download URI for a theme or plugin.
	 *
	 * @param string $slug
	 * @param string $type 'plugins' or 'themes'.
	 */
	protected function get_download_uri( $slug, $type ) {
		$cached = get_transient( 'download_uri_' . $slug );
		if ( $cached ) {
			return $cached;
		}

		if ( ! in_array( $type, [ 'plugins', 'themes' ], true ) ) {
			return '';
		}

		$response = wp_remote_post(
			"http://api.wordpress.org/$type/info/1.0/$slug.xml",
			[
				'body' => [
					'action' => 'plugins' === $type ? 'plugin_information' : 'theme_information',
				],
			]
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$link = '';
		} else {
			$link = "https://wordpress.org/$type/$slug";
		}

		set_transient( 'download_uri_' . $slug, $link, DAY_IN_SECONDS );

		return $link;
	}

	/**
	 * Create export WXP.
	 *
	 * @return \WP_Error|bool
	 */
	protected function create_wxp() {
		$wxp = new WXP( $this->exports_dir . 'wordpress.xml' );

		$wxp->set_post_types( $this->post_types );
		$wxp->set_acknowledgements_page_id( $this->acknowledgements_page_id );

		if ( ! $wxp->create() ) {
			return new WP_Error(
				'ol.exporter.create.wxp',
				'Unable to create WXP export file.'
			);
		}

		return true;
	}

	/**
	 * Save export files into archive.
	 *
	 * @return \WP_Error|string
	 */
	protected function archive() {
		if ( ! class_exists( 'ZipArchive' ) ) {
			return new WP_Error(
				'ol.exporter.archive',
				'Unable to generate export file. ZipArchive not available.'
			);
		}

		$archive_filename = $this->filename();
		$archive_pathname = $this->exports_dir . $archive_filename;

		if ( file_exists( $archive_pathname ) ) {
			wp_delete_file( $archive_pathname );
		}

		$zip = new ZipArchive;
		if ( true !== $zip->open( $archive_pathname, ZipArchive::CREATE ) ) {
			return new WP_Error(
				'ol.exporter.archive',
				'Unable to add data to export file.'
			);
		}

		$zip->addFile( $this->exports_dir . 'wordpress.xml', 'wordpress.xml' );

		foreach ( $this->files as $file ) {
			$zip->addFile( $file, $this->normalize_path( $file ) );
		}

		$readme_pathname = $this->exports_dir . 'readme.md';
		file_put_contents( $readme_pathname, $this->readme_text );
		$zip->addFile( $readme_pathname, 'readme.md' );

		$zip->close();

		// Remove export file.
		unlink( $this->exports_dir . 'wordpress.xml' );

		return $archive_pathname;
	}

	/**
	 * Generate export filename.
	 *
	 * @return string $filename
	 */
	protected function filename() {
		$stripped_url = sanitize_title_with_dashes( get_bloginfo( 'name' ) );
		$timestamp    = date( 'Y-m-d' );
		$filename     = "export-{$stripped_url}-{$timestamp}.zip";

		return $filename;
	}

	/**
	 * Change file path for better storing in archive.
	 *
	 * @param string $file
	 * @return string
	 */
	protected function normalize_path( $file ) {
		$abs_path = realpath( ABSPATH );
		$abs_path = trailingslashit( str_replace( '\\', '/', $abs_path ) );

		return str_replace( [ '\\', $abs_path ], '/', $file );
	}
}
