<?php

/**
 * Default auth user toke
 *
 * @package    Kohana/Auth
 * @author     Kohana Team
 * @copyright  (c) 2007-2012 Kohana Team
 * @license    https://kohana.top/license
 */
class Model_Auth_User_Token extends ORM
{
    // Relationships
    protected $_belongs_to = [
        'user' => ['model' => 'User'],
    ];
    protected $_created_column = [
        'column' => 'created',
        'format' => true,
    ];

    /**
     * Handles garbage collection and deleting of expired objects.
     *
     * @return  void
     * @throws Kohana_Exception
     */
    public function __construct($id = null)
    {
        parent::__construct($id);

        if (mt_rand(1, 100) === 1) {
            // Do garbage collection
            $this->delete_expired();
        }

        if ($this->expires < time() && $this->_loaded) {
            // This object has expired
            $this->delete();
        }
    }

    /**
     * Deletes all expired tokens.
     *
     * @return  ORM
     * @throws Kohana_Exception
     */
    public function delete_expired()
    {
        // Delete all expired tokens
        DB::delete($this->_table_name)
            ->where('expires', '<', time())
            ->execute($this->_db);

        return $this;
    }

    public function create(Validation $validation = null)
    {
        $this->token = $this->create_token();

        return parent::create($validation);
    }

    protected function create_token()
    {
        do {
            $token = sha1(uniqid(Text::random('alnum', 32), true));
        } while (ORM::factory('User_Token', ['token' => $token])->loaded());

        return $token;
    }

}
