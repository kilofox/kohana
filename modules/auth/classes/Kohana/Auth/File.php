<?php

/**
 * File Auth driver.
 * [!!] this Auth driver does not support roles nor autologin.
 *
 * @package    Kohana/Auth
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_Auth_File extends Auth
{
    // User list
    protected $_users;

    /**
     * Constructor loads the user list into the class.
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        // Load user list
        $this->_users = Arr::get($config, 'users', []);
    }

    /**
     * Logs a user in.
     *
     * @param string $username Username
     * @param string $password Password
     * @param bool $remember Enable autologin (not supported)
     * @return bool
     * @throws Kohana_Exception
     */
    protected function _login($username, $password, $remember)
    {
        if (is_string($password)) {
            // Create a hashed password
            $password = $this->hash($password);
        }

        if (isset($this->_users[$username]) && $this->_users[$username] === $password) {
            // Complete the login
            return $this->complete_login($username);
        }

        // Login failed
        return false;
    }

    /**
     * Forces a user to be logged in, without specifying a password.
     *
     * @param   mixed    $username  Username
     * @return  bool
     */
    public function force_login($username)
    {
        // Complete the login
        return $this->complete_login($username);
    }

    /**
     * Get the stored password for a username.
     *
     * @param   mixed   $username  Username
     * @return  string
     */
    public function password($username)
    {
        return Arr::get($this->_users, $username, false);
    }

    /**
     * Compare password with original (plain text). Works for current (logged in) user
     *
     * @param   string   $password  Password
     * @return  bool
     */
    public function check_password($password)
    {
        $username = $this->get_user();

        if ($username === false) {
            return false;
        }

        return $password === $this->password($username);
    }

}
