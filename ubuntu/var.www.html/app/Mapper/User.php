<?php
namespace Htec\Mapper;

use Htec\Mapper;

final class User extends Mapper
{
    protected string $table = 'users';

    public function getColumnsDefinition()
    {
        return [
            ['column' => 'id', 'name' => 'id', 'type' => 'int'],
            ['column' => 'first_name', 'name' => 'firstName', 'type' => 'str', 'required' => true],
            ['column' => 'last_name', 'name' => 'lastName', 'type' => 'str', 'required' => true],
            ['column' => 'username', 'name' => 'username', 'type' => 'str', 'unique' => true],
            ['column' => 'password', 'name' => 'password', 'type' => 'str', 'required' => true],
            ['column' => 'salt', 'name' => 'salt', 'type' => 'str'],
            ['column' => 'role', 'name' => 'role', 'type' => 'enum', 'values' => ['user', 'admin']],
            ['column' => 'token', 'name' => 'token', 'type' => 'str'],
            ['column' => 'token_expires', 'name' => 'tokenExpires', 'type' => 'datetime'],
        ];
    }
}
