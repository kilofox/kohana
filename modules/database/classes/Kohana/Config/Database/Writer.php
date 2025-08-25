<?php

/**
 * Database writer for the config system
 *
 * Schema for configuration table:
 *
 *    CREATE TABLE IF NOT EXISTS `config` (
 *      `group_name` varchar(128) NOT NULL,
 *      `config_key` varchar(128) NOT NULL,
 *      `config_value` text,
 *       PRIMARY KEY (`group_name`,`config_key`)
 *     ) ENGINE=InnoDB;
 *
 * @package    Kohana
 * @category   Configuration
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Config_Database_Writer extends Config_Database_Reader implements Kohana_Config_Writer
{
    protected $_loaded_keys = [];

    /**
     * Tries to load the specified configuration group
     *
     * Returns false if group does not exist or an array if it does
     *
     * @param string $group Configuration group
     * @return bool|array
     * @throws Kohana_Exception
     */
    public function load(string $group)
    {
        $config = parent::load($group);

        if ($config !== false) {
            $this->_loaded_keys[$group] = array_combine(array_keys($config), array_keys($config));
        }

        return $config;
    }

    /**
     * Writes the passed config for $group
     *
     * Returns chainable instance on success or throws
     * Kohana_Config_Exception on failure
     *
     * @param string $group The config group
     * @param string $key The config key to write to
     * @param mixed $config The configuration to write
     * @return bool
     * @throws Kohana_Exception
     */
    public function write(string $group, string $key, $config): bool
    {
        $config = serialize($config);

        // Check to see if we've loaded the config from the table already
        if (isset($this->_loaded_keys[$group][$key])) {
            $this->_update($group, $key, $config);
        } else {
            // Attempt to run an insert query
            // This may fail if the config key already exists in the table,
            // and we don't know about it
            try {
                $this->_insert($group, $key, $config);
            } catch (Database_Exception $e) {
                // Attempt to run an update instead
                $this->_update($group, $key, $config);
            }
        }

        return true;
    }

    /**
     * Insert the config values into the table
     *
     * @param string $group The config group
     * @param string $key The config key to write to
     * @param string $config The serialized configuration to write
     * @return Kohana_Config_Database_Writer
     * @throws Kohana_Exception
     */
    protected function _insert(string $group, string $key, string $config): Kohana_Config_Database_Writer
    {
        DB::insert($this->_table_name, ['group_name', 'config_key', 'config_value'])
            ->values([$group, $key, $config])
            ->execute($this->_db_instance);

        return $this;
    }

    /**
     * Update the config values in the table
     *
     * @param string $group The config group
     * @param string $key The config key to write to
     * @param string $config The serialized configuration to write
     * @return Kohana_Config_Database_Writer
     * @throws Kohana_Exception
     */
    protected function _update(string $group, string $key, string $config): Kohana_Config_Database_Writer
    {
        DB::update($this->_table_name)
            ->set(['config_value' => $config])
            ->where('group_name', '=', $group)
            ->where('config_key', '=', $key)
            ->execute($this->_db_instance);

        return $this;
    }

}
