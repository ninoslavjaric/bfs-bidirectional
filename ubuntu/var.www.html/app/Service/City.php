<?php
namespace Htec\Service;

use Htec\Service;
use Htec\Mapper\City as CityMapper;

/**
 * @property CityMapper $mapper
 */
final class City extends Service
{
    public function searchBy($searchTerm)
    {
        return $this->mapper->searchByName($searchTerm);
    }

    protected function getColumnDefinitionForKeyValueMap(): array
    {
        $definition = $this->mapper->getColumnsDefinition();

        $definition[] = [
            'column' => 'country_name',
            'name' => 'countryName',
        ];

        return $definition;
    }
}
