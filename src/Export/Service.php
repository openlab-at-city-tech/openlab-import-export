<?php
/**
 * Export settings.
 */

namespace OpenLab\ImportExport\Export;

use const OpenLab\ImportExport\ROOT_DIR;
use const OpenLab\ImportExport\ROOT_FILE;

use OpenLab\ImportExport\Contracts\Registerable;

class Service implements Registerable {

	/**
	 * Register our settings page.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_menu', [ $this, 'register_page' ] );
		add_action( 'admin_print_scripts-tools_page_openlab_site_export', [ $this, 'enqueue_assets' ] );
		add_action( 'admin_post_export-site', [ $this, 'handle' ] );
	}

	/**
	 * Register custom tools page.
	 *
	 * @return void
	 */
	public function register_page() {
		add_submenu_page(
			'tools.php',
			'OpenLab Export',
			'OpenLab Export',
			'export',
			'openlab_site_export',
			[ $this, 'render' ]
		);
	}

	/**
	 * Enqueues assets.
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		wp_register_style( 'openlab-import-export-select2', plugin_dir_url( ROOT_FILE ) . '/vendor/select2/select2/dist/css/select2.min.css' );
		wp_register_script( 'openlab-import-export-select2', plugin_dir_url( ROOT_FILE ) . '/vendor/select2/select2/dist/js/select2.min.js', [ 'jquery' ], false, true );

		wp_enqueue_style( 'openlab-import-export-export', plugin_dir_url( ROOT_FILE ) . 'assets/css/export.css', [ 'openlab-import-export-select2' ] );
		wp_enqueue_script( 'openlab-import-export-export', plugin_dir_url( ROOT_FILE ) . 'assets/js/export.js', [ 'jquery', 'openlab-import-export-select2' ], false, true );
	}

	/**
	 * Generate export file for downloading.
	 *
	 * @return void
	 */
	public function handle() {
		check_admin_referer( 'ol-export-site' );

		$exporter = new Exporter( wp_get_upload_dir() );

		// Determine whether to include attachments.
		$include_attachments = ! empty( $_POST['submit-with-attachments'] );
		$exporter->include_attachments( $include_attachments );

		// 'all' falls through and is handled in WXP.
		if ( isset( $_POST['post-types'] ) ) {
			$post_types = wp_unslash( $_POST['post-types'] );
		} else {
			$post_types = [ 'all' ];
		}

		if ( ! in_array( 'all', $post_types, true ) ) {
			foreach ( $post_types as $post_type ) {
				$options = [];

				if ( ! empty( $_POST[ $post_type . '_status' ] ) ) {
					$options['status'] = sanitize_text_field( wp_unslash( $_POST[ $post_type . '_status' ] ) );
				}

				if ( ! empty( $_POST[ $post_type . '_start_date' ] ) ) {
					$options['start_date'] = sanitize_text_field( wp_unslash( $_POST[ $post_type . '_start_date' ] ) );
				}

				if ( ! empty( $_POST[ $post_type . '_end_date' ] ) ) {
					$options['end_date'] = sanitize_text_field( wp_unslash( $_POST[ $post_type . '_end_date' ] ) );
				}

				if ( ! empty( $_POST[ $post_type . '_author' ] ) ) {
					$options['author'] = array_map( 'intval', wp_unslash( $_POST[ $post_type . '_author' ] ) );
				}

				$exporter->add_post_type( $post_type, $options );
			}
		}

		if ( ! empty( $_POST['readme-additional-text'] ) ) {
			$additional_text = sanitize_textarea_field( wp_unslash( $_POST['readme-additional-text'] ) );
			$exporter->add_readme_custom_text( $additional_text );
		}

		if ( ! empty( $_POST['acknowledgements-text'] ) ) {
			$acknowledgements_text = wp_kses_post( wp_unslash( $_POST['acknowledgements-text'] ) );
			$exporter->add_acknowledgements_text( $acknowledgements_text );
		}

		wp_ob_end_flush_all();
		$filename = $exporter->run();

		if ( is_wp_error( $filename ) ) {
			add_settings_error(
				'failed_export',
				'failed_export',
				$filename->get_error_message()
			);

			wp_safe_redirect( wp_get_referer() );
		}

		header('Content-type: application/zip');
		header('Content-Disposition: attachment; filename="' . basename( $filename ) . '"');
		header('Content-length: ' . filesize( $filename ) );
		readfile( $filename );

		// Remove file.
		unlink( $filename );

		exit;
	}

	/**
	 * Render export page.
	 *
	 * @return void
	 */
	public function render() {
		require ROOT_DIR . '/views/export/export.php';
	}

	/**
	 * Render view part.
	 *
	 * @param string $part Path to the view, relative to the views directory.
	 * @param array  $args Arguments to make available to the template.
	 *
	 * @return void
	 */
	public static function render_view_part( $part, $args = [] ) {
		require ROOT_DIR . '/views/' . $part;
	}
}
