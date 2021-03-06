<?php

namespace GTCrais\LaravelCentinelApi;

use GTCrais\LaravelCentinelApi\Classes\Platform;
use Illuminate\Support\ServiceProvider;

class LaravelCentinelApiServiceProvider extends ServiceProvider
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
		Platform::setPlatform('laravel');

		$this->commands([
			'GTCrais\LaravelCentinelApi\Console\Commands\Setup',
			'GTCrais\LaravelCentinelApi\Console\Commands\CheckZipLibraries',
		]);
    }

	protected function loadRoutes()
	{
		$routePath = __DIR__ . '/routes/laravelRoutes.php';

		if (method_exists($this, 'loadRoutesFrom')) {
			$this->loadRoutesFrom($routePath);
		} else {
			include $routePath;
		}
	}
}
