<?php

/**
 * Database query builder for INSERT statements. See [Query Builder](/database/query/builder) for usage and examples.
 *
 * @package    Kohana/Database
 * @category   Query
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Database_Query_Builder_Insert extends Database_Query_Builder
{
    // INSERT INTO ...
    protected $_table;
    // (...)
    protected $_columns = [];
    // VALUES (...)
    protected $_values = [];

    /**
     * Set the table and columns for an insert.
     *
     * @param mixed $table table name or [$table, $alias] or object
     * @param array|null $columns column names
     * @throws Kohana_Exception
     */
    public function __construct($table = null, array $columns = null)
    {
        if ($table) {
            // Set the initial table name
            $this->table($table);
        }

        if ($columns) {
            // Set the column names
            $this->_columns = $columns;
        }

        // Start the query with no SQL
        parent::__construct(Database::INSERT, '');
    }

    /**
     * Sets the table to insert into.
     *
     * @param string $table table name
     * @return  $this
     * @throws Kohana_Exception
     */
    public function table(string $table): Kohana_Database_Query_Builder_Insert
    {
        if (!is_string($table))
            throw new Kohana_Exception('INSERT INTO syntax does not allow table aliasing');

        $this->_table = $table;

        return $this;
    }

    /**
     * Set the columns that will be inserted.
     *
     * @param   array  $columns  column names
     * @return  $this
     */
    public function columns(array $columns): Kohana_Database_Query_Builder_Insert
    {
        $this->_columns = $columns;

        return $this;
    }

    /**
     * Adds or overwrites values. Multiple value sets can be added.
     *
     * @param array ...$values values list
     * @return  $this
     * @throws Kohana_Exception
     */
    public function values(array ...$values): Kohana_Database_Query_Builder_Insert
    {
        if (!is_array($this->_values)) {
            throw new Kohana_Exception('INSERT INTO ... SELECT statements cannot be combined with INSERT INTO ... VALUES');
        }

        foreach ($values as $value) {
            $this->_values[] = $value;
        }

        return $this;
    }

    /**
     * Use a sub-query to for the inserted values.
     *
     * @param Database_Query $query Database_Query of SELECT type
     * @return  $this
     * @throws Kohana_Exception
     */
    public function select(Database_Query $query): Kohana_Database_Query_Builder_Insert
    {
        if ($query->type() !== Database::SELECT) {
            throw new Kohana_Exception('Only SELECT queries can be combined with INSERT queries');
        }

        $this->_values = $query;

        return $this;
    }

    /**
     * Compile the SQL query and return it.
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

        // Start an insertion query
        $query = 'INSERT INTO ' . $db->quote_table($this->_table);

        // Add the column names
        $query .= ' (' . implode(', ', array_map([$db, 'quote_column'], $this->_columns)) . ') ';

        if (is_array($this->_values)) {
            $groups = [];
            foreach ($this->_values as $group) {
                foreach ($group as $offset => $value) {
                    if ((is_string($value) && array_key_exists($value, $this->_parameters)) === false) {
                        // Quote the value, it is not a parameter
                        $group[$offset] = $db->quote($value);
                    }
                }

                $groups[] = '(' . implode(', ', $group) . ')';
            }

            // Add the values
            $query .= 'VALUES ' . implode(', ', $groups);
        } else {
            // Add the sub-query
            $query .= $this->_values;
        }

        $this->_sql = $query;

        return parent::compile($db);
    }

    public function reset(): Kohana_Database_Query_Builder
    {
        $this->_table = null;

        $this->_columns = $this->_values = [];

        $this->_parameters = [];

        $this->_sql = null;

        return $this;
    }

}
