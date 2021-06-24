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

		$export = $this->create_wxp();
		if ( is_wp_error( $export ) ) {
			return $export;
		}

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
		$admin_names = array_map(
			function( $user ) {
				return $user->display_name;
			},
			get_users( [ 'role' => 'administrator' ] )
		);

		$text = sprintf(
			// translators: 1. Site name; 2. Site URL; 3. List of admin names
			esc_html__( 'The source site for this export is: %1$s: %2$s created by %3$s', 'openlab-import-export' ),
			get_option( 'blogname' ),
			get_option( 'home' ),
			implode( ', ', $admin_names )
		);

		$text .= "\n\n";
		$text .= '# ' . esc_html__( 'Acknowledgements', 'openlab-import-export' );
		$text .= "\n\n";

		$source_site_name        = 'SOURCE SITE NAME';
		$source_site_url         = 'SOURCE SITE URL';
		$source_site_admin_names = 'SOURCE SITE ADMIN NAMES';

		$text .= sprintf(
			esc_html__( 'This site is based on [%s] (%s) by %s.

Please be sure to display this information somewhere on your site.', 'openlab-import-export' ),
			esc_html( $source_site_name ),
			esc_html( $source_site_url ),
			esc_html( $source_site_admin_names )
		);

		$text .= "\n\n";
		$text .= '# ' . esc_html__( 'Themes and Plugins', 'openlab-import-export' );
		$text .= "\n\n";

		$text .= esc_html__( 'To best replicate the setup of that site, and to include any content types associated with its themes or plugins, activate the following themes and plugins on your site:', 'openlab-import-export' );

		$active_theme = wp_get_theme( get_stylesheet() );

		$text .= "\n\n";
		$text .= sprintf(
			'* %s: %s',
			esc_html( $active_theme->name ),
			esc_html( $active_theme->get( 'ThemeURI' ) )
		);
		$text .= "\n\n";

		$all_plugins = get_plugins();
		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			if ( ! is_plugin_active( $plugin_file ) ) {
				continue;
			}

			if ( ! empty( $plugin_data['PluginURI'] ) ) {
				$text .= sprintf(
					'* %s: %s',
					esc_html( $plugin_data['Name'] ),
					esc_html( $plugin_data['PluginURI'] )
				);
			} else {
				$text .= sprintf(
					'* %s',
					esc_html( $plugin_data['Name'] ),
				);
			}
			$text .= "\n";
		}

		if ( ! empty( $this->readme_custom_text ) ) {
			$text .= "\n";
			$text .= $this->readme_custom_text;
		};

		$this->readme_text = $text;
	}

	/**
	 * Create export WXP.
	 *
	 * @return \WP_Error|bool
	 */
	protected function create_wxp() {
		$wxp = new WXP( $this->exports_dir . 'wordpress.xml' );

		$wxp->set_post_types( $this->post_types );

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
