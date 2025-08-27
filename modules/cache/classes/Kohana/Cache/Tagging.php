<?php

/**
 * Kohana Cache Tagging Interface
 *
 * @package    Kohana/Cache
 * @category   Base
 * @author     Kohana Team
 * @copyright  (c) 2009-2012 Kohana Team
 * @license    https://kohana.top/license
 */
interface Kohana_Cache_Tagging
{
    /**
     * Set a value based on an id. Optionally add tags.
     *
     * Note : Some caching engines do not support
     * tagging
     *
     * @param string $id id
     * @param mixed $data data
     * @param int|null $lifetime lifetime [Optional]
     * @param array|null $tags tags [Optional]
     * @return  bool
     */
    public function set_with_tags($id, $data, $lifetime = null, array $tags = null);
    /**
     * Delete cache entries based on a tag
     *
     * @param string $tag Tag label identifying cache entries to be deleted.
     */
    public function delete_tag($tag);
    /**
     * Find cache entries based on a tag
     *
     * @param string $tag Tag label used to find associated cache entries.
     * @return  array
     */
    public function find($tag);
}
