<?php

/**
 * Database query wrapper.  See [Parameterized Statements](database/query/parameterized) for usage and examples.
 *
 * @package    Kohana/Database
 * @category   Query
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Database_Query
{
    // Query type
    protected $_type;
    // Execute the query during a cache hit
    protected $_force_execute = false;
    // Cache lifetime
    protected $_lifetime = null;
    // SQL statement
    protected $_sql;
    // Quoted query parameters
    protected $_parameters = [];
    // Return results as associative arrays or objects
    protected $_as_object = false;
    // Parameters for __construct when using object results
    protected $_object_params = [];

    /**
     * Creates a new SQL query of the specified type.
     *
     * @param int $type Query type: Database::SELECT, Database::INSERT, etc.
     * @param string $sql Query string
     * @return  void
     */
    public function __construct(int $type, string $sql)
    {
        $this->_type = $type;
        $this->_sql = $sql;
    }

    /**
     * Return the SQL query string.
     *
     * @return  string
     */
    public function __toString()
    {
        try {
            // Return the SQL string
            return $this->compile(Database::instance());
        } catch (Exception $e) {
            return Kohana_Exception::text($e);
        }
    }

    /**
     * Get the type of the query.
     *
     * @return int
     */
    public function type(): int
    {
        return $this->_type;
    }

    /**
     * Enables the query to be cached for a specified amount of time.
     *
     * @param int|null $lifetime Number of seconds to cache, 0 deletes it from the cache
     * @param bool $force whether to execute the query during a cache hit
     * @return  $this
     * @uses    Kohana::$cache_life
     */
    public function cached(int $lifetime = null, bool $force = false): Kohana_Database_Query
    {
        if ($lifetime === null) {
            // Use the global setting
            $lifetime = Kohana::$cache_life;
        }

        $this->_force_execute = $force;
        $this->_lifetime = $lifetime;

        return $this;
    }

    /**
     * Returns results as associative arrays
     *
     * @return  $this
     */
    public function as_assoc(): Kohana_Database_Query
    {
        $this->_as_object = false;

        $this->_object_params = [];

        return $this;
    }

    /**
     * Returns results as objects
     *
     * @param string|bool $class classname or true for stdClass
     * @param array|null $params
     * @return  $this
     */
    public function as_object($class = true, array $params = null): Kohana_Database_Query
    {
        $this->_as_object = $class;

        if ($params) {
            // Add object parameters
            $this->_object_params = $params;
        }

        return $this;
    }

    /**
     * Set the value of a parameter in the query.
     *
     * @param string $param Parameter key to replace
     * @param   mixed    $value  value to use
     * @return  $this
     */
    public function param(string $param, $value): Kohana_Database_Query
    {
        // Add or overload a new parameter
        $this->_parameters[$param] = $value;

        return $this;
    }

    /**
     * Bind a variable to a parameter in the query.
     *
     * @param string $param Parameter key to replace
     * @param   mixed   $var    variable to use
     * @return  $this
     */
    public function bind(string $param, &$var): Kohana_Database_Query
    {
        // Bind a value to a variable
        $this->_parameters[$param] = &$var;

        return $this;
    }

    /**
     * Add multiple parameters to the query.
     *
     * @param   array  $params  list of parameters
     * @return  $this
     */
    public function parameters(array $params): Kohana_Database_Query
    {
        // Merge the new parameters in
        $this->_parameters = $params + $this->_parameters;

        return $this;
    }

    /**
     * Compile the SQL query and return it. Replaces any parameters with their
     * given values.
     *
     * @param mixed $db Database instance or name of instance
     * @return  string
     * @throws Kohana_Exception
     */
    public function compile($db = null): string
    {
        if (!is_object($db)) {
            // Get the database instance
            $db = Database::instance($db);
        }

        // Import the SQL locally
        $sql = $this->_sql;

        if (!empty($this->_parameters)) {
            // Quote all the values
            $values = array_map([$db, 'quote'], $this->_parameters);

            // Replace the values in the SQL
            $sql = strtr($sql, $values);
        }

        return $sql;
    }

    /**
     * Execute the current query on the given database.
     *
     * @param mixed $db Database instance or name of instance
     * @param string|null $as_object Result object classname, true for stdClass or false for array
     * @param array|null $object_params Result object constructor arguments
     * @return Database_Result|array|int Database_Result for SELECT queries, insert ID for INSERT queries, number of affected rows for all other queries.
     * @throws Kohana_Exception
     */
    public function execute($db = null, string $as_object = null, array $object_params = null)
    {
        if (!is_object($db)) {
            // Get the database instance
            $db = Database::instance($db);
        }

        if ($as_object === null) {
            $as_object = $this->_as_object;
        }

        if ($object_params === null) {
            $object_params = $this->_object_params;
        }

        // Compile the SQL query
        $sql = $this->compile($db);

        if ($this->_lifetime !== null && $this->_type === Database::SELECT) {
            // Set the cache key based on the database instance name and SQL
            $cache_key = 'Database::query("' . $db . '", "' . $sql . '")';

            // Read the cache first to delete a possible hit with lifetime <= 0
            if (($result = Kohana::cache($cache_key, null, $this->_lifetime)) !== null && !$this->_force_execute) {
                // Return a cached result
                return new Database_Result_Cached($result, $sql, $as_object);
            }
        }

        // Execute the query
        $result = $db->query($this->_type, $sql, $as_object, $object_params);

        if (isset($cache_key) && $this->_lifetime > 0) {
            // Cache the result array
            Kohana::cache($cache_key, $result->as_array(), $this->_lifetime);
        }

        return $result;
    }

}
