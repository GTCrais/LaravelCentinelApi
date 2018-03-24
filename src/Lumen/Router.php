<?php

namespace GTCrais\LaravelCentinelApi\Lumen;


class Router
{
	public static function getRouter()
	{
		$availableBindings = app()->availableBindings;

		if (array_key_exists('router', $availableBindings)) {
			return app()->router;
		}

		return app();
	}
}