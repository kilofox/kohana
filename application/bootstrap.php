<?php

// -- Environment setup --------------------------------------------------------
// Load the core Kohana class
require SYSPATH . 'classes/Kohana/Core' . EXT;

if (is_file(APPPATH . 'classes/Kohana' . EXT)) {
    // Application extends the core
    require APPPATH . 'classes/Kohana' . EXT;
} else {
    // Load empty core extension
    require SYSPATH . 'classes/Kohana' . EXT;
}

/**
 * Set the default time zone.
 *
 * @link https://kohana.top/guide/using.configuration
 * @link https://www.php.net/timezones
 */
date_default_timezone_set('America/Chicago');

/**
 * Set the default locale.
 *
 * @link https://kohana.top/guide/using.configuration
 * @link https://www.php.net/function.setlocale
 */
setlocale(LC_ALL, 'en_US.utf-8');

/**
 * Enable the Kohana autoloader.
 *
 * @link https://kohana.top/guide/using.autoloading
 * @link https://www.php.net/function.spl-autoload-register
 */
spl_autoload_register(['Kohana', 'auto_load']);

/**
 * Optionally, you can enable a compatibility autoloader for use with
 * older modules that have not been updated for PSR-0.
 *
 * It is recommended to not enable this unless absolutely necessary.
 */
//spl_autoload_register(['Kohana', 'auto_load_lowercase']);

/**
 * Enable the Kohana autoloader for unserialization.
 *
 * @link https://www.php.net/function.spl-autoload-call
 * @link https://www.php.net/var.configuration#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

/**
 * Set the mb_substitute_character to "none"
 *
 * @link https://www.php.net/manual/en/function.mb-substitute-character.php
 */
mb_substitute_character('none');

// -- Configuration and initialization -----------------------------------------

/**
 * Set the default language
 */
I18n::lang('en-us');

if (isset($_SERVER['SERVER_PROTOCOL'])) {
    // Replace the default protocol.
    HTTP::$protocol = $_SERVER['SERVER_PROTOCOL'];
}

/**
 * Set Kohana::$environment if a 'KOHANA_ENV' environment variable has been supplied.
 *
 * Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant Kohana::<INVALID_ENV_NAME>"
 */
if (isset($_SERVER['KOHANA_ENV'])) {
    Kohana::$environment = constant('Kohana::' . strtoupper($_SERVER['KOHANA_ENV']));
}

/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   null
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - integer  cache_life  lifetime, in seconds, of items cached              60
 * - boolean  errors      enable or disable error handling                   true
 * - boolean  profile     enable or disable internal profiling               true
 * - boolean  caching     enable or disable internal caching                 false
 * - boolean  expose      set the X-Powered-By header                        false
 */
Kohana::init([
    'base_url' => '/kohana/',
]);

/**
 * Attach the file writer to logging. Multiple writers are supported.
 */
Kohana::$log->attach(new Log_File(APPPATH . 'logs'));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Kohana::$config->attach(new Config_File);

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules([
//    'auth' => MODPATH . 'auth', // Basic authentication
//    'cache' => MODPATH . 'cache', // Caching with multiple backends
//    'codebench' => MODPATH . 'codebench', // Benchmarking tool
//    'database' => MODPATH . 'database', // Database access
//    'image' => MODPATH . 'image', // Image manipulation
//    'minion' => MODPATH . 'minion', // CLI Tasks
//    'orm' => MODPATH . 'orm', // Object Relationship Mapping
//    'unittest' => MODPATH . 'unittest', // Unit testing
//    'userguide' => MODPATH . 'userguide', // User guide and API documentation
]);

/**
 * Cookie Salt
 * @see  https://kohana.top/3.3/guide/kohana/cookies
 *
 * If you have not defined a cookie salt in your Cookie class then
 * uncomment the line below and define a preferably long salt.
 */
// Cookie::$salt = null;

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */
Route::set('default', '(<controller>(/<action>(/<id>)))')
    ->defaults([
        'controller' => 'welcome',
        'action' => 'index',
    ]);
