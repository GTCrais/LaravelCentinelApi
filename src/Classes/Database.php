<?php

namespace GTCrais\LaravelCentinelApi\Classes;

use GTCrais\LaravelCentinelApi\Lumen\Config as LumenConfig;
use Spatie\DbDumper\Databases\MySql;
use GTCrais\LaravelCentinelApi\Classes\MySql as CentinelMySql;
use Spatie\DbDumper\Databases\PostgreSql;
use Spatie\DbDumper\Databases\Sqlite;
use Spatie\DbDumper\Databases\MongoDb;

class Database
{
	public static function dump()
	{
		$connection = self::getConnectionConfig();

		if (!$connection) {
			throw new \Exception('Could not find database connection configuration');
		}

		$driver = $connection['driver'];

		if (self::driverIsSupported($driver)) {
			if ($driver == 'mysql') {
				return self::dumpMySql($connection);
			}

			if ($driver == 'pgsql') {
				return self::dumpPostgreSql($connection);
			}

			if ($driver == 'sqlite') {
				return self::dumpSqLite($connection);
			}

			if ($driver == 'mongodb') {
				return self::dumpMongoDb($connection);
			}
		}

		throw new \Exception("Unsupported driver: " . $driver);
	}

	protected static function dumpMySql($connection)
	{
		$filename = 'databasedump.sql';
		$fullPath = self::getDumpPath($filename);

		/** @var MySql $dumper */
		$dumper = self::getMySqlDumper();
		$dumper = self::setDumpOptions($dumper, $connection);

		$dumper->dumpToFile($fullPath);

		return $filename;
	}

	protected static function dumpPostgreSql($connection)
	{
		$filename = 'databasedump.sql';
		$fullPath = self::getDumpPath($filename);

		/** @var PostgreSql $dumper */
		$dumper = PostgreSql::create();
		$dumper = self::setDumpOptions($dumper, $connection);

		$dumper->dumpToFile($fullPath);

		return $filename;
	}

	protected static function dumpSqLite($connection)
	{
		$filename = 'databasedump.sqlite';
		$fullPath = self::getDumpPath($filename);

		/** @var Sqlite $dumper */
		$dumper = Sqlite::create();
		$dumper = self::setDumpOptions($dumper, $connection);

		$dumper->dumpToFile($fullPath);

		return $filename;
	}

	protected static function dumpMongoDb($connection)
	{
		$filename = 'databasedump.databasedump.gz';
		$fullPath = self::getDumpPath($filename);

		/** @var MongoDb $dumper */
		$dumper = MongoDb::create();
		$dumper = self::setDumpOptions($dumper, $connection);

		$dumper->dumpToFile($fullPath);

		return $filename;
	}

	protected static function setDumpOptions($dumper, $connection)
	{
		// We're relying on the underlying Spatie DbDumper class
		// to use the correct options for each dumper

		/** @var \Spatie\DbDumper\DbDumper $dumper */

		if (isset($connection['database']) && method_exists($dumper, 'setDbName')) {
			$dumper = $dumper->setDbName($connection['database']);
		}

		if (isset($connection['username']) && method_exists($dumper, 'setUserName')) {
			$dumper = $dumper->setUserName($connection['username']);
		}

		if (isset($connection['password']) && method_exists($dumper, 'setPassword')) {
			$dumper = $dumper->setPassword($connection['password']);
		}

		if (isset($connection['host']) && method_exists($dumper, 'setHost')) {
			$dumper = $dumper->setHost($connection['host']);
		}

		if (self::optionIsSet('dumpBinaryPath') && method_exists($dumper, 'setDumpBinaryPath')) {
			$dumper = $dumper->setDumpBinaryPath(self::getOption('dumpBinaryPath'));
		}

		if (self::optionIsSet('timeout') && method_exists($dumper, 'setTimeout')) {
			$dumper = $dumper->setTimeout(self::getOption('timeout'));
		}

		if (method_exists($dumper, 'setPort')) {
			if (!empty($connection['port'])) {
				$dumper = $dumper->setPort($connection['port']);
			} else if (self::optionIsSet('port')) {
				$dumper = $dumper->setPort(self::getOption('port'));
			}
		}

		if (method_exists($dumper, 'setSocket')) {
			if (!empty($connection['unix_socket'])) {
				$dumper = $dumper->setSocket($connection['unix_socket']);
			} else if (self::optionIsSet('unixSocket')) {
				$dumper = $dumper->setSocket(self::getOption('unixSocket'));
			}
		}

		if (self::optionIsSet('includeTables') && method_exists($dumper, 'includeTables')) {
			$dumper = $dumper->includeTables(self::getOption('includeTables'));
		}

		if (self::optionIsSet('excludeTables') && method_exists($dumper, 'excludeTables')) {
			$dumper = $dumper->excludeTables(self::getOption('excludeTables'));
		}

		/** @var \Spatie\DbDumper\Databases\MySql $dumper */

		if (self::optionIsSet('dontSkipComments') && self::getOption('dontSkipComments') && method_exists($dumper, 'dontSkipComments')) {
			$dumper = $dumper->dontSkipComments();
		}

		if (self::optionIsSet('dontUseExtendedInserts') && self::getOption('dontUseExtendedInserts') && method_exists($dumper, 'dontUseExtendedInserts')) {
			$dumper = $dumper->dontUseExtendedInserts();
		}

		if (self::optionIsSet('useSingleTransaction') && self::getOption('useSingleTransaction') && method_exists($dumper, 'useSingleTransaction')) {
			$dumper = $dumper->useSingleTransaction();
		}

		if (self::optionIsSet('setDefaultCharacterSet') && method_exists($dumper, 'setDefaultCharacterSet')) {
			$dumper = $dumper->setDefaultCharacterSet(self::getOption('setDefaultCharacterSet'));
		}

		if (self::optionIsSet('gtidPurged') && method_exists($dumper, 'setGtidPurged')) {
			$dumper = $dumper->setGtidPurged(self::getOption('gtidPurged'));
		}

		if (self::optionIsSet('extraOptions') && is_array(self::getOption('extraOptions')) && method_exists($dumper, 'addExtraOption')) {
			foreach (self::getOption('extraOptions') as $extraOption) {
				$dumper->addExtraOption($extraOption);
			}
		}

		/** @var \Spatie\DbDumper\Databases\PostgreSQL $dumper */

		if (self::optionIsSet('useInserts') && self::getOption('useInserts') && method_exists($dumper, 'useInserts')) {
			$dumper = $dumper->useInserts();
		}

		/** @var \Spatie\DbDumper\Databases\MongoDb $dumper */

		if (self::optionIsSet('collection') && method_exists($dumper, 'setCollection')) {
			$dumper = $dumper->setCollection(self::getOption('collection'));
		}

		if (self::optionIsSet('authenticationDatabase') && method_exists($dumper, 'setAuthenticationDatabase')) {
			$dumper = $dumper->setAuthenticationDatabase(self::getOption('authenticationDatabase'));
		}

		if (self::optionIsSet('enableCompression') && self::getOption('enableCompression') && method_exists($dumper, 'enableCompression')) {
			$dumper = $dumper->enableCompression();
		}

		return $dumper;
	}

	protected static function driverIsSupported($driver)
	{
		$supported = [
			'mysql',
			'pgsql'
		];

		if (class_exists('Spatie\DbDumper\Databases\Sqlite')) {
			$supported[] = 'sqlite';
		}

		if (class_exists('Spatie\DbDumper\Databases\MongoDb')) {
			$supported[] = 'mongodb';
		}

		return in_array($driver, $supported);
	}

	protected static function getConnectionConfig()
	{
		if (Platform::getPlatform() == 'laravel') {
			$connectionName = config('centinelApi.database.connection');
			$connectionName = ($connectionName == '{default}') ? config('database.default') : $connectionName;

			return config('database.connections.' . $connectionName);
		}

		return LumenConfig::getDatabaseConnectionConfig();
	}

	public static function getDumpPath($filename = null)
	{
		$path = storage_path('databasedump');

		if ($filename) {
			$path .= '/' . $filename;
		}

		return $path;
	}

	protected static function optionIsSet($option)
	{
		$options = config('centinelApi.database');

		if (isset($options[$option]) && !is_null($options[$option])) {
			return true;
		}

		return false;
	}

	protected static function getOption($option)
	{
		$options = config('centinelApi.database');

		return $options[$option];
	}

	protected static function getMySqlDumper()
	{
		// If Sqlite class exists, it means we're using
		// version of DB Dumper where MySql has been fixed
		// so we can return the default MySql dumper

		if (class_exists('Spatie\DbDumper\Databases\Sqlite')) {
			return MySql::create();
		}

		return CentinelMySql::create();
	}
}