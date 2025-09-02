<?php

/**
 * Object used for caching the results of select queries.  See [Results](/database/results#select-cached) for usage and examples.
 *
 * @package    Kohana/Database
 * @category   Query/Result
 * @author     Kohana Team
 * @copyright  (c) 2009 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Database_Result_Cached extends Database_Result
{
    public function __construct(array $result, $sql, $as_object = null)
    {
        parent::__construct($result, $sql, $as_object);

        // Find the number of rows in the result
        $this->_total_rows = count($result);
    }

    public function __destruct()
    {
        // Cached results do not use resources
    }

    public function cached(): Database_Result
    {
        return $this;
    }

    public function seek($offset): bool
    {
        if ($this->offsetExists($offset)) {
            $this->_current_row = $offset;

            return true;
        } else {
            return false;
        }
    }

    public function current()
    {
        // Return an array of the row
        return $this->valid() ? $this->_result[$this->_current_row] : null;
    }

}
