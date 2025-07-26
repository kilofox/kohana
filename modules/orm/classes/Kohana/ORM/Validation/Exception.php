<?php

/**
 * ORM Validation exceptions.
 *
 * @package    Kohana/ORM
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Kohana_ORM_Validation_Exception extends Kohana_Exception
{
    /**
     * Array of validation objects
     * @var array
     */
    protected $_objects = [];

    /**
     * The alias of the main ORM model this exception was created for
     * @var string
     */
    protected $_alias = null;

    /**
     * Constructs a new exception for the specified model
     *
     * @param  string     $alias       The alias to use when looking for error messages
     * @param  Validation $object      The Validation object of the model
     * @param  string     $message     The error message
     * @param  array      $values      The array of values for the error message
     * @param  int $code The error code for the exception
     * @return void
     */
    public function __construct($alias, Validation $object, $message = 'Failed to validate array', array $values = null, $code = 0, Exception $previous = null)
    {
        $this->_alias = $alias;
        $this->_objects['_object'] = $object;
        $this->_objects['_has_many'] = false;

        parent::__construct($message, $values, $code, $previous);
    }

    /**
     * Adds a Validation object to this exception
     *
     *     // The following will add a validation object for a profile model
     *     // inside the exception for a user model.
     *     $e->add_object('profile', $validation);
     *     // The errors array will now look something like this
     *     // [
     *     //     'username' => 'This field is required'
     *     //     'profile' => [
     *     //         'first_name' => 'This field is required'
     *     //     ]
     *     // ];
     *
     * @param  string     $alias    The relationship alias from the model
     * @param  Validation $object   The Validation object to merge
     * @param  mixed      $has_many The array key to use if this exception can be merged multiple times
     * @return Kohana_ORM_Validation_Exception
     */
    public function add_object($alias, Validation $object, $has_many = false)
    {
        // We will need this when generating errors
        $this->_objects[$alias]['_has_many'] = $has_many !== false;

        if ($has_many === true) {
            // This is most likely a has_many relationship
            $this->_objects[$alias][]['_object'] = $object;
        } elseif ($has_many) {
            // This is most likely a has_many relationship
            $this->_objects[$alias][$has_many]['_object'] = $object;
        } else {
            $this->_objects[$alias]['_object'] = $object;
        }

        return $this;
    }

    /**
     * Merges an ORM_Validation_Exception object into the current exception
     * Useful when you want to combine errors into one array
     *
     * @param  ORM_Validation_Exception $object   The exception to merge
     * @param  mixed                    $has_many The array key to use if this exception can be merged multiple times
     * @return Kohana_ORM_Validation_Exception
     */
    public function merge(ORM_Validation_Exception $object, $has_many = false)
    {
        $alias = $object->alias();

        // We will need this when generating errors
        $this->_objects[$alias]['_has_many'] = $has_many !== false;

        if ($has_many === true) {
            // This is most likely a has_many relationship
            $this->_objects[$alias][] = $object->objects();
        } elseif ($has_many) {
            // This is most likely a has_many relationship
            $this->_objects[$alias][$has_many] = $object->objects();
        } else {
            $this->_objects[$alias] = $object->objects();
        }

        return $this;
    }

    /**
     * Returns a merged array of the errors from all the Validation objects in this exception
     *
     *     // Will load Model_User errors from messages/orm-validation/user.php
     *     $e->errors('orm-validation');
     *
     * @param   string  $directory Directory to load error messages from
     * @param   mixed   $translate Translate the message
     * @return  array
     * @see generate_errors()
     */
    public function errors($directory = null, $translate = true)
    {
        return $this->generate_errors($this->_alias, $this->_objects, $directory, $translate);
    }

    /**
     * Recursive method to fetch all the errors in this exception
     *
     * @param  string $alias     Alias to use for messages file
     * @param  array  $array     Array of Validation objects to get errors from
     * @param  string $directory Directory to load error messages from
     * @param  mixed  $translate Translate the message
     * @return array
     */
    protected function generate_errors($alias, array $array, $directory, $translate)
    {
        $errors = [];

        foreach ($array as $key => $object) {
            if (is_array($object)) {
                $errors[$key] = $key === '_external'
                    // Search for errors in $alias/_external.php
                    ? $this->generate_errors($alias . '/' . $key, $object, $directory, $translate)
                    // Regular models get their own file not nested within $alias
                    : $this->generate_errors($key, $object, $directory, $translate);
            } elseif ($object instanceof Validation) {
                if ($directory === null) {
                    // Return the raw errors
                    $file = null;
                } else {
                    $file = trim($directory . '/' . $alias, '/');
                }

                // Merge in this array of errors
                $errors += $object->errors($file, $translate);
            }
        }

        return $errors;
    }

    /**
     * Returns the protected _objects property from this exception
     *
     * @return array
     */
    public function objects()
    {
        return $this->_objects;
    }

    /**
     * Returns the protected _alias property from this exception
     *
     * @return string
     */
    public function alias()
    {
        return $this->_alias;
    }

}
