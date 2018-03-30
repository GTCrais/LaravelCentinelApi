<?php

namespace GTCrais\LaravelCentinelApi\Classes;

use Spatie\DbDumper\Databases\PostgreSql;

class Database
{
	public static function dump()
	{
		$connectionName = \Config::get('laravel-centinel-api::database.connection');

		if ($connectionName == '{default}') {
			$connectionName = \Config::get('database.default');
		}

		$connection = \Config::get('database.connections.' . $connectionName);

		if (!$connection) {
			throw new \Exception("Connection '" . $connectionName . "' does not exist.");
		}

		$driver = $connection['driver'];

		if ($driver == 'mysql') {
			return self::dumpMySql($connection);
		} else if ($driver == 'pgsql') {
			return self::dumpPostgreSql($connection);
		} else {
			throw new \Exception("Unsupported driver: " . $driver);
		}
	}

	public static function getDumpPath($filename = null)
	{
		$path = storage_path('databasedump');

		if ($filename) {
			$path .= '/' . $filename;
		}

		return $path;
	}

	protected static function dumpMySql($connection)
	{
		$filename = 'databasedump.sql';
		$fullPath = self::getDumpPath($filename);

		/** @var MySql $dumper */
		$dumper = MySql::create();

		if (self::optionIsSet('port')) {
			$dumper = $dumper->setPort(self::getOption('port'));
		}

		if (self::optionIsSet('unixSocket')) {
			$dumper = $dumper->setSocket(self::getOption('unixSocket'));
		}

		if (self::optionIsSet('dumpBinaryPath')) {
			$dumper = $dumper->setDumpBinaryPath(self::getOption('dumpBinaryPath'));
		}

		if (self::optionIsSet('timeout')) {
			$dumper = $dumper->setTimeout(self::getOption('timeout'));
		}

		if (self::optionIsSet('includeTables')) {
			$dumper = $dumper->includeTables(self::getOption('includeTables'));
		}

		if (self::optionIsSet('excludeTables')) {
			$dumper = $dumper->excludeTables(self::getOption('excludeTables'));
		}

		$dumper->setDbName($connection['database'])
			->setUserName($connection['username'])
			->setPassword($connection['password'])
			->setHost($connection['host'])
			->dumpToFile($fullPath);

		return $filename;
	}

	protected static function dumpPostgreSql($connection)
	{
		$filename = 'databasedump.sql';
		$fullPath = self::getDumpPath($filename);

		/** @var PostgreSql $dumper */
		$dumper = PostgreSql::create();

		if (self::optionIsSet('port')) {
			$dumper = $dumper->setPort(self::getOption('port'));
		}

		if (self::optionIsSet('unixSocket')) {
			$dumper = $dumper->setSocket(self::getOption('unixSocket'));
		}

		if (self::optionIsSet('dumpBinaryPath')) {
			$dumper = $dumper->setDumpBinaryPath(self::getOption('dumpBinaryPath'));
		}

		if (self::optionIsSet('timeout')) {
			$dumper = $dumper->setTimeout(self::getOption('timeout'));
		}

		if (self::optionIsSet('includeTables')) {
			$dumper = $dumper->includeTables(self::getOption('includeTables'));
		}

		if (self::optionIsSet('excludeTables')) {
			$dumper = $dumper->excludeTables(self::getOption('excludeTables'));
		}

		$dumper->setDbName($connection['database'])
			->setUserName($connection['username'])
			->setPassword($connection['password'])
			->setHost($connection['host'])
			->dumpToFile($fullPath);

		return $filename;
	}

	protected static function optionIsSet($option)
	{
		$options = \Config::get('laravel-centinel-api::database');

		if (isset($options[$option]) && !is_null($options[$option])) {
			return true;
		}

		return false;
	}

	protected static function getOption($option)
	{
		$options = \Config::get('laravel-centinel-api::database');

		return $options[$option];
	}
}