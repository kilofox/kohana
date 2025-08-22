<?php

/**
 * Unit testing helpers
 */
class Kohana_Unittest_Helpers
{
    /**
     * Static variable used to work out whether we have an internet
     * connection
     * @see has_internet
     * @var bool
     */
    static protected $_has_internet = null;

    /**
     * Check for internet connectivity
     *
     * @return bool Whether an internet connection is available
     */
    public static function has_internet()
    {
        if (!isset(self::$_has_internet)) {
            // The @ operator is used here to avoid DNS errors when there is no connection.
            $sock = @fsockopen("www.google.com", 80, $errno, $errstr, 1);

            self::$_has_internet = (bool) $sock;
        }

        return self::$_has_internet;
    }

    /**
     * Helper function which replaces the "/" to OS-specific delimiter
     *
     * @param string $path
     * @return string
     */
    static public function dir_separator(string $path)
    {
        return str_replace('/', DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Removes all cache files from the kohana cache dir
     *
     * @return void
     */
    static public function clean_cache_dir()
    {
        $cache_dir = opendir(Kohana::$cache_dir);

        while ($dir = readdir($cache_dir)) {
            // Cache files are split into directories based on first two characters of hash
            if ($dir[0] !== '.' && strlen($dir) === 2) {
                $dir = self::dir_separator(Kohana::$cache_dir . '/' . $dir . '/');

                $cache = opendir($dir);

                while ($file = readdir($cache)) {
                    if ($file[0] !== '.') {
                        unlink($dir . $file);
                    }
                }

                closedir($cache);

                rmdir($dir);
            }
        }

        closedir($cache_dir);
    }

    /**
     * Backup of the environment variables
     * @see set_environment
     * @var array
     */
    protected $_environment_backup = [];

    /**
     * Allows easy setting & backing up of environment config
     *
     * Option types are checked in the following order:
     *
     * * Server Var
     * * Static Variable
     * * Config option
     *
     * @param array $environment List of environment to set
     * @return false|void
     * @throws Kohana_Exception
     * @throws ReflectionException
     */
    public function set_environment(array $environment)
    {
        if (!count($environment))
            return false;

        foreach ($environment as $option => $value) {
            $backup_needed = !array_key_exists($option, $this->_environment_backup);

            // Handle changing superglobals
            if (in_array($option, ['_GET', '_POST', '_SERVER', '_FILES'])) {
                // For some reason we need to do this in order to change the superglobals
                global $$option;

                if ($backup_needed) {
                    $this->_environment_backup[$option] = $$option;
                }

                // PHPUnit makes a backup of superglobals automatically
                $$option = $value;
            }
            // If this is a static property i.e. Html::$windowed_urls
            elseif (strpos($option, '::$') !== false) {
                list($class, $var) = explode('::$', $option, 2);

                $class = new ReflectionClass($class);

                if ($backup_needed) {
                    $this->_environment_backup[$option] = $class->getStaticPropertyValue($var);
                }

                $class->setStaticPropertyValue($var, $value);
            }
            // If this is an environment variable
            elseif (preg_match('/^[A-Z_-]+$/', $option) || isset($_SERVER[$option])) {
                if ($backup_needed) {
                    $this->_environment_backup[$option] = $_SERVER[$option] ?? '';
                }

                $_SERVER[$option] = $value;
            }
            // Else we assume this is a config option
            else {
                if ($backup_needed) {
                    $this->_environment_backup[$option] = Kohana::$config->load($option);
                }

                list($group, $var) = explode('.', $option, 2);

                Kohana::$config->load($group)->set($var, $value);
            }
        }
    }

    /**
     * Restores the environment to the original state
     *
     * @chainable
     * @return void
     * @throws Kohana_Exception
     * @throws ReflectionException
     */
    public function restore_environment()
    {
        $this->set_environment($this->_environment_backup);
    }

}
