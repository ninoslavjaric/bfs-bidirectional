<?php
namespace Htec\Service;

use Htec\Contract\Importable;
use Htec\Service;
use Htec\Traits\Service\CityServiceTrait;
use Htec\Traits\Service\CountryServiceTrait;

class Airport extends Service implements Importable
{
    use CountryServiceTrait;
    use CityServiceTrait;

    public function getImportItemStructure(): array
    {
        return [
            'id',
            'name',
            'city',
            'country',
            'iata',
            'icao',
            'latitude',
            'longitude',
            'altitude',
            'timezone',
            'dst',
            'dbTimezone',
            'type',
            'source',
        ];
    }

    public function importData(string $data): void
    {
        $data = explode("\n", trim($data));
        foreach ($data as &$row) {
            $row = str_getcsv($row);
            $row = array_combine($this->getImportItemStructure(), $row);

            if (empty($row['city'])) {
                continue;
            }

            $airportData = $this->get(['id' => $row['id']]);
            if (empty($airportData)) {
                $this->create($row);
            }
        }
    }

    protected function beforeCreate(array &$data): void
    {
        $countryService = $this->getCountryService();
        $cityService = $this->getCityService();

        $countryRecords = [
            'name' => $data['country']
        ];
        $countryData = $countryService->get($countryRecords);
        if (empty($countryData)) {
            $countryData = $countryService->create($countryRecords);
        }

        $cityRecords = [
            'countryId' => $countryData['id'],
            'name' => $data['city'],
        ];
        $cityData = $cityService->get($cityRecords);
        if (empty($cityData)) {
            $cityData = $cityService->create($cityRecords);
        }

        unset($data['city'], $data['country']);
        $data['cityId'] = $cityData['id'];

        parent::beforeCreate($data);
    }


}
