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
		include __DIR__ . '/routes/lumenRoutes.php';
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
}
