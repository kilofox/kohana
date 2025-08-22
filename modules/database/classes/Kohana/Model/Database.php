<?php

/**
 * Database Model base class.
 *
 * @package    Kohana/Database
 * @category   Models
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 */
abstract class Kohana_Model_Database extends Model
{
    /**
     * Create a new model instance. A [Database] instance or configuration
     * group name can be passed to the model. If no database is defined, the
     * "default" database group will be used.
     *
     *     $model = Model::factory($name);
     *
     * @param string $name Model name
     * @param   mixed    $db    Database instance object or string
     * @return  Model
     */
    public static function factory(string $name, $db = null)
    {
        // Add the model prefix
        $class = 'Model_' . $name;

        return new $class($db);
    }

    // Database instance
    protected $_db;

    /**
     * Loads the database.
     *
     *     $model = new Foo_Model($db);
     *
     * @param mixed $db Database instance object or string
     * @return  void
     * @throws Kohana_Exception
     */
    public function __construct($db = null)
    {
        if ($db) {
            // Set the instance or name
            $this->_db = $db;
        } elseif (!$this->_db) {
            // Use the default name
            $this->_db = Database::$default;
        }

        if (is_string($this->_db)) {
            // Load the database
            $this->_db = Database::instance($this->_db);
        }
    }

}
