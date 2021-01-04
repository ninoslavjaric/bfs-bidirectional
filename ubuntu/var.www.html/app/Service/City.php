<?php
namespace Htec\Service;

use Htec\Exception\NotFoundException;
use Htec\Service;
use Htec\Mapper\City as CityMapper;
use Htec\Traits\Service\CountryServiceTrait;

/**
 * @property CityMapper $mapper
 */
class City extends Service
{
    use CountryServiceTrait;

    public function searchBy($searchTerm)
    {
        $cityRecords = $this->mapper->searchByName($searchTerm);

        if (empty($cityRecords)) {
            throw new NotFoundException('No city found');
        }

        foreach ($cityRecords as &$city) {
            $city = $this->generateDataKeyValueMap($city);
            $this->parseJson($city['comments']);
        }

        return $cityRecords;
    }

    public function getAll(array $where = []): array
    {
        $cityRecords = parent::getAll($where);

        foreach ($cityRecords as &$city) {
            $this->parseJson($city['comments']);
        }

        return $cityRecords;
    }

    public function get(array $where): array
    {
        $city = parent::get($where);

        if (!empty($city)) {
            $this->parseJson($city['comments']);
        }

        return $city;
    }

    private function parseJson(&$string): void
    {
        $string = empty($string) ? [] : json_decode($string, true);
    }

    protected function getColumnDefinitionForKeyValueMap(): array
    {
        $definition = $this->mapper->getColumnsDefinition();

        $extraDefinition = [
            ['column' => 'country_name', 'name' => 'countryName'],
            ['column' => 'comments', 'name' => 'comments'],
        ];

        $definition = array_merge($definition, $extraDefinition);

        return $definition;
    }

    protected function beforeCreate(array &$data): void
    {
        $countryService = $this->getCountryService();

        if (array_key_exists('country', $data)) {
            $country = $countryService->getBy('name', $data['country']);
            if (empty($country)) {
                $country = $countryService->create([
                    'name' => $data['country']
                ]);
            }

            $data['countryId'] = $country['id'];
            unset($data['country']);
            unset($data['comment']);
        }

        parent::beforeCreate($data);
    }
}
