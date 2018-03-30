<?php

return [

	'privateKey' => '',

	'encryptionKey' => '',

	'routePrefix' => '',

	'enabledRoutes' => [
		'LogRoutes',
		'DatabaseRoutes',
	],

	'database' => [
		'connection' => '{default}',
		'port' => null,
		'unixSocket' => null,
		'dumpBinaryPath' => null,
		'timeout' => 120,
		'includeTables' => null,
		'excludeTables' => null
	],

	'zipPassword' => ''

];