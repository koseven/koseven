<?php

// -- Environment setup --------------------------------------------------------

// Load the core Koseven class
require SYSPATH.'classes/Koseven/Core'.EXT;

if (is_file(APPPATH.'classes/Koseven'.EXT))
{
	// Application extends the core
	require APPPATH.'classes/Koseven'.EXT;
}
else
{
	// Load empty core extension
	require SYSPATH.'classes/Koseven'.EXT;
}

/**
 * Set the default time zone.
 *
 * @link http://kohanaframework.org/guide/using.configuration
 * @link http://www.php.net/manual/timezones
 */
date_default_timezone_set('America/Chicago');

/**
 * Set the default locale.
 *
 * @link http://kohanaframework.org/guide/using.configuration
 * @link http://www.php.net/manual/function.setlocale
 */
setlocale(LC_ALL, 'en_US.utf-8');

/**
 * Enable the Koseven auto-loader.
 *
 * @link http://kohanaframework.org/guide/using.autoloading
 * @link http://www.php.net/manual/function.spl-autoload-register
 */
spl_autoload_register(['Koseven', 'auto_load']);

/**
 * Optionally, you can enable a compatibility auto-loader for use with
 * older modules that have not been updated for PSR-0.
 *
 * It is recommended to not enable this unless absolutely necessary.
 */
//spl_autoload_register(array('Koseven', 'auto_load_lowercase'));

/**
 * Enable the Koseven auto-loader for unserialization.
 *
 * @link http://www.php.net/manual/function.spl-autoload-call
 * @link http://www.php.net/manual/var.configuration#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

/**
 * Enable Custom Kohana Classes for Backwards Compatibility
 */
if (Koseven::$compatibility AND is_file(APPPATH.'classes/Kohana'.EXT))
{
    // Application extends the core
    require APPPATH.'classes/Koseven'.EXT;
}

/**
 * Enable composer autoload libraries
 */
if (is_file(DOCROOT.'/vendor/autoload.php'))
{
	require DOCROOT.'/vendor/autoload.php';
}

/**
 * Set the mb_substitute_character to "none"
 *
 * @link http://www.php.net/manual/function.mb-substitute-character.php
 */
mb_substitute_character('none');

// -- Configuration and initialization -----------------------------------------

/**
 * Set the default language
 */
I18n::lang('en-us');

if (isset($_SERVER['SERVER_PROTOCOL']))
{
	// Replace the default protocol.
	HTTP::$protocol = $_SERVER['SERVER_PROTOCOL'];
}

/**
 * Set Koseven::$environment if a 'KOSEVEN_ENV/KOHANA_ENV' environment variable has been supplied.
 *
 * Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant Koseven::<INVALID_ENV_NAME>"
 */
if (isset($_SERVER['KOHANA_ENV']))
	Koseven::$environment = constant('Koseven::'.strtoupper($_SERVER['KOHANA_ENV']));
elseif(isset($_SERVER['KOSEVEN_ENV']))
    Koseven::$environment = constant('Koseven::'.strtoupper($_SERVER['KOSEVEN_ENV']));

/**
 * Initialize Koseven, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php", if set to FALSE uses clean URLS     index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - integer  cache_life  lifetime, in seconds, of items cached              60
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 * - boolean  expose      set the X-Powered-By header                        FALSE
 */
Koseven::init([
	'base_url'   => '/',
]);

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
Koseven::$log->attach(new Log_File(APPPATH.'logs'));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Koseven::$config->attach(new Config_File);

/**
 * Modules to enable. Modules are referenced by a relative or absolute path.
 */
$modules = array(
    // 'encrypt'    => MODPATH.'encrypt',    // Encryption supprt
    // 'auth'       => MODPATH.'auth',       // Basic authentication
    // 'cache'      => MODPATH.'cache',      // Caching with multiple backends
    // 'codebench'  => MODPATH.'codebench',  // Benchmarking tool
    // 'database'   => MODPATH.'database',   // Database access
    // 'image'      => MODPATH.'image',      // Image manipulation
    // 'minion'     => MODPATH.'minion',     // CLI Tasks
    // 'orm'        => MODPATH.'orm',        // Object Relationship Mapping
    // 'pagination' => MODPATH.'pagination', // Pagination
    // 'unittest'   => MODPATH.'unittest',   // Unit testing
    // 'userguide'  => MODPATH.'userguide',  // User guide and API documentation
);

/**
 * Load legacy Module for Kohana Support
 */
if (Koseven::$compatibility) {
    $modules = ['kohana' => MODPATH.'kohana'] + $modules;
}

/**
 * Initialize Modules
 */
Koseven::modules($modules);

/**
 * Cookie Salt
 * @see  http://kohanaframework.org/3.3/guide/kohana/cookies
 *
 * If you have not defined a cookie salt in your Cookie class then
 * uncomment the line below and define a preferrably long salt.
 */
// Cookie::$salt = NULL;
/**
 * Cookie HttpOnly directive
 * If set to true, disallows cookies to be accessed from JavaScript
 * @see https://en.wikipedia.org/wiki/Session_hijacking
 */
// Cookie::$httponly = TRUE;
/**
 * If website runs on secure protocol HTTPS, allows cookies only to be transmitted
 * via HTTPS.
 * Warning: HSTS must also be enabled in .htaccess, otherwise first request
 * to http://www.example.com will still reveal this cookie
 */
// Cookie::$secure = isset($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] == 'on' ? TRUE : FALSE;

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */
Route::set('default', '(<controller>(/<action>(/<id>)))')
	->defaults([
		'controller' => 'welcome',
		'action'     => 'index',
	]);
