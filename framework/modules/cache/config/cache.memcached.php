<?php
return array
(
    // @link http://github.com/gimpe/kohana-memcached
    /*
    'memcached' => array(
        'driver' => 'memcached',
        'servers' => array(
            //array(
            //    'host' => 'localhost',
            //    'port' => 11211,
            //    'weight' => 1,
            //    ),
            ),
        'options' => array(
            // see http://php.net/manual/en/memcached.setoption.php and http://www.php.net/manual/en/memcached.constants.php

            //Memcached::OPT_COMPRESSION => TRUE,
            // Enables or disables payload compression. When enabled, item values longer than a certain threshold (currently 100 bytes) will be
            // compressed during storage and decompressed during retrieval transparently.
            // Type: boolean, default: TRUE.
            // more info https://github.com/iliaal/php-memcached/blob/master/memcached.ini

            //Memcached::OPT_SERIALIZER => Memcached::SERIALIZER_PHP,
            // Specifies the serializer to use for serializing non-scalar values. The valid serializers are Memcached::SERIALIZER_PHP or
            // Memcached::SERIALIZER_IGBINARY. The latter is supported only when memcached is configured with --enable-memcached-igbinary option and
            // the igbinary extension is loaded.
            // Type: integer, default: Memcached::SERIALIZER_PHP.
            //
            // Choices:
            //    Memcached::SERIALIZER_PHP     : The default PHP serializer.
            //    Memcached::SERIALIZER_IGBINARY: The igbinary serializer. Instead of textual representation it stores PHP data structures in a
            //                                    compact binary form, resulting in space and time gains.
            //    Memcached::SERIALIZER_JSON    : The JSON serializer. Requires PHP 5.2.10+.

            //Memcached::OPT_PREFIX_KEY => '',
            // This can be used to create a "domain" for your item keys. The value specified here will be prefixed to each of the keys. It cannot be
            // longer than 128 characters and will reduce the maximum available key size. The prefix is applied only to the item keys, not to the
            // server keys.
            // Type: string, default: "".

            //Memcached::OPT_HASH => Memcached::HASH_DEFAULT,
            // Specifies the hashing algorithm used for the item keys. The valid values are supplied via Memcached::HASH_* constants. Each hash
            // algorithm has its advantages and its disadvantages. Go with the default if you don't know or don't care.
            // Type: integer, default: Memcached::HASH_DEFAULT
            //
            // Choices:
            //    Memcached::HASH_DEFAULT : The default (Jenkins one-at-a-time) item key hashing algorithm.
            //    Memcached::HASH_MD5     : MD5 item key hashing algorithm.
            //    Memcached::HASH_CRC     : CRC item key hashing algorithm.
            //    Memcached::HASH_FNV1_64 : FNV1_64 item key hashing algorithm.
            //    Memcached::HASH_FNV1A_64: FNV1_64A item key hashing algorithm.
            //    Memcached::HASH_FNV1_32 : FNV1_32 item key hashing algorithm.
            //    Memcached::HASH_FNV1A_32: FNV1_32A item key hashing algorithm.
            //    Memcached::HASH_HSIEH   : Hsieh item key hashing algorithm.
            //    Memcached::HASH_MURMUR  : Murmur item key hashing algorithm.

            Memcached::OPT_DISTRIBUTION => Memcached::DISTRIBUTION_CONSISTENT,
            // Specifies the method of distributing item keys to the servers. Currently supported methods are modulo and consistent hashing.
            // Consistent hashing delivers better distribution and allows servers to be added to the cluster with minimal cache losses.
            // Type: integer, default: Memcached::DISTRIBUTION_MODULA.
            //
            // Choices:
            //    Memcached::DISTRIBUTION_MODULA: Modulo-based key distribution algorithm.
            //    Memcached::DISTRIBUTION_CONSISTENT: Consistent hashing key distribution algorithm (based on libketama).

            Memcached::OPT_LIBKETAMA_COMPATIBLE => TRUE,
            // Enables or disables compatibility with libketama-like behavior. When enabled, the item key hashing algorithm is set to MD5 and
            // distribution is set to be weighted consistent hashing distribution. This is useful because other libketama-based clients (Python,
            // Ruby, etc.) with the same server configuration will be able to access the keys transparently.
            // Note: It is highly recommended to enable this option if you want to use consistent hashing, and it may be enabled by default in future
            // releases.
            // Type: boolean, default: FALSE.

            //Memcached::OPT_BUFFER_WRITES => FALSE,
            // Enables or disables buffered I/O. Enabling buffered I/O causes storage commands to "buffer" instead of being sent. Any action that
            // retrieves data causes this buffer to be sent to the remote connection. Quitting the connection or closing down the connection will
            // also cause the buffered data to be pushed to the remote connection.
            // Type: boolean, default: FALSE.

            //Memcached::OPT_BINARY_PROTOCOL => FALSE,
            // Enable the use of the binary protocol. Please note that you cannot toggle this option on an open connection.
            // Type: boolean, default: FALSE.

            //Memcached::OPT_NO_BLOCK => FALSE,
            // Enables or disables asynchronous I/O. This is the fastest transport available for storage functions.
            // Type: boolean, default: FALSE.

            //Memcached::OPT_TCP_NODELAY => FALSE,
            // Enables or disables the no-delay feature for connecting sockets (may be faster in some environments).
            // Type: boolean, default: FALSE.

            //Memcached::OPT_SOCKET_SEND_SIZE => ,
            // The maximum socket send buffer in bytes.
            // Type: integer, default: varies by platform/kernel configuration.

            //Memcached::OPT_SOCKET_RECV_SIZE => ,
            // The maximum socket receive buffer in bytes.
            // Type: integer, default: varies by platform/kernel configuration.

            //Memcached::OPT_CONNECT_TIMEOUT => 1000,
            // In non-blocking mode this set the value of the timeout during socket connection, in milliseconds.
            // Type: integer, default: 1000.

            //Memcached::OPT_RETRY_TIMEOUT => 0,
            // The amount of time, in seconds, to wait until retrying a failed connection attempt.
            // Type: integer, default: 0.

            //Memcached::OPT_SEND_TIMEOUT => 0,
            // Socket sending timeout, in microseconds. In cases where you cannot use non-blocking I/O this will allow you to still have timeouts on the sending of data.
            // Type: integer, default: 0.

            //Memcached::OPT_RECV_TIMEOUT => 0,
            // Socket reading timeout, in microseconds. In cases where you cannot use non-blocking I/O this will allow you to still have timeouts on the reading of data.
            // Type: integer, default: 0.

            //Memcached::OPT_POLL_TIMEOUT => 1000,
            // Timeout for connection polling, in milliseconds.
            // Type: integer, default: 1000.

            //Memcached::OPT_CACHE_LOOKUPS => FALSE,
            // Enables or disables caching of DNS lookups.
            // Type: boolean, default: FALSE.

            //Memcached::OPT_SERVER_FAILURE_LIMIT => 0,
            // Specifies the failure limit for server connection attempts. The server will be removed after this many continuous connection failures.
            // Type: integer, default: 0.
            ),
        ),
    'apc' => array
    (
        'driver' => 'apc',
        'default_expire' => 3600,
    ),
    'file' => array
    (
        'driver' => 'file',
        'cache_dir' => APPPATH . 'cache',
        'default_expire' => 3600,
    )
	*/
);
