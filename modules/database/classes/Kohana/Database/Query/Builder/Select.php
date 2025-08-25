<?php

/**
 * Database query builder for SELECT statements. See [Query Builder](/database/query/builder) for usage and examples.
 *
 * @package    Kohana/Database
 * @category   Query
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Database_Query_Builder_Select extends Database_Query_Builder_Where
{
    // SELECT ...
    protected $_select = [];
    // DISTINCT
    protected $_distinct = false;
    // FROM ...
    protected $_from = [];
    // JOIN ...
    protected $_join = [];
    // GROUP BY ...
    protected $_group_by = [];
    // HAVING ...
    protected $_having = [];
    // OFFSET ...
    protected $_offset = null;
    // UNION ...
    protected $_union = [];
    // The last JOIN statement created
    protected $_last_join;

    /**
     * Sets the initial columns to select from.
     *
     * @param array|null $columns column list
     */
    public function __construct(array $columns = null)
    {
        if (!empty($columns)) {
            // Set the initial columns
            $this->_select = $columns;
        }

        // Start the query with no actual SQL statement
        parent::__construct(Database::SELECT, '');
    }

    /**
     * Enables or disables selecting only unique columns using "SELECT DISTINCT"
     *
     * @param bool $value enable or disable distinct columns
     * @return  $this
     */
    public function distinct(bool $value): Kohana_Database_Query_Builder_Select
    {
        $this->_distinct = (bool) $value;

        return $this;
    }

    /**
     * Choose the columns to select from.
     *
     * @param mixed ...$columns column name or [$column, $alias] or object
     * @return  $this
     */
    public function select(...$columns): Kohana_Database_Query_Builder_Select
    {
        $this->_select = array_merge($this->_select, $columns);

        return $this;
    }

    /**
     * Choose the columns to select from, using an array.
     *
     * @param   array  $columns  list of column names or aliases
     * @return  $this
     */
    public function select_array(array $columns): Kohana_Database_Query_Builder_Select
    {
        $this->_select = array_merge($this->_select, $columns);

        return $this;
    }

    /**
     * Choose the tables to select "FROM ..."
     *
     * @param mixed ...$tables table name or [$table, $alias] or object
     * @return  $this
     */
    public function from(...$tables): Kohana_Database_Query_Builder_Select
    {
        $this->_from = array_merge($this->_from, $tables);

        return $this;
    }

    /**
     * Adds addition tables to "JOIN ...".
     *
     * @param   mixed   $table  column name or [$column, $alias] or object
     * @param string|null $type Join type (LEFT, RIGHT, INNER, etc.)
     * @return  $this
     */
    public function join($table, string $type = null): Kohana_Database_Query_Builder_Select
    {
        $this->_join[] = $this->_last_join = new Database_Query_Builder_Join($table, $type);

        return $this;
    }

    /**
     * Adds "ON ..." conditions for the last created JOIN statement.
     *
     * @param   mixed   $c1  column name or [$column, $alias] or object
     * @param string $op Logic operator
     * @param   mixed   $c2  column name or [$column, $alias] or object
     * @return  $this
     */
    public function on($c1, string $op, $c2): Kohana_Database_Query_Builder_Select
    {
        $this->_last_join->on($c1, $op, $c2);

        return $this;
    }

    /**
     * Adds "USING ..." conditions for the last created JOIN statement.
     *
     * @param string ...$columns column name
     * @return  $this
     */
    public function using(...$columns): Kohana_Database_Query_Builder_Select
    {
        call_user_func_array([$this->_last_join, 'using'], $columns);

        return $this;
    }

    /**
     * Creates a "GROUP BY ..." filter.
     *
     * @param mixed ...$columns column name or [$column, $alias] or object
     * @return  $this
     */
    public function group_by(...$columns): Kohana_Database_Query_Builder_Select
    {
        $this->_group_by = array_merge($this->_group_by, $columns);

        return $this;
    }

    /**
     * Alias of and_having()
     *
     * @param   mixed   $column  column name or [$column, $alias] or object
     * @param string $op Logic operator
     * @param   mixed   $value   column value
     * @return  $this
     */
    public function having($column, string $op, $value = null): Kohana_Database_Query_Builder_Select
    {
        return $this->and_having($column, $op, $value);
    }

    /**
     * Creates a new "AND HAVING" condition for the query.
     *
     * @param   mixed   $column  column name or [$column, $alias] or object
     * @param string $op Logic operator
     * @param   mixed   $value   column value
     * @return  $this
     */
    public function and_having($column, string $op, $value = null): Kohana_Database_Query_Builder_Select
    {
        $this->_having[] = ['AND' => [$column, $op, $value]];

        return $this;
    }

    /**
     * Creates a new "OR HAVING" condition for the query.
     *
     * @param   mixed   $column  column name or [$column, $alias] or object
     * @param string $op Logic operator
     * @param   mixed   $value   column value
     * @return  $this
     */
    public function or_having($column, string $op, $value = null): Kohana_Database_Query_Builder_Select
    {
        $this->_having[] = ['OR' => [$column, $op, $value]];

        return $this;
    }

    /**
     * Alias of and_having_open()
     *
     * @return  $this
     */
    public function having_open(): Kohana_Database_Query_Builder_Select
    {
        return $this->and_having_open();
    }

    /**
     * Opens a new "AND HAVING (...)" grouping.
     *
     * @return  $this
     */
    public function and_having_open(): Kohana_Database_Query_Builder_Select
    {
        $this->_having[] = ['AND' => '('];

        return $this;
    }

    /**
     * Opens a new "OR HAVING (...)" grouping.
     *
     * @return  $this
     */
    public function or_having_open(): Kohana_Database_Query_Builder_Select
    {
        $this->_having[] = ['OR' => '('];

        return $this;
    }

    /**
     * Closes an open "AND HAVING (...)" grouping.
     *
     * @return  $this
     */
    public function having_close(): Kohana_Database_Query_Builder_Select
    {
        return $this->and_having_close();
    }

    /**
     * Closes an open "AND HAVING (...)" grouping.
     *
     * @return  $this
     */
    public function and_having_close(): Kohana_Database_Query_Builder_Select
    {
        $this->_having[] = ['AND' => ')'];

        return $this;
    }

    /**
     * Closes an open "OR HAVING (...)" grouping.
     *
     * @return  $this
     */
    public function or_having_close(): Kohana_Database_Query_Builder_Select
    {
        $this->_having[] = ['OR' => ')'];

        return $this;
    }

    /**
     * Adds a UNION clause.
     *
     * @param mixed $select If a string, it must be the name of a table.
     * Otherwise, it must be an instance of Database_Query_Builder_Select.
     * @param bool $all Determines if it's a UNION or UNION ALL clause.
     * @return $this
     * @throws Kohana_Exception
     */
    public function union($select, bool $all = true): Kohana_Database_Query_Builder_Select
    {
        if (is_string($select)) {
            $select = DB::select()->from($select);
        }
        if (!$select instanceof Database_Query_Builder_Select)
            throw new Kohana_Exception('first parameter must be a string or an instance of Database_Query_Builder_Select');
        $this->_union [] = ['select' => $select, 'all' => $all];
        return $this;
    }

    /**
     * Start returning results after "OFFSET ..."
     *
     * @param int|null $number Starting result number or null to reset
     * @return  $this
     */
    public function offset(?int $number): Kohana_Database_Query_Builder_Select
    {
        $this->_offset = $number;

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
    public function compile($db = null): string
    {
        if (!is_object($db)) {
            // Get the database instance
            $db = Database::instance($db);
        }

        // Callback to quote columns
        $quote_column = [$db, 'quote_column'];

        // Callback to quote tables
        $quote_table = [$db, 'quote_table'];

        // Start a selection query
        $query = 'SELECT ';

        if ($this->_distinct === true) {
            // Select only unique results
            $query .= 'DISTINCT ';
        }

        if (empty($this->_select)) {
            // Select all columns
            $query .= '*';
        } else {
            // Select all columns
            $query .= implode(', ', array_unique(array_map($quote_column, $this->_select)));
        }

        if (!empty($this->_from)) {
            // Set tables to select from
            $query .= ' FROM ' . implode(', ', array_unique(array_map($quote_table, $this->_from)));
        }

        if (!empty($this->_join)) {
            // Add tables to join
            $query .= ' ' . $this->_compile_join($db, $this->_join);
        }

        if (!empty($this->_where)) {
            // Add selection conditions
            $query .= ' WHERE ' . $this->_compile_conditions($db, $this->_where);
        }

        if (!empty($this->_group_by)) {
            // Add grouping
            $query .= ' ' . $this->_compile_group_by($db, $this->_group_by);
        }

        if (!empty($this->_having)) {
            // Add filtering conditions
            $query .= ' HAVING ' . $this->_compile_conditions($db, $this->_having);
        }

        if (!empty($this->_order_by)) {
            // Add sorting
            $query .= ' ' . $this->_compile_order_by($db, $this->_order_by);
        }

        if ($this->_limit !== null) {
            // Add limiting
            $query .= ' LIMIT ' . $this->_limit;
        }

        if ($this->_offset !== null) {
            // Add offsets
            $query .= ' OFFSET ' . $this->_offset;
        }

        if (!empty($this->_union)) {
            $query = '(' . $query . ')';
            foreach ($this->_union as $u) {
                $query .= ' UNION ';
                if ($u['all'] === true) {
                    $query .= 'ALL ';
                }
                $query .= '(' . $u['select']->compile($db) . ')';
            }
        }

        $this->_sql = $query;

        return parent::compile($db);
    }

    public function reset(): Kohana_Database_Query_Builder
    {
        $this->_select = $this->_from = $this->_join = $this->_where = $this->_group_by = $this->_having = $this->_order_by = $this->_union = [];

        $this->_distinct = false;

        $this->_limit = $this->_offset = $this->_last_join = null;

        $this->_parameters = [];

        $this->_sql = null;

        return $this;
    }

}
