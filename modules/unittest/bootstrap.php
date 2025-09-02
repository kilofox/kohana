<?php

/**
 * Set the path to the document root
 *
 * This assumes that this file is stored 2 levels below the DOCROOT, if you move
 * this bootstrap file somewhere else then you'll need to modify this value to
 * compensate.
 */
define('DOCROOT', realpath(__DIR__ . '/../../public') . DIRECTORY_SEPARATOR);

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
// Define the absolute paths for configured directories
define('APPPATH', realpath(DOCROOT . '../application') . DIRECTORY_SEPARATOR);
define('MODPATH', realpath(DOCROOT . '../modules') . DIRECTORY_SEPARATOR);
define('SYSPATH', realpath(DOCROOT . '../system') . DIRECTORY_SEPARATOR);

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
require APPPATH . 'bootstrap.php';

// Disable output buffering
if (($ob_len = ob_get_length()) !== false) {
    // flush_end on an empty buffer causes headers to be sent. Only flush if needed.
    if ($ob_len > 0) {
        ob_end_flush();
    } else {
        ob_end_clean();
    }
}

// Enable the unittest module if it is not already loaded - use the absolute path
$modules = Kohana::modules();
$unittest_path = realpath(__DIR__) . DIRECTORY_SEPARATOR;
if (!in_array($unittest_path, $modules)) {
    $modules['unittest'] = $unittest_path;
    Kohana::modules($modules);
}
