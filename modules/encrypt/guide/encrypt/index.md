# Encryption

The encrypt module is included with Koseven, but needs to be enabled before you can use it. To enable, open your
`application/bootstrap.php` file and modify the `$modules` variable by including the encrypt module like so:

    $modules = [
        ...
        'encrypt' => MODPATH.'encrypt',
        ...
    ];

Also ensure you either have the OpenSSL or Sodium extension loaded to your PHP config.

## Configuration
Copy the default config/encryption.php to your application/config folder.
The default Encryption config file looks like this:

    <?php

    return array(
        'default' => [
        		'type'	 => 'openssl',
        		'key'	 => NULL,
        		'cipher' => Encrypt_Engine_Openssl::AES_256_CBC
        	],
        	.....
    );


A couple of notes about the config.
First, you may have multiple first-level keys other than 'default' if you need to.
Second, notice there is no key provided. You need to add that.
It is strongly recommended that you choose a high-strength random key.
You can generate those for example by using the [pwgen linux program](http://linux.die.net/man/1/pwgen)...

    shell> pwgen 63 1
    trwQwVXX96TIJoKxyBHB9AJkwAOHixuV1ENZmIWyanI0j1zNgSVvqywy044Agaj

...or by going to [GRC.com/passwords.htm](https://www.grc.com/passwords.htm).

The 'type' key tells the Encryption module which driver engine to use.
The following Engines are currently supported by default:

- openssl
- sodium

## Basic Usage

To use the Encryption class, obtain an instance of the Encrypt class by calling it's *instance* method, optionally
passing the desired configuration group. If you do not pass a config group to the instance method, the default group
will be used.

    $encrypt = Encrypt::instance('tripledes');

### Encoding Data

Next, encode some data using the *encode* method:

    $encrypt = Encrypt::instance('tripledes');
    $encrypted_data = $encrypt->encode('Data to Encode');
    // $encrypted_data now contains pCD5Z6oVdb9hbLxxV+FgGrhwVzZuhQoH

[!!] Raw encrypted strings usually won't print in a browser, and may not store properly in a VARCHAR or TEXT field. For this reason, Koseven's Encrypt class automatically calls base64_encode on encode, and base64_decode on decode, to prevent this problem.

[!!] One word of caution. The length of the encoded data expands quite a bit, so be sure your database column is long enough to store the encrypted data. If even one character is truncated, the data will not be recoverable.

### Decoding Data

To decode some data, load it from the place you stored it (most likely your database) then pass it to the *decode* method:

    $encrypt = Encrypt::instance('tripledes');
    $decoded_string = $encrypt->decode($encrypted_data);
    echo $decoded_string;
    // prints 'Data to Encode'

You can't know in advance what the encoded string will be, and it's not reproducible, either.
That said, you can encode the same value over and over, but you'll always obtain a different encoded version,
even without changing your key, cipher and mode. This is because Koseven adds some random entropy before encoding it
with your value. This ensures an attacker cannot easily discover your key and cipher, even given a collection of encoded
values.
