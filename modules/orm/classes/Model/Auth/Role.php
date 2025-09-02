<?php

/**
 * Default auth role
 *
 * @package    Kohana/Auth
 * @author     Kohana Team
 * @copyright  (c) 2007-2009 Kohana Team
 * @license    https://kohana.top/license.html
 */
class Model_Auth_Role extends ORM
{
    // Relationships
    protected $_has_many = [
        'users' => ['model' => 'User', 'through' => 'roles_users'],
    ];

    public function rules(): array
    {
        return [
            'name' => [
                ['not_empty'],
                ['min_length', [':value', 4]],
                ['max_length', [':value', 32]],
            ],
            'description' => [
                ['max_length', [':value', 255]],
            ]
        ];
    }

}
