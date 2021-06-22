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
		add_action( 'admin_post_export-portfolio', [ $this, 'handle' ] );
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
		wp_enqueue_style( 'openlab-import-export-export', plugin_dir_url( ROOT_FILE ) . 'assets/css/export.css' );
		wp_enqueue_script( 'openlab-import-export-export', plugin_dir_url( ROOT_FILE ) . 'assets/js/export.js', [ 'jquery' ], false, true );
	}

	/**
	 * Generate export file for downloading.
	 *
	 * @return void
	 */
	public function handle() {
		check_admin_referer( 'ol-export-portfolio' );

		$exporter = new Exporter( wp_get_upload_dir() );
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
