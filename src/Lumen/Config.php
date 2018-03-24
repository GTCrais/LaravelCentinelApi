<?php

namespace GTCrais\LaravelCentinelApi\Lumen;

class Config
{
	public static function getDatabaseConnectionConfig()
	{
		// Try to get config from /config/database.php
		// config file, if it exists
		$databaseConfig = config('database');

		if ($databaseConfig) {
			$connectionName = config('centinelApi.database.connection');
			$connectionName = ($connectionName == '{default}') ? config('database.default') : $connectionName;

			if ($connectionName && config('database.connections.' . $connectionName)) {
				return config('database.connections.' . $connectionName);
			}
		}

		// Try to get config from .env
		$connection = [
			'driver' => env('DB_CONNECTION'),
			'host' => env('DB_HOST'),
			'port' => env('DB_PORT'),
			'database' => env('DB_DATABASE'),
			'username' => env('DB_USERNAME'),
			'password' => env('DB_PASSWORD'),
		];

		return self::validateConnectionData($connection) ? $connection : null;
	}

	protected static function validateConnectionData($connection)
	{
		if (
			$connection['driver'] &&
			$connection['host'] &&
			$connection['database'] &&
			$connection['username']
		) {
			return true;
		}

		return false;
	}
}