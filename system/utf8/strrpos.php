<?php

/**
 * UTF8::strrpos
 *
 * @package    Kohana
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @copyright  (c) 2005 Harry Fuecks
 * @license    https://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
function _strrpos($str, $search, $offset = 0)
{
    $offset = (int) $offset;

    if (UTF8::is_ascii($str) && UTF8::is_ascii($search))
        return strrpos($str, $search, $offset);

    if ($offset === 0) {
        $array = explode($search, $str, -1);
        return isset($array[0]) ? UTF8::strlen(implode($search, $array)) : false;
    }

    $str = UTF8::substr($str, $offset);
    $pos = UTF8::strrpos($str, $search);
    return $pos === false ? false : $pos + $offset;
}
