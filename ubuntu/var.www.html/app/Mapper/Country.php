<?php
namespace Htec\Mapper;

use Htec\Mapper;

final class Country extends Mapper
{
    protected string $table = 'countries';

    public function getColumnsDefinition()
    {
        return [
            ['column' => 'id', 'name' => 'id', 'type' => 'int'],
            ['column' => 'name', 'name' => 'name', 'type' => 'str', 'required' => true],
        ];
    }
}
