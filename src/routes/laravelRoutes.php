<?php

Route::group([
	'prefix' => config('centinelApi.routePrefix'),
	'namespace' => 'GTCrais\LaravelCentinelApi\Controllers',
	'middleware' => GTCrais\LaravelCentinelApi\Middleware\AuthorizeCentinelApiRequest::class
], function() {
	Route::post('create-log', ['as' => 'centinelApiLogCreate', 'uses' => 'CentinelApiController@createLog']);
	Route::post('download-log', ['as' => 'centinelApiLogDownload', 'uses' => 'CentinelApiController@downloadLog']);
	Route::post('dump-database', ['as' => 'centinelApiDatabaseDump', 'uses' => 'CentinelApiController@dumpDatabase']);
	Route::post('download-database', ['as' => 'centinelApiDatabaseDownload', 'uses' => 'CentinelApiController@downloadDatabase']);
});
