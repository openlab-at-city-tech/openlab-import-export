<?php

/**
 * Plugin name: OpenLab Import-Export
 * Description: Site import-export for the OpenLab at City Tech
 * Version: 1.0
 */

namespace OpenLab\ImportExport;

const ROOT_DIR  = __DIR__;
const ROOT_FILE = __FILE__;

require 'vendor/autoload.php';

spl_autoload_register(
	function( $class ) {
		$prefix   = 'OpenLab\\ImportExport\\';
		$base_dir = __DIR__ . '/src/';

		// Does the class use the namespace prefix?
		$len = strlen( $prefix );
		if ( strncmp( $prefix, $class, $len ) !== 0 ) {
			return;
		}

		// Get the relative class name.
		$relative_class = substr( $class, $len );

		// Swap directory separators and namespace to create filename.
		$file = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

		// If the file exists, require it.
		if ( file_exists( $file ) ) {
			require $file;
		}
	}
);

add_action( 'init', [ '\OpenLab\ImportExport\App', 'init' ] );
