<?php

/**
 * Default auth user
 *
 * @package    Kohana/Auth
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Model_Auth_User extends ORM
{
    /**
     * A user has many tokens and roles
     *
     * @var array Relationships
     */
    protected $_has_many = [
        'user_tokens' => ['model' => 'User_Token'],
        'roles' => ['model' => 'Role', 'through' => 'roles_users'],
    ];

    /**
     * Rules for the user model. Because the password is _always_ a hash
     * when it's set,you need to run an additional not_empty rule in your controller
     * to make sure you didn't hash an empty string. The password rules
     * should be enforced outside the model or with a model helper method.
     *
     * @return array Rules
     */
    public function rules()
    {
        return [
            'username' => [
                ['not_empty'],
                ['max_length', [':value', 32]],
                [[$this, 'unique'], ['username', ':value']],
            ],
            'password' => [
                ['not_empty'],
            ],
            'email' => [
                ['not_empty'],
                ['email'],
                [[$this, 'unique'], ['email', ':value']],
            ],
        ];
    }

    /**
     * Filters to run when data is set in this model. The password filter
     * automatically hashes the password when it's set in the model.
     *
     * @return array Filters
     * @throws Kohana_Exception
     */
    public function filters()
    {
        return [
            'password' => [
                [[Auth::instance(), 'hash']]
            ]
        ];
    }

    /**
     * Labels for fields in this model
     *
     * @return array Labels
     */
    public function labels()
    {
        return [
            'username' => 'username',
            'email' => 'email address',
            'password' => 'password',
        ];
    }

    /**
     * Complete the login for a user by incrementing the logins and saving login timestamp
     *
     * @return void
     * @throws Kohana_Exception
     * @throws ORM_Validation_Exception
     * @throws ReflectionException
     */
    public function complete_login()
    {
        if ($this->_loaded) {
            // Update the number of logins
            $this->logins = new Database_Expression('logins + 1');

            // Set the last login date
            $this->last_login = time();

            // Save the user
            $this->update();
        }
    }

    /**
     * Tests if a unique key value exists in the database.
     *
     * @param mixed    the value to test
     * @param string   field name
     * @return  boolean
     * @throws Kohana_Exception
     */
    public function unique_key_exists($value, $field = NULL)
    {
        if ($field === NULL) {
            // Automatically determine field by looking at the value
            $field = $this->unique_key($value);
        }

        return (bool) DB::select([DB::expr('COUNT(*)'), 'total_count'])
                ->from($this->_table_name)
                ->where($field, '=', $value)
                ->where($this->_primary_key, '!=', $this->pk())
                ->execute($this->_db)
                ->get('total_count');
    }

    /**
     * Allows a model use both email and username as unique identifiers for login
     *
     * @param   string  unique value
     * @return  string  field name
     */
    public function unique_key($value)
    {
        return Valid::email($value) ? 'email' : 'username';
    }

    /**
     * Password validation for plain passwords.
     *
     * @param array $values
     * @return Validation
     */
    public static function get_password_validation($values)
    {
        return Validation::factory($values)
                ->rule('password', 'min_length', [':value', 8])
                ->rule('password_confirm', 'matches', [
                    ':validation',
                    ':field', 'password'
        ]);
    }

    /**
     * Create a new user
     *
     * Example usage:
     * ~~~
     * $user = ORM::factory('User')->create_user($_POST, [
     *     'username',
     *     'password',
     *     'email',
     * ]);
     * ~~~
     *
     * @param array $values
     * @param array $expected
     * @return ORM
     * @throws Kohana_Exception
     * @throws ORM_Validation_Exception
     * @throws ReflectionException
     */
    public function create_user($values, $expected)
    {
        // Validation for passwords
        $extra_validation = Model_User::get_password_validation($values)
            ->rule('password', 'not_empty');

        return $this->values($values, $expected)->create($extra_validation);
    }

    /**
     * Update an existing user
     *
     * [!!] We make the assumption that if a user does not supply a password, that they do not wish to update their password.
     *
     * Example usage:
     * ~~~
     * $user = ORM::factory('User')
     *     ->where('username', '=', 'kiall')
     *     ->find()
     *     ->update_user($_POST, [
     *         'username',
     *         'password',
     *         'email',
     * ]);
     * ~~~
     *
     * @param array $values
     * @param array $expected
     * @return ORM
     * @throws Kohana_Exception
     * @throws ORM_Validation_Exception
     * @throws ReflectionException
     */
    public function update_user($values, $expected = NULL)
    {
        if (empty($values['password'])) {
            unset($values['password'], $values['password_confirm']);
        }

        // Validation for passwords
        $extra_validation = Model_User::get_password_validation($values);

        return $this->values($values, $expected)->update($extra_validation);
    }

}
