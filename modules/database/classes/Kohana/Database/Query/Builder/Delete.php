<?php

/**
 * Database query builder for DELETE statements. See [Query Builder](/database/query/builder) for usage and examples.
 *
 * @package    Kohana/Database
 * @category   Query
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Database_Query_Builder_Delete extends Database_Query_Builder_Where
{
    // DELETE FROM ...
    protected $_table;

    /**
     * Set the table for a deletion.
     *
     * @param   mixed  $table  table name or [$table, $alias] or object
     * @return  void
     */
    public function __construct($table = null)
    {
        if ($table) {
            // Set the initial table name
            $this->_table = $table;
        }

        // Start the query with no SQL
        return parent::__construct(Database::DELETE, '');
    }

    /**
     * Sets the table to delete from.
     *
     * @param   mixed  $table  table name or [$table, $alias] or object
     * @return  $this
     */
    public function table($table)
    {
        $this->_table = $table;

        return $this;
    }

    /**
     * Compile the SQL query and return it.
     *
     * @param mixed $db Database instance or name of instance
     * @return  string
     * @throws Database_Exception
     * @throws Kohana_Exception
     */
    public function compile($db = null)
    {
        if (!is_object($db)) {
            // Get the database instance
            $db = Database::instance($db);
        }

        // Start a deletion query
        $query = 'DELETE FROM ' . $db->quote_table($this->_table);

        if (!empty($this->_where)) {
            // Add deletion conditions
            $query .= ' WHERE ' . $this->_compile_conditions($db, $this->_where);
        }

        if (!empty($this->_order_by)) {
            // Add sorting
            $query .= ' ' . $this->_compile_order_by($db, $this->_order_by);
        }

        if ($this->_limit !== null) {
            // Add limiting
            $query .= ' LIMIT ' . $this->_limit;
        }

        $this->_sql = $query;

        return parent::compile($db);
    }

    public function reset()
    {
        $this->_table = null;
        $this->_where = [];

        $this->_parameters = [];

        $this->_sql = null;

        return $this;
    }

}
