<?php

Route::filter('centinelMiddleware', 'GTCrais\LaravelCentinelApi\Middleware\AuthorizeCentinelApiRequest');

Route::group([
	'prefix' => Config::get('laravel-centinel-api::routePrefix'),
	'namespace' => 'GTCrais\LaravelCentinelApi\Controllers',
	'before' => 'centinelMiddleware'
], function() {
	Route::post('create-log', ['as' => 'centinelApiLogCreate', 'uses' => 'CentinelApiController@createLog']);
	Route::post('download-log', ['as' => 'centinelApiLogDownload', 'uses' => 'CentinelApiController@downloadLog']);
	Route::post('dump-database', ['as' => 'centinelApiDatabaseDump', 'uses' => 'CentinelApiController@dumpDatabase']);
	Route::post('download-database', ['as' => 'centinelApiDatabaseDownload', 'uses' => 'CentinelApiController@downloadDatabase']);
});
