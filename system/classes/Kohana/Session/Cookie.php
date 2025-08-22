<?php

/**
 * Cookie-based session class.
 *
 * @package    Kohana
 * @category   Session
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Session_Cookie extends Session
{
    /**
     * @param string|null $id Session ID
     * @return  string
     * @throws Kohana_Exception
     */
    protected function _read(string $id = null)
    {
        return Cookie::get($this->_name);
    }

    /**
     * @return  null
     */
    protected function _regenerate()
    {
        // Cookie sessions have no id
        return null;
    }

    /**
     * @return  bool
     * @throws Kohana_Exception
     */
    protected function _write()
    {
        return Cookie::set($this->_name, $this->__toString(), $this->_lifetime);
    }

    /**
     * @return  bool
     */
    protected function _restart()
    {
        return true;
    }

    /**
     * @return  bool
     */
    protected function _destroy()
    {
        return Cookie::delete($this->_name);
    }

}
