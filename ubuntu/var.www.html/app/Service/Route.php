<?php
namespace Htec\Service;

use Htec\Contract\Importable;
use Htec\Exception;
use Htec\Exception\InvalidParamsException;
use Htec\Service;
use Htec\Mapper;
use Htec\Mapper\Route as RouteMapper;
use Htec\Traits\Service\AirlineServiceTrait;
use Htec\Traits\Service\AirportServiceTrait;
use Htec\Traits\Service\CityServiceTrait;

/**
 * @property RouteMapper $mapper
 */
class Route extends Service implements Importable
{
    use AirportServiceTrait;
    use CityServiceTrait;
    use AirlineServiceTrait;

    /**
     * @param Airport $airportService
     */
    public function setAirportService(Airport $airportService): void
    {
        $this->airportService = $airportService;
    }

    /**
     * @param City $cityService
     */
    public function setCityService(City $cityService): void
    {
        $this->cityService = $cityService;
    }


    public function getImportItemStructure(): array
    {
        return [
            'airline',
            'airlineId',
            'sourceAirport',
            'sourceAirportId',
            'destinationAirport',
            'destinationAirportId',
            'codeshare',
            'stops',
            'equipment',
            'price',
        ];
    }

    public function importData(string $data): void
    {
        $data = explode("\n", trim($data));
        foreach ($data as &$row) {
            $row = str_getcsv($row);
            $row = array_combine($this->getImportItemStructure(), $row);

            $airportService = $this->getAirportService();
            $sourceAirportData = $airportService->get(['id' => $row['sourceAirportId']]);
            $destinationAirportData = $airportService->get(['id' => $row['destinationAirportId']]);

            if (empty($sourceAirportData) || empty($destinationAirportData)) {
                continue;
            }

            $airportData = $this->get([
                'airlineId' => $row['airlineId'],
                'sourceAirportId' => $row['sourceAirportId'],
                'destinationAirportId' => $row['destinationAirportId'],
            ]);

            if (!empty($airportData)) {
                continue;
            }

            try {
                $this->create($row);
            } catch (InvalidParamsException $ex) {
                continue;
            }
        }
    }

    private function validateCityIds($cityOriginId, $cityDestinationId): void
    {
        if (
            filter_var($cityOriginId, FILTER_VALIDATE_INT) === false
            || filter_var($cityDestinationId, FILTER_VALIDATE_INT) === false
            || $cityOriginId < 1 || $cityDestinationId < 1
        ) {
            throw new InvalidParamsException('City params must be integer and positive');
        }
    }

    public function getRoutes($cityOriginId, $cityDestinationId)
    {
        $this->validateCityIds($cityOriginId, $cityDestinationId);

        $airportService = $this->getAirportService();
        $originAirports = $airportService->getAll(['cityId' => $cityOriginId]);
        $destinationAirports = $airportService->getAll(['cityId' => $cityDestinationId]);

        $travelCombinations = $this->getTravelCombinations($originAirports, $destinationAirports);

        $this->mapper->resetCalculationTable();

        foreach ($travelCombinations as $key => $travelCombination) {
            $calculationParams = $travelCombination;
            $this->calculateCheapestFlight(...$calculationParams);
        }

        $calculatedRoute = $this->mapper->fetchCalculatedRoute();

        if (empty($calculatedRoute)) {
            throw new Exception\NotFoundException('No optimal route found');
        }

        $calculatedRoute['path'] = explode(',', $calculatedRoute['path']);

        $stepsStack = [];

        for ($key = 0; $key < count($calculatedRoute['path']) - 1; $key++) {
            $originId = $calculatedRoute['path'][$key];
            $destinationId = $calculatedRoute['path'][$key+1];

            $stepsStack[] = $this->getRouteStep($originId, $destinationId);
        }

        $calculatedRoute['path'] = $stepsStack;

        return $calculatedRoute;
    }

    private function getTravelCombinations($originAirports, $destinationAirports)
    {
        $travelCombinations = [];
        foreach ($originAirports as $originAirport) {
            foreach ($destinationAirports as $destinationAirport) {
                $travelCombinations[] = [$originAirport['id'], $destinationAirport['id']];
            }
        }

        return $travelCombinations;
    }
    
    private function getCheapest(array $where)
    {
        $result = $this->mapper->findRowByWhere($where, [], ['price' => Mapper::SORT_ASC]) ?: [];

        return $this->generateDataKeyValueMap($result);
    }

    private function getRouteStep($originId, $destinationId)
    {
        $cityService = $this->getCityService();
        $airportService = $this->getAirportService();

        $step = $this->getCheapest([
            'sourceAirportId' => $originId,
            'destinationAirportId' => $destinationId,
        ]);

        $step['sourceAirport'] = $airportService->getBy('id', $step['sourceAirportId']);
        $step['destinationAirport'] = $airportService->getBy('id', $step['destinationAirportId']);

        $step['sourceCity'] = $cityService->getBy('id', $step['sourceAirport']['cityId']);
        $step['destinationCity'] = $cityService->getBy('id', $step['destinationAirport']['cityId']);

        $step['distance'] = $this->calculateDistance($step['sourceAirport'], $step['destinationAirport']);

        return $step;
    }

    private function calculateDistance($sourceAirport, $destinationAirport)
    {
        $lat1 = $sourceAirport['latitude'];
        $lng1 = $sourceAirport['longitude'];

        $lat2 = $destinationAirport['latitude'];
        $lng2 = $destinationAirport['longitude'];

        return 6371 * 2 * asin(SQRT(
            pow(
                    sin(($lat1 - abs($lat2)) * pi()/180 / 2), 2
                ) + cos($lat1 * pi()/180 ) * cos(abs($lat2) *
                    pi()/180) * pow(sin(($lng1 - $lng2) * pi()/180 / 2), 2)
            ));
    }

    protected function beforeCreate(array &$data): void
    {
        $airlineService = $this->getAirlineService();

        $airlineData = $airlineService->get(['id' => $data['airlineId']]);
        if (empty($airlineData)) {
            $airlineService->create([
                'id' => $data['airlineId'],
                'name' => $data['airline'],
            ]);
        }

        $attributes = array_column($this->mapper->getColumnsDefinition(), 'name');

        $data = array_filter($data, function($key) use ($attributes) {
            return in_array($key, $attributes);
        }, ARRAY_FILTER_USE_KEY);

        parent::beforeCreate($data);
    }

    private function calculateCheapestFlight($originAirportId, $destinationAirportId)
    {
        $this->mapper->initialCalculation($originAirportId, $destinationAirportId);

        list($routesC, $iRoutesC) = $this->getBfsRoutesCounter();

        while ($routesC > 0 || $iRoutesC > 0) {
            $this->mapper->iterateBFSTable($originAirportId, $destinationAirportId);
            $this->mapper->iterateBFSInvertedTable($originAirportId, $destinationAirportId);
            $this->mapper->resolveBidirectionalBFSEncounter();

            list($routesC, $iRoutesC) = $this->getBfsRoutesCounter();
        }
    }

    private function getBfsRoutesCounter()
    {
        return [
            $this->mapper->getBFSTableCount(),
            $this->mapper->getBFSInvertedTableCount(),
        ];
    }
}
