<?php

namespace GTCrais\LaravelCentinelApi\Classes;


class Platform
{
	protected static $platform;

	public static function getPlatform()
	{
		return self::$platform;
	}

	public static function setPlatform($platform)
	{
		self::$platform = $platform;
	}

	public static function getPlatformVersion()
	{
		preg_match('/\d+\.\d+(\.\d+)?/', app()->version(), $matches);

		return (is_array($matches) && isset($matches[0])) ? $matches[0] : 0;
	}

	public static function getLogFilename()
	{
		return self::getPlatform() == 'laravel' ? 'laravel.log' : 'lumen.log';
	}
}