<?php

return [

	'driver'  => 'File',
	'hash_method' => 'sha256',
	'hash_key' => NULL,
	'lifetime' => 1209600,
	'session_type' => Session::$default,
	'session_key' => 'auth_user',
	// For Auth_Bcrypt
	'cost' => 10,

	// Username/password combinations for the Auth File driver
	'users' => [
		// 'admin' => 'b3154acf3a344170077d11bdb5fff31532f679a1919e716a02',
	],

];
