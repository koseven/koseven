<?php

return [
	'default' => [
		'type' => Encrypt::TYPE_OPENSSL,
		/**
		 * The following options must be set
         * string hash one of sha-2 cryptographic hashes (sha256, sha384, sha512)
		 * string key secret passphrase
		 * string cipher encryption cipher, one of the openssl cipher constants
		 */
		'hash' => 'sha256',
		'key' => null,
		'cipher' => 'AES-256-CTR'
	],
//	'libsodium' => [
//		'type' => Encrypt::TYPE_LIBSODIUM,
//		'key' => null
//	],
//	'legacy' => [
//		'type' => Encrypt::TYPE_LEGACY,
//		'key' => NULL,
//		'cipher' => MCRYPT_RIJNDAEL_128,
//		'mode' => MCRYPT_MODE_CBC,
//	],
];
