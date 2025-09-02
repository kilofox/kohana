<?php

/**
 * Interface for config writers
 *
 * Specifies the methods that a config writer must implement
 *
 * @package Kohana
 * @author  Kohana Team
 * @copyright  (c) 2008-2014 Kohana Team
 * @license    https://kohana.top/license
 */
interface Kohana_Config_Writer extends Kohana_Config_Source
{
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
     */
    public function write(string $group, string $key, $config): bool;
}
