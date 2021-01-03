<?php
namespace Htec\Mapper;

use Htec\Core\Validator;
use Htec\Mapper;

final class Airline extends Mapper
{
    protected string $table = 'airlines';

    public function getColumnsDefinition()
    {
        return [
            ['column' => 'id', 'name' => 'id', 'type' => Validator::TYPE_INT],
            ['column' => 'name', 'name' => 'name', 'type' => Validator::TYPE_STR, 'required' => true],
        ];
    }
}
