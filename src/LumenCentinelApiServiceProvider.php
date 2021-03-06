<?php

namespace GTCrais\LaravelCentinelApi;

use GTCrais\LaravelCentinelApi\Classes\Platform;
use Illuminate\Support\ServiceProvider;

class LumenCentinelApiServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
		$this->loadRoutes();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
		Platform::setPlatform('lumen');

		$this->commands([
			'GTCrais\LaravelCentinelApi\Console\Commands\Setup',
			'GTCrais\LaravelCentinelApi\Console\Commands\CheckZipLibraries',
		]);
    }

	protected function loadRoutes()
	{
		include __DIR__ . '/routes/lumenRoutes.php';
	}
}
