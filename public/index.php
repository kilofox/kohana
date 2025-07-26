<?php

/**
 * The default extension of resource files. If you change this, all resources
 * must be renamed to use the new extension.
 *
 * @link https://kohana.top/guide/about.install#ext
 */
define('EXT', '.php');

/**
 * Set the PHP error reporting level. If you set this in php.ini, you remove this.
 * @link https://www.php.net/errorfunc.configuration#ini.error-reporting
 *
 * When developing your application, it is highly recommended to enable notices
 * and warnings. Enable them by using: E_ALL
 *
 * In a production environment, it is safe to ignore notices and warnings.
 * Disable them by using: E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED
 */
error_reporting(E_ALL);

/**
 * End of standard configuration! Changing any of the code below should only be
 * attempted by those with a working knowledge of Kohana internals.
 *
 * @link https://kohana.top/guide/using.configuration
 */
// Set the full path to the docroot
define('DOCROOT', __DIR__ . DIRECTORY_SEPARATOR);

// Define the absolute paths for required directories
define('APPPATH', realpath(DOCROOT . '../application') . DIRECTORY_SEPARATOR);
define('MODPATH', realpath(DOCROOT . '../modules') . DIRECTORY_SEPARATOR);
define('SYSPATH', realpath(DOCROOT . '../system') . DIRECTORY_SEPARATOR);

if (file_exists('install' . EXT)) {
    // Load the installation check
    return include 'install' . EXT;
}

/**
 * Define the start time of the application, used for profiling.
 */
if (!defined('KOHANA_START_TIME')) {
    define('KOHANA_START_TIME', microtime(true));
}

/**
 * Define the memory usage at the start of the application, used for profiling.
 */
if (!defined('KOHANA_START_MEMORY')) {
    define('KOHANA_START_MEMORY', memory_get_usage());
}

// Bootstrap the application
require APPPATH . 'bootstrap' . EXT;

if (PHP_SAPI === 'cli') {
    // Try and load minion
    class_exists('Minion_Task') or die('Please enable the Minion module for CLI support.');
    set_exception_handler(['Minion_Exception', 'handler']);

    Minion_Task::factory(Minion_CLI::options())->execute();
} else {
    /**
     * Execute the main request. A source of the URI can be passed, e.g., $_SERVER['PATH_INFO'].
     * If no source is specified, the URI will be automatically detected.
     */
    echo Request::factory(true, [], false)
        ->execute()
        ->send_headers(true)
        ->body();
}
