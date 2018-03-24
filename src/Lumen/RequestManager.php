<?php

namespace GTCrais\LaravelCentinelApi\Lumen;

use Illuminate\Http\Request;

class RequestManager
{
	public static function getRouteName(Request $request)
	{
		list($found, $routeInfo, $params) = $request->route() ?: [false, [], []];

		return !empty($routeInfo['as']) ? $routeInfo['as'] : 'unknown-centinel-api-route';
	}
}