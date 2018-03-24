<?php

return [

	'privateKey' => '',

	'encryptionKey' => '',

	'routePrefix' => '',

	'enabledRoutes' => [
		'LogRoutes',
		'DatabaseRoutes',
	],

	/*
	|
	| All of the options except for 'connection' are optional.
	|
	| Some of the database options are not available for Laravel/Lumen 5.1
	| and on PHP 5 (regardless of the framework version).
	|
	| For details on what is available check Spatie's DB Dumper v1.5.1
	| https://github.com/spatie/db-dumper/tree/1.5.1
	|
	| For details on how to use the options check the installed version of the package.
	| For Laravel/Lumen 5.2+ on PHP 7 that will be Spatie's DB Dumper v2.9
	| https://github.com/spatie/db-dumper/tree/2.9.0
	|
	*/

	'database' => [
		'connection' => '{default}',
		'dump_binary_path' => null,
		'timeout' => 120,

		// MySQL, PostgreSQL, MongoDB
		'port' => null, // can be overridden through /config/database.php config file

		// MySQL, PostgreSQL
		'unix_socket' => null, // can be overridden through /config/database.php config file
		'includeTables' => null, // array
		'excludeTables' => null, // array

		// MySQL
		'dontSkipComments' => null, // null or true
		'dontUseExtendedInserts' => null, // null or true
		'useSingleTransaction' => null, // null or true
		'defaultCharacterSet' => null,
		'gtidPurged' => null,
		'extraOptions' => null, // array

		// PostgreSQL
		'useInserts' => null, // null or true

		// MongoDB
		'collection' => null,
		'authenticationDatabase' => null,
		'enableCompression' => null // null or true
	],

	'zipPassword' => ''

];