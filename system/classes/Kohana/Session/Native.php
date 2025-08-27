<?php

/**
 * Native PHP session class.
 *
 * @package    Kohana
 * @category   Session
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Session_Native extends Session
{
    /**
     * @return  string
     */
    public function id()
    {
        return session_id();
    }

    /**
     * @param string|null $id Session ID
     * @return  null
     */
    protected function _read($id = null)
    {
        /**
         * session_set_cookie_params will override php ini settings
         * If Cookie::$domain is null or empty and is passed, PHP
         * will override ini and sent cookies with the host name
         * of the server which generated the cookie
         *
         * see issue #3604
         *
         * see https://www.php.net/function.session-set-cookie-params
         * see https://www.php.net/session.configuration#ini.session.cookie-domain
         *
         * set to Cookie::$domain if available, otherwise default to ini setting
         */
        $session_cookie_domain = empty(Cookie::$domain) ? ini_get('session.cookie_domain') : Cookie::$domain;

        // Sync up the session cookie with Cookie parameters
        session_set_cookie_params(
            $this->_lifetime, Cookie::$path, $session_cookie_domain, Cookie::$secure, Cookie::$httponly
        );

        // Do not allow PHP to send Cache-Control headers
        session_cache_limiter(false);

        // Set the session cookie name
        session_name($this->_name);

        if ($id) {
            // Set the session id
            session_id($id);
        }

        // Start the session
        session_start();

        // Use the $_SESSION global for storing data
        $this->_data = & $_SESSION;

        return null;
    }

    /**
     * @return  string
     */
    protected function _regenerate()
    {
        // Regenerate the session id
        session_regenerate_id();

        return session_id();
    }

    /**
     * @return  bool
     */
    protected function _write()
    {
        // Write and close the session
        session_write_close();

        return true;
    }

    /**
     * @return  bool
     */
    protected function _restart()
    {
        // Fire up a new session
        $status = session_start();

        // Use the $_SESSION global for storing data
        $this->_data = & $_SESSION;

        return $status;
    }

    /**
     * @return  bool
     */
    protected function _destroy()
    {
        // Destroy the current session
        session_destroy();

        // Did destruction work?
        $status = !session_id();

        if ($status) {
            // Make sure the session cannot be restarted
            Cookie::delete($this->_name);
        }

        return $status;
    }

}
