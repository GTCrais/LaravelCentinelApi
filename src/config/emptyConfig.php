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
		'unix_socket' => null,
		'dump_binary_path' => null,
		'timeout' => 120,
		'includeTables' => null,
		'excludeTables' => null
	],

	'zipPassword' => ''

];