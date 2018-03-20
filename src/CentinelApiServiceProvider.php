<?php

namespace GTCrais\LaravelCentinelApi;

use Illuminate\Support\ServiceProvider;

class CentinelApiServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
		$this->package('gtcrais/laravel-centinel-api', 'laravel-centinel-api', __DIR__);

		include __DIR__.'/routes/centinelapi.php';
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
		$this->commands([
			'GTCrais\LaravelCentinelApi\Console\Commands\Setup',
			'GTCrais\LaravelCentinelApi\Console\Commands\CheckZipLibraries',
		]);
    }

	public function provides()
	{
		return [];
	}

	/**
	 * Register the package's component namespaces.
	 *
	 * @param  string  $package
	 * @param  string  $namespace
	 * @param  string  $path
	 * @return void
	 */
	public function package($package, $namespace = null, $path = null)
	{
		if (method_exists($this->app['config'], 'package')) {
			$this->app['config']->package($package, $path.'/config', $namespace);
		}
	}
}
