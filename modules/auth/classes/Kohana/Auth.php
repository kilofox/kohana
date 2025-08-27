<?php

/**
 * User authorization library. Handles user login and logout, as well as secure
 * password hashing.
 *
 * @package    Kohana/Auth
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    https://kohana.top/license
 */
abstract class Kohana_Auth
{
    // Auth instances
    protected static $_instance;

    /**
     * Singleton pattern
     *
     * @return Auth
     * @throws Kohana_Exception
     */
    public static function instance()
    {
        if (!isset(Auth::$_instance)) {
            // Load the configuration for this type
            $config = Kohana::$config->load('auth');

            if (!$type = $config->get('driver')) {
                $type = 'file';
            }

            // Set the session class name
            $class = 'Auth_' . ucfirst($type);

            // Create a new session instance
            Auth::$_instance = new $class($config);
        }

        return Auth::$_instance;
    }

    protected $_session;
    protected $_config;

    /**
     * Loads Session and configuration options.
     *
     * @param array $config Config Options
     * @return  void
     * @throws Kohana_Exception
     */
    public function __construct(array $config = [])
    {
        // Save the config in the object
        $this->_config = $config;

        $this->_session = Session::instance($this->_config['session_type']);
    }

    abstract protected function _login($username, $password, $remember);
    abstract public function password($username);
    abstract public function check_password($password);
    /**
     * Gets the currently logged-in user from the session.
     * Returns null if no user is currently logged in.
     *
     * @param   mixed  $default  Default value to return if the user is currently not logged in.
     * @return  mixed
     */
    public function get_user($default = null)
    {
        return $this->_session->get($this->_config['session_key'], $default);
    }

    /**
     * Attempt to log in a user by using an ORM object and plain-text password.
     *
     * @param string $username Username to log in
     * @param string $password Password to check against
     * @param bool $remember Enable autologin
     * @return  bool
     */
    public function login($username, $password, $remember = false)
    {
        if (empty($password))
            return false;

        return $this->_login($username, $password, $remember);
    }

    /**
     * Log out a user by removing the related session variables.
     *
     * @param bool $destroy     Completely destroy the session
     * @param bool $logout_all  Remove all tokens for user
     * @return  bool
     */
    public function logout($destroy = false, $logout_all = false)
    {
        if ($destroy === true) {
            // Destroy the session completely
            $this->_session->destroy();
        } else {
            // Remove the user from the session
            $this->_session->delete($this->_config['session_key']);

            // Regenerate session_id
            $this->_session->regenerate();
        }

        // Double check
        return !$this->logged_in();
    }

    /**
     * Check if there is an active session. Optionally allows checking for a
     * specific role.
     *
     * @param string|null $role Role name
     * @return  bool
     */
    public function logged_in($role = null)
    {
        return $this->get_user() !== null;
    }

    /**
     * Perform a hmac hash, using the configured method.
     *
     * @param string $str string to hash
     * @return  string
     * @throws Kohana_Exception
     */
    public function hash($str)
    {
        if (!$this->_config['hash_key'])
            throw new Kohana_Exception('A valid hash key must be set in your auth config.');

        return hash_hmac($this->_config['hash_method'], $str, $this->_config['hash_key']);
    }

    protected function complete_login($user)
    {
        // Regenerate session_id
        $this->_session->regenerate();

        // Store username in session
        $this->_session->set($this->_config['session_key'], $user);

        return true;
    }

}
