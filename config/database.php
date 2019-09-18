<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Default Database Connection Name
	|--------------------------------------------------------------------------
	|
	| Here you may specify which of the database connections below you wish
	| to use as your default connection for all database work. Of course
	| you may use many connections at once using the Database library.
	|
	*/

	'default' => env('DB_CONNECTION', 'dev_tap_dw'),

	/*
	|--------------------------------------------------------------------------
	| Database Connections
	|--------------------------------------------------------------------------
	|
	| Here are each of the database connections setup for your application.
	| Of course, examples of configuring each database platform that is
	| supported by Laravel is shown below to make development simple.
	|
	|
	| All database work in Laravel is done through the PHP PDO facilities
	| so make sure you have the driver for your particular database of
	| choice installed on your machine before you begin development.
	|
	*/

	'connections' => [

		# GLOBAL DATABASE CONFIG
		'tap_flow' => [
			'driver'   => 'oracle',
			'tns'      =>  '',
			'host'     => '10.20.1.111',
			'port'     => '1521',
			'database' => 'tapapps',
			'username' => 'tap_flow',
			'password' => 'tap_flow',
			'charset'  => 'AL32UTF8',
			'prefix'   => '',
		],

		# PRODUCTION DATABASE ENV
		'prod_tapapps_mobile_estate' => [
			'driver'   => 'oracle',
			'tns'      =>  '',
			'host'     => '10.20.1.111',
			'port'     => '1521',
			'database' => 'tapapps',
			'username' => 'mobile_estate',
			'password' => 'estate123#',
			'charset'  => 'AL32UTF8',
			'prefix'   => '',
		],
		'prod_tapapps_mobile_inspection' => [
			'driver'   => 'oracle',
			'tns'      =>  '',
			'host'     => '10.20.1.111',
			'port'     => '1521',
			'database' => 'tapapps',
			'username' => 'mobile_inspection',
			'password' => 'mobile_inspection',
			'charset'  => 'AL32UTF8',
			'prefix'   => '',
		],
		'prod_tap_dw' => [
			'driver'   => 'oracle',
			'tns'      =>  '',
			'host'     => 'dw.tap-agri.com',
			'port'     => '1521',
			'database' => 'tapdw',
			'username' => 'qa_user',
			'password' => 'qa_user',
			'charset'  => 'AL32UTF8',
			'prefix'   => '',
		],

		# QA DATABASE ENV 
		'qa_tapapps_mobile_inspection' => [
			'driver'   => 'oracle',
			'tns'      =>  '',
			'host'     => '10.20.1.111',
			'port'     => '1521',
			'database' => 'tapapps',
			'username' => 'mobile_inspection',
			'password' => 'mobile_inspection',
			'charset'  => 'AL32UTF8',
			'prefix'   => '',
		],
		'qa_tap_dw' => [
			'driver'   => 'oracle',
			'tns'      =>  '',
			'host'     => '10.20.1.103',
			'port'     => '1521',
			'database' => 'tapdw',
			'username' => 'tap_dw',
			'password' => 'tapdw123#',
			'charset'  => 'AL32UTF8',
			'prefix'   => '',
		],
		'qa_tapapps_mobile_estate' => [
			'driver'   => 'oracle',
			'tns'      =>  '',
			'host'     => '10.20.1.111',
			'port'     => '1521',
			'database' => 'tapapps',
			'username' => 'mobile_estate',
			'password' => 'estate123#',
			'charset'  => 'AL32UTF8',
			'prefix'   => '',
		],

		# DEVELOPMENT DATABASE ENV 
		'dev_tapapps_mobile_inspection' => [
			'driver'   => 'oracle',
			'tns'      =>  '',
			'host'     => '10.20.1.111',
			'port'     => '1521',
			'database' => 'tapapps',
			'username' => 'mobile_inspection',
			'password' => 'mobile_inspection',
			'charset'  => 'AL32UTF8',
			'prefix'   => '',
		],
		'dev_tap_dw' => [
			'driver'   => 'oracle',
			'tns'      =>  '',
			'host'     => '10.20.1.103',
			'port'     => '1521',
			'database' => 'tapdw',
			'username' => 'tap_dw',
			'password' => 'tapdw123#',
			'charset'  => 'AL32UTF8',
			'prefix'   => '',
		],
		'dev_tapapps_mobile_estate' => [
			'driver'   => 'oracle',
			'tns'      =>  '',
			'host'     => '10.20.1.111',
			'port'     => '1521',
			'database' => 'tapapps',
			'username' => 'mobile_estate',
			'password' => 'estate123#',
			'charset'  => 'AL32UTF8',
			'prefix'   => '',
		],

		# TIDAK DIPAKAI / DIABAIKAN
		'sqlite' => [
			'driver' => 'sqlite',
			'database' => env('DB_DATABASE', database_path('database.sqlite')),
			'prefix' => '',
		],
		'pgsql' => [
			'driver' => 'pgsql',
			'host' => env('DB_HOST', '127.0.0.1'),
			'port' => env('DB_PORT', '5432'),
			'database' => env('DB_DATABASE', 'forge'),
			'username' => env('DB_USERNAME', 'forge'),
			'password' => env('DB_PASSWORD', ''),
			'charset' => 'utf8',
			'prefix' => '',
			'schema' => 'public',
			'sslmode' => 'prefer',
		],

		'sqlsrv' => [
			'driver' => 'sqlsrv',
			'host' => env('DB_HOST', 'localhost'),
			'port' => env('DB_PORT', '1433'),
			'database' => env('DB_DATABASE', 'forge'),
			'username' => env('DB_USERNAME', 'forge'),
			'password' => env('DB_PASSWORD', ''),
			'charset' => 'utf8',
			'prefix' => '',
		],

	],

	/*
	|--------------------------------------------------------------------------
	| Migration Repository Table
	|--------------------------------------------------------------------------
	|
	| This table keeps track of all the migrations that have already run for
	| your application. Using this information, we can determine which of
	| the migrations on disk haven't actually been run in the database.
	|
	*/

	'migrations' => 'migrations',

	/*
	|--------------------------------------------------------------------------
	| Redis Databases
	|--------------------------------------------------------------------------
	|
	| Redis is an open source, fast, and advanced key-value store that also
	| provides a richer set of commands than a typical key-value systems
	| such as APC or Memcached. Laravel makes it easy to dig right in.
	|
	*/

	'redis' => [

		'client' => 'predis',

		'default' => [
			'host' => env('REDIS_HOST', '127.0.0.1'),
			'password' => env('REDIS_PASSWORD', null),
			'port' => env('REDIS_PORT', 6379),
			'database' => 0,
		],

	],

];
