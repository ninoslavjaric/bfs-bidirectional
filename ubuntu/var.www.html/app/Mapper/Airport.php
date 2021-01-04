<?php
namespace Htec\Mapper;

use Htec\Mapper;

class Airport extends Mapper
{
    protected string $table = 'airports';

    public function getColumnsDefinition()
    {
        return [
            ['column' => 'id', 'name' => 'id', 'type' => 'int'],
            ['column' => 'name', 'name' => 'name', 'type' => 'str'],
            ['column' => 'city_id', 'name' => 'cityId', 'type' => 'str'],
            ['column' => 'iata', 'name' => 'iata', 'type' => 'str'],
            ['column' => 'icao', 'name' => 'icao', 'type' => 'str'],
            ['column' => 'latitude', 'name' => 'latitude', 'type' => 'float'],
            ['column' => 'longitude', 'name' => 'longitude', 'type' => 'float'],
            ['column' => 'altitude', 'name' => 'altitude', 'type' => 'int'],
            ['column' => 'timezone', 'name' => 'timezone', 'type' => 'str'],
            ['column' => 'dst', 'name' => 'dst', 'type' => 'str'],
            ['column' => 'db_timezone', 'name' => 'dbTimezone', 'type' => 'str'],
            ['column' => 'type', 'name' => 'type', 'type' => 'str'],
            ['column' => 'source', 'name' => 'source', 'type' => 'str'],
        ];
    }
}
