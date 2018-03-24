<?php

$router = \GTCrais\LaravelCentinelApi\Lumen\Router::getRouter();

$router->group([
	'prefix' => config('centinelApi.routePrefix'),
	'namespace' => 'GTCrais\LaravelCentinelApi\Controllers',
	'middleware' => GTCrais\LaravelCentinelApi\Middleware\AuthorizeCentinelApiRequest::class
], function() use ($router) {
	$router->post('create-log', ['as' => 'centinelApiLogCreate', 'uses' => 'CentinelApiController@createLog']);
	$router->post('download-log', ['as' => 'centinelApiLogDownload', 'uses' => 'CentinelApiController@downloadLog']);
	$router->post('dump-database', ['as' => 'centinelApiDatabaseDump', 'uses' => 'CentinelApiController@dumpDatabase']);
	$router->post('download-database', ['as' => 'centinelApiDatabaseDownload', 'uses' => 'CentinelApiController@downloadDatabase']);
});
