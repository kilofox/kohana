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
    protected function _read(string $id = null): string
    {
        return Cookie::get($this->_name);
    }

    /**
     * @return  null
     */
    protected function _regenerate(): ?string
    {
        // Cookie sessions have no id
        return null;
    }

    /**
     * @return  bool
     * @throws Kohana_Exception
     */
    protected function _write(): bool
    {
        return Cookie::set($this->_name, $this->__toString(), $this->_lifetime);
    }

    /**
     * @return  bool
     */
    protected function _restart(): bool
    {
        return true;
    }

    /**
     * @return  bool
     */
    protected function _destroy(): bool
    {
        return Cookie::delete($this->_name);
    }

}
