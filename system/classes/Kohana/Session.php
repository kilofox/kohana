<?php

/**
 * Base session class.
 *
 * @package    Kohana
 * @category   Session
 * @author     Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license    https://kohana.top/license
 */
abstract class Kohana_Session
{
    /**
     * @var  string  default session adapter
     */
    public static $default = 'native';

    /**
     * @var  array  session instances
     */
    public static $instances = [];

    /**
     * Creates a singleton session of the given type. Some session types
     * (native, database) also support restarting a session by passing a
     * session id as the second parameter.
     *
     *     $session = Session::instance();
     *
     * [!!] [Session::write] will automatically be called when the request ends.
     *
     * @param string $type type of session (native, cookie, etc.)
     * @param string $id session identifier
     * @return  Session
     * @throws Kohana_Exception
     * @uses    Kohana::$config
     */
    public static function instance($type = null, $id = null)
    {
        if ($type === null) {
            // Use the default type
            $type = Session::$default;
        }

        if (!isset(Session::$instances[$type])) {
            // Load the configuration for this type
            $config = Kohana::$config->load('session')->get($type);

            // Set the session class name
            $class = 'Session_' . ucfirst($type);

            // Create a new session instance
            Session::$instances[$type] = $session = new $class($config, $id);

            // Write the session at shutdown
            register_shutdown_function([$session, 'write']);
        }

        return Session::$instances[$type];
    }

    /**
     * @var  string  cookie name
     */
    protected $_name = 'session';

    /**
     * @var  int  cookie lifetime
     */
    protected $_lifetime = 0;

    /**
     * @var  bool  encrypt session data?
     */
    protected $_encrypted = false;

    /**
     * @var  array  session data
     */
    protected $_data = [];

    /**
     * @var  bool  session destroyed?
     */
    protected $_destroyed = false;

    /**
     * Overloads the name, lifetime, and encrypted session settings.
     *
     * [!!] Sessions can only be created using the [Session::instance] method.
     *
     * @param array|null $config configuration
     * @param string|null $id session id
     * @throws Session_Exception
     * @uses    Session::read
     */
    public function __construct(array $config = null, $id = null)
    {
        if (isset($config['name'])) {
            // Cookie name to store the session id in
            $this->_name = (string) $config['name'];
        }

        if (isset($config['lifetime'])) {
            // Cookie lifetime
            $this->_lifetime = (int) $config['lifetime'];
        }

        if (isset($config['encrypted'])) {
            if ($config['encrypted'] === true) {
                // Use the default Encrypt instance
                $config['encrypted'] = 'default';
            }

            // Enable or disable encryption of data
            $this->_encrypted = $config['encrypted'];
        }

        // Load the session
        $this->read($id);
    }

    /**
     * Session object is rendered to a serialized string. If encryption is
     * enabled, the session will be encrypted. If not, the output string will
     * be encoded.
     *
     *     echo $session;
     *
     * @return  string
     * @throws Kohana_Exception
     * @uses    Encrypt::encode
     */
    public function __toString()
    {
        // Serialize the data array
        $data = $this->_serialize($this->_data);

        if ($this->_encrypted) {
            // Encrypt the data using the default key
            $data = Encrypt::instance($this->_encrypted)->encode($data);
        } else {
            // Encode the data
            $data = $this->_encode($data);
        }

        return $data;
    }

    /**
     * Returns the current session array. The returned array can also be
     * assigned by reference.
     *
     *     // Get a copy of the current session data
     *     $data = $session->as_array();
     *
     *     // Assign by reference for modification
     *     $data =& $session->as_array();
     *
     * @return  array
     */
    public function & as_array()
    {
        return $this->_data;
    }

    /**
     * Get the current session id, if the session supports it.
     *
     *     $id = $session->id();
     *
     * [!!] Not all session types have ids.
     *
     * @return  string
     * @since   3.0.8
     */
    public function id()
    {
        return null;
    }

    /**
     * Get the current session cookie name.
     *
     *     $name = $session->name();
     *
     * @return  string
     * @since   3.0.8
     */
    public function name()
    {
        return $this->_name;
    }

    /**
     * Get a variable from the session array.
     *
     *     $foo = $session->get('foo');
     *
     * @param   string  $key        variable name
     * @param   mixed   $default    default value to return
     * @return  mixed
     */
    public function get($key, $default = null)
    {
        return array_key_exists($key, $this->_data) ? $this->_data[$key] : $default;
    }

    /**
     * Get and delete a variable from the session array.
     *
     *     $bar = $session->get_once('bar');
     *
     * @param   string  $key        variable name
     * @param   mixed   $default    default value to return
     * @return  mixed
     */
    public function get_once($key, $default = null)
    {
        $value = $this->get($key, $default);

        unset($this->_data[$key]);

        return $value;
    }

    /**
     * Set a variable in the session array.
     *
     *     $session->set('foo', 'bar');
     *
     * @param   string  $key    variable name
     * @param   mixed   $value  value
     * @return  $this
     */
    public function set($key, $value)
    {
        $this->_data[$key] = $value;

        return $this;
    }

    /**
     * Set a variable by reference.
     *
     *     $session->bind('foo', $foo);
     *
     * @param   string  $key    variable name
     * @param   mixed   $value  referenced value
     * @return  $this
     */
    public function bind($key, & $value)
    {
        $this->_data[$key] = & $value;

        return $this;
    }

    /**
     * Removes a variable in the session array.
     *
     *     $session->delete('foo');
     *
     * @param string ...$keys variable name
     * @return  $this
     */
    public function delete(...$keys)
    {
        foreach ($keys as $key) {
            unset($this->_data[$key]);
        }

        return $this;
    }

    /**
     * Loads existing session data.
     *
     *     $session->read();
     *
     * @param string $id session id
     * @return  void
     * @throws Session_Exception
     */
    public function read($id = null)
    {
        try {
            if (is_string($data = $this->_read($id))) {
                if ($this->_encrypted) {
                    // Decrypt the data using the default key
                    $data = Encrypt::instance($this->_encrypted)->decode($data);
                } else {
                    // Decode the data
                    $data = $this->_decode($data);
                }

                // Unserialize the data
                $data = $this->_unserialize($data);
            } else {
                // Ignore these, session is valid, likely no data though.
            }
        } catch (Exception $e) {
            // Error reading the session, usually a corrupt session.
            throw new Session_Exception('Error reading session data.', null, Session_Exception::SESSION_CORRUPT);
        }

        if (is_array($data)) {
            // Load the data locally
            $this->_data = $data;
        }
    }

    /**
     * Generates a new session id and returns it.
     *
     *     $id = $session->regenerate();
     *
     * @return  string
     */
    public function regenerate()
    {
        return $this->_regenerate();
    }

    /**
     * Sets the last_active timestamp and saves the session.
     *
     *     $session->write();
     *
     * [!!] Any errors that occur during session writing will be logged,
     * but not displayed, because sessions are written after output has
     * been sent.
     *
     * @return  bool
     * @uses    Kohana::$log
     */
    public function write()
    {
        if (headers_sent() || $this->_destroyed) {
            // Session cannot be written when the headers are sent or when
            // the session has been destroyed
            return false;
        }

        // Set the last active timestamp
        $this->_data['last_active'] = time();

        try {
            return $this->_write();
        } catch (Exception $e) {
            // Log & ignore all errors when a write fails
            Kohana::$log->add(Log::ERROR, Kohana_Exception::text($e))->write();

            return false;
        }
    }

    /**
     * Completely destroy the current session.
     *
     *     $success = $session->destroy();
     *
     * @return bool
     */
    public function destroy()
    {
        if ($this->_destroyed === false) {
            if ($this->_destroyed = $this->_destroy()) {
                // The session has been destroyed, clear all data
                $this->_data = [];
            }
        }

        return $this->_destroyed;
    }

    /**
     * Restart the session.
     *
     *     $success = $session->restart();
     *
     * @return bool
     */
    public function restart()
    {
        if ($this->_destroyed === false) {
            // Wipe out the current session.
            $this->destroy();
        }

        // Allow the new session to be saved
        $this->_destroyed = false;

        return $this->_restart();
    }

    /**
     * Serializes the session data.
     *
     * @param   array  $data  data
     * @return  string
     */
    protected function _serialize($data)
    {
        return serialize($data);
    }

    /**
     * Unserializes the session data.
     *
     * @param   string  $data  data
     * @return  array
     */
    protected function _unserialize($data)
    {
        return unserialize($data);
    }

    /**
     * Encodes the session data using [base64_encode].
     *
     * @param   string  $data  data
     * @return  string
     */
    protected function _encode($data)
    {
        return base64_encode($data);
    }

    /**
     * Decodes the session data using [base64_decode].
     *
     * @param   string  $data  data
     * @return  string
     */
    protected function _decode($data)
    {
        return base64_decode($data);
    }

    /**
     * Loads the raw session data string and returns it.
     *
     * @param   string  $id session id
     * @return  string
     */
    abstract protected function _read($id = null);
    /**
     * Generate a new session id and return it.
     *
     * @return  string
     */
    abstract protected function _regenerate();
    /**
     * Writes the current session.
     *
     * @return bool
     */
    abstract protected function _write();
    /**
     * Destroys the current session.
     *
     * @return bool
     */
    abstract protected function _destroy();
    /**
     * Restarts the current session.
     *
     * @return bool
     */
    abstract protected function _restart();
}
