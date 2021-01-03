<?php
namespace Htec\Service;

use Htec\Contract\Importable;
use Htec\Exception;
use Htec\Exception\InvalidParamsException;
use Htec\Service;
use Htec\Mapper;
use Htec\Mapper\Route as RouteMapper;

/**
 * @property RouteMapper $mapper
 */
final class Route extends Service implements Importable
{
    private array $routesData = [];

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
        foreach ($data as &$rowa    ) {
            $row = str_getcsv($rowa);
            $row = array_combine($this->getImportItemStructure(), $row);

            $airportService = Airport::getInstance();
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
        ) {
            throw new InvalidParamsException('City params must be integer');
        }
    }

    public function getRoutes($cityOriginId, $cityDestinationId)
    {
        $this->validateCityIds($cityOriginId, $cityDestinationId);

        $airportService = Airport::getInstance();
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
        $cityService = City::getInstance();
        $airportService = Airport::getInstance();

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
        $airlineService = Airline::getInstance();

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
