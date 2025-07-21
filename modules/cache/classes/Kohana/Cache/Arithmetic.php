<?php

/**
 * Kohana Cache Arithmetic Interface, for basic cache integer based
 * arithmetic, addition and subtraction
 * 
 * @package    Kohana/Cache
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2009-2012 Kohana Team
 * @license    https://kohana.top/license
 * @since      3.2.0
 */
interface Kohana_Cache_Arithmetic
{
    /**
     * Increments a given value by the step value supplied.
     * Useful for shared counters and other persistent integer based
     * tracking.
     *
     * @param string $id id of cache entry to increment
     * @param int $step step value to increment by
     * @return  integer
     * @return bool
     */
    public function increment($id, $step = 1);
    /**
     * Decrements a given value by the step value supplied.
     * Useful for shared counters and other persistent integer based
     * tracking.
     *
     * @param string $id id of cache entry to decrement
     * @param int $step step value to decrement by
     * @return  integer
     * @return bool
     */
    public function decrement($id, $step = 1);
}
