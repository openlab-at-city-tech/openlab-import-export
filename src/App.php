<?php

namespace OpenLab\ImportExport;

use OpenLab\ImportExport\Contracts\Registerable;
use OpenLab\ImportExport\Export\Service as ExportService;
use OpenLab\ImportExport\Import\Service as ImportService;

final class App {
	const SERVICE_PROVIDERS = [
		ExportService::class,
		ImportService::class,
	];

	public static function init() {
		static $instance = null;

		if ( null === $instance ) {
			$instance = new self();
			$instance->register_services();
		}

		return $instance;
	}

	/**
	 * Register the individual services of this plugin.
	 *
	 * @return void
	 */
	protected function register_services() {
		$services = array_map( [ $this, 'init_services' ], self::SERVICE_PROVIDERS );

		array_walk( $services, function ( Registerable $service ) {
			$service->register();
		} );
	}

	/**
	 * Instantiate a single service.
	 *
	 * @param object $service
	 * @return object
	 */
	protected function init_services( $service ) {
		return new $service;
	}
}
