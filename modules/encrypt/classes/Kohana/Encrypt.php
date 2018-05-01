<?php

class Kohana_Encrypt
{
    /**
     * Types of encryption engines available
     */
    const TYPE_LEGACY = 'legacy';
    const TYPE_OPENSSL = 'openssl';
    const TYPE_LIBSODIUM = 'libsodium';

    /**
     * @var string Default instance name
     */
    public static $default = 'default';

    /**
     * @var array Instances of Kohana_Encrypt_Engine class
     */
    public static $instances = [];

    /**
     * Creates instance of Encrypt_Engine class
     * @param string|NULL $name Name of the instance
     * @param array|NULL $config Custom array with configuration (not needed)
     * @return Kohana_Encrypt_Engine
     */
    public static function instance(string $name = NULL, array $config = NULL): Kohana_Encrypt_Engine
    {
        if (is_null($name))
        {
            $name = self::$default;
        }

        if (!isset(self::$instances[$name]))
        {
            if ($config === NULL)
            {
                // Load the configuration data
                $config = Kohana::$config->load('encrypt')->{$name};
            }

            $class = 'Kohana_Encrypt_Engine_' . Text::ucfirst($config['type']);
            self::$instances[$name] = new $class($config);
        }
        return self::$instances[$name];
    }

    /**
     * Kohana_Encrypt constructor.
     * Singleton - constructor is private
     */
    private function __construct()
    {

    }

    /**
     * Converts from one encryption instance to another
     * Useful if your key has been revealed, or you are
     * updating from legacy mcrypt to openssl /
     * @param string $nameFrom Name of your old encryption instance
     * @param string $nameTo Name of your new encryption instance
     * @param string $ciphertext Ciphertext to convert
     * @return mixed
     */
    public static function convert(string $nameFrom, string $nameTo, string $ciphertext)
    {
        return self::instance($nameTo)->encrypt(self::instance($nameFrom)->decrypt($ciphertext));
    }

}
