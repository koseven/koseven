<?php

return [
	'database' => [
		/**
		 * Database settings for session storage.
		 *
		 * string   group  configuation group name
		 * string   table  session table name
		 * integer  gc     number of requests before gc is invoked
		 * columns  array  custom column names
		 */
		'group'   => 'default',
		'table'   => 'sessions',
		'gc'      => 500,
		'columns' => [
			/**
			 * session_id:  session identifier
			 * last_active: timestamp of the last activity
			 * contents:    serialized session data
			 */
			'session_id'  => 'session_id',
			'last_active' => 'last_active',
			'contents'    => 'contents'
		],
		/**
		 * disable save empty session
		 */
		'save_empty_session' => true,
		/**
		 * Interval in seconds to update the session content if it hasn't changed
		 */
		'session_update_interval' => 0
	],
];
