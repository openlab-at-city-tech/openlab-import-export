<?php

namespace OpenLab\ImportExport\Contracts;

interface Registerable {
	/**
	 * Register the service.
	 *
	 * @return void
	 */
	public function register();
}
