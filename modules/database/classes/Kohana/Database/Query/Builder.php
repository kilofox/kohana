<?php

/**
 * Database query builder. See [Query Builder](/database/query/builder) for usage and examples.
 *
 * @package    Kohana/Database
 * @category   Query
 * @author     Kohana Team
 * @copyright  (c) 2008-2009 Kohana Team
 * @license    https://kohana.top/license
 */
abstract class Kohana_Database_Query_Builder extends Database_Query
{
    /**
     * Compiles an array of JOIN statements into an SQL partial.
     *
     * @param Database $db Database instance
     * @param array $joins join statements
     * @return  string
     */
    protected function _compile_join(Database $db, array $joins): string
    {
        $statements = [];

        foreach ($joins as $join) {
            // Compile each of the join statements
            $statements[] = $join->compile($db);
        }

        return implode(' ', $statements);
    }

    /**
     * Compiles an array of conditions into an SQL partial. Used for WHERE
     * and HAVING.
     *
     * @param Database $db Database instance
     * @param array $conditions condition statements
     * @return  string
     * @throws Kohana_Exception
     */
    protected function _compile_conditions(Database $db, array $conditions): string
    {
        $last_condition = null;

        $sql = '';
        foreach ($conditions as $group) {
            // Process groups of conditions
            foreach ($group as $logic => $condition) {
                if ($condition === '(') {
                    if (!empty($sql) && $last_condition !== '(') {
                        // Include logic operator
                        $sql .= ' ' . $logic . ' ';
                    }

                    $sql .= '(';
                } elseif ($condition === ')') {
                    $sql .= ')';
                } else {
                    if (!empty($sql) && $last_condition !== '(') {
                        // Add the logic operator
                        $sql .= ' ' . $logic . ' ';
                    }

                    // Split the condition
                    list($column, $op, $value) = $condition;

                    if ($value === null) {
                        if ($op === '=') {
                            // Convert "val = NULL" to "val IS NULL"
                            $op = 'IS';
                        } elseif ($op === '!=' || $op === '<>') {
                            // Convert "val != NULL" to "val IS NOT NULL"
                            $op = 'IS NOT';
                        }
                    }

                    // Database operators are always uppercase
                    $op = strtoupper($op);

                    if ($op === 'BETWEEN' && is_array($value)) {
                        // BETWEEN always has exactly two arguments
                        list($min, $max) = $value;

                        if ((is_string($min) && array_key_exists($min, $this->_parameters)) === false) {
                            // Quote the value, it is not a parameter
                            $min = $db->quote($min);
                        }

                        if ((is_string($max) && array_key_exists($max, $this->_parameters)) === false) {
                            // Quote the value, it is not a parameter
                            $max = $db->quote($max);
                        }

                        // Quote the min and max value
                        $value = $min . ' AND ' . $max;
                    } elseif ((is_string($value) && array_key_exists($value, $this->_parameters)) === false) {
                        // Quote the value, it is not a parameter
                        $value = $db->quote($value);
                    }

                    if ($column) {
                        if (is_array($column)) {
                            // Use the column name
                            $column = $db->quote_identifier(reset($column));
                        } else {
                            // Apply proper quoting to the column
                            $column = $db->quote_column($column);
                        }
                    }

                    // Append the statement to the query
                    $sql .= trim($column . ' ' . $op . ' ' . $value);
                }

                $last_condition = $condition;
            }
        }

        return $sql;
    }

    /**
     * Compiles an array of set values into an SQL partial. Used for UPDATE.
     *
     * @param Database $db Database instance
     * @param array $values updated values
     * @return  string
     * @throws Kohana_Exception
     */
    protected function _compile_set(Database $db, array $values): string
    {
        $set = [];
        foreach ($values as $group) {
            // Split the set
            list ($column, $value) = $group;

            // Quote the column name
            $column = $db->quote_column($column);

            if ((is_string($value) && array_key_exists($value, $this->_parameters)) === false) {
                // Quote the value, it is not a parameter
                $value = $db->quote($value);
            }

            $set[$column] = $column . ' = ' . $value;
        }

        return implode(', ', $set);
    }

    /**
     * Compiles an array of GROUP BY columns into an SQL partial.
     *
     * @param Database $db Database instance
     * @param array $columns
     * @return  string
     * @throws Kohana_Exception
     */
    protected function _compile_group_by(Database $db, array $columns): string
    {
        $group = [];

        foreach ($columns as $column) {
            if (is_array($column)) {
                // Use the column alias
                $column = $db->quote_identifier(end($column));
            } else {
                // Apply proper quoting to the column
                $column = $db->quote_column($column);
            }

            $group[] = $column;
        }

        return 'GROUP BY ' . implode(', ', $group);
    }

    /**
     * Compiles an array of ORDER BY statements into an SQL partial.
     *
     * @param Database $db Database instance
     * @param array $columns sorting columns
     * @return  string
     * @throws Database_Exception
     * @throws Kohana_Exception
     */
    protected function _compile_order_by(Database $db, array $columns): string
    {
        $sort = [];
        foreach ($columns as $group) {
            list ($column, $direction) = $group;

            if (is_array($column)) {
                // Use the column alias
                $column = $db->quote_identifier(end($column));
            } else {
                // Apply proper quoting to the column
                $column = $db->quote_column($column);
            }

            if ($direction) {
                // Make the direction uppercase
                $direction = strtoupper($direction);

                if (!in_array($direction, ['ASC', 'DESC'])) {
                    throw new Database_Exception('Order direction must be "ASC" or "DESC".');
                }

                $direction = ' ' . $direction;
            }

            $sort[] = $column . $direction;
        }

        return 'ORDER BY ' . implode(', ', $sort);
    }

    /**
     * Reset the current builder status.
     */
    abstract public function reset();
}
