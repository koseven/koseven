<?php
return array
(
/*	
    'default' => 'file',                            // allows to specify default cache directl from config file
    'prefix'  => 'cache1_',                          //used to avoid duplicates when using _sanitize_id
    'apcu'   => array(
        'driver'             => 'apcu',
        'default_expire'     => 3600,
        'prefix'             => 'cache_apcu_',       // if set uses this prefix instead of global 'prefix'
    ),
    'memcache' => array(
		'driver'             => 'memcache',
		'default_expire'     => 3600,
		'compression'        => FALSE,              // Use Zlib compression (can cause issues with integers)
		'servers'            => array(
			'local' => array(
				'host'             => 'localhost',  // Memcache Server
				'port'             => 11211,        // Memcache port number
				'persistent'       => FALSE,        // Persistent connection
				'weight'           => 1,
				'timeout'          => 1,
				'retry_interval'   => 15,
				'status'           => TRUE,
			),
		),
		'instant_death'      => TRUE,               // Take server offline immediately on first fail (no retry)
	),
	'memcachetag' => array(
		'driver'             => 'memcachetag',
		'default_expire'     => 3600,
		'compression'        => FALSE,              // Use Zlib compression (can cause issues with integers)
		'servers'            => array(
			'local' => array(
				'host'             => 'localhost',  // Memcache Server
				'port'             => 11211,        // Memcache port number
				'persistent'       => FALSE,        // Persistent connection
				'weight'           => 1,
				'timeout'          => 1,
				'retry_interval'   => 15,
				'status'           => TRUE,
			),
		),
		'instant_death'      => TRUE,
	),
	'wincache' => array(
		'driver'             => 'wincache',
		'default_expire'     => 3600,
	),
	'sqlite'   => array(
		'driver'             => 'sqlite',
		'default_expire'     => 3600,
		'database'           => APPPATH.'cache/kohana-cache.sql3',
		'schema'             => 'CREATE TABLE caches(id VARCHAR(127) PRIMARY KEY, tags VARCHAR(255), expiration INTEGER, cache TEXT)',
	),
	'eaccelerator'           => array(
		'driver'             => 'eaccelerator',
	),
	'xcache'   => array(
		'driver'             => 'xcache',
		'default_expire'     => 3600,
	),
	'file'    => array(
		'driver'             => 'file',
		'cache_dir'          => APPPATH.'cache',
		'default_expire'     => 3600,
		'ignore_on_delete'   => array(
			'.gitignore',
			'.git',
			'.svn'
		)
	)
*/
);
