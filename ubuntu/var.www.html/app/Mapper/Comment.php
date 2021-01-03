<?php
namespace Htec\Mapper;

use Htec\Mapper;

final class Comment extends Mapper
{
    protected string $table = 'comments';

    public function getColumnsDefinition()
    {
        return [
            ['column' => 'id', 'name' => 'id', 'type' => 'int'],
            ['column' => 'text', 'name' => 'text', 'type' => 'str', 'required' => true],
            ['column' => 'city_id', 'name' => 'cityId', 'type' => 'int', 'required' => true],
            ['column' => 'user_id', 'name' => 'userId', 'type' => 'int', 'required' => true],
        ];
    }
}
