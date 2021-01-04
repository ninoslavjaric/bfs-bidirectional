<?php

namespace TestHtec\Service;

use Htec\Exception\NotFoundException;
use Htec\Service\Airport;
use Htec\Service\Route;
use Htec\Mapper\Route as RouteMapper;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    private Route $routeService;
    private RouteMapper $routeMapper;
    private Airport $airportStub;

    protected function setUp(): void
    {
        parent::setUp();
        $this->routeService = Route::getInstance();

        $this->routeMapper = $this->createMock(RouteMapper::class);
        $this->airportStub = $this->createMock(Airport::class);

        $this->routeService->setMapper($this->routeMapper);
        $this->routeService->setAirportService($this->airportStub);
    }

    public function getRoutesBadValues(): array
    {
        return [
            [null, 2],
            [1, null],
            [null, null],
            [-1, 0],
            [null, 0],
        ];
    }

    /**
     * @dataProvider getRoutesBadValues
     */
    public function testGetRoutesWithInvalidParametersExpectingExceptionThrown($cityOriginId, $cityDestinationId): void
    {
        $this->expectException(\Htec\Exception\InvalidParamsException::class);
        $this->routeService->getRoutes($cityOriginId, $cityDestinationId);
    }

    public function testGetRoutesWithValidParamsWhenAirportsHaveNoRoutesEndingWithNoOptimalRoute(): void
    {
        $airportsOrigin = [
            ['id' => 1],
            ['id' => 2],
            ['id' => 3],
        ];
        $airportsDestination = [
            ['id' => 4],
            ['id' => 5],
            ['id' => 6],
            ['id' => 7],
        ];

        $this->airportStub->expects($this->exactly(2))->method('getAll')->will($this->onConsecutiveCalls($airportsOrigin, $airportsDestination));
        $this->routeMapper->expects($this->once())->method('resetCalculationTable');
        $this->routeMapper->expects($this->exactly(count($airportsOrigin) * count($airportsDestination)))->method('initialCalculation');

        $this->routeMapper->expects($this->exactly(count($airportsOrigin) * count($airportsDestination)))->method('getBFSTableCount')->willReturn(0);
        $this->routeMapper->expects($this->exactly(count($airportsOrigin) * count($airportsDestination)))->method('getBFSInvertedTableCount')->willReturn(0);

        $this->routeMapper->expects($this->never())->method('iterateBFSTable');
        $this->routeMapper->expects($this->never())->method('iterateBFSInvertedTable');
        $this->routeMapper->expects($this->never())->method('resolveBidirectionalBFSEncounter');

        $this->routeMapper->expects($this->once())->method('fetchCalculatedRoute')->willReturn([]);

        $this->expectException(NotFoundException::class);
        $path = $this->routeService->getRoutes(1, 2);
    }
}
