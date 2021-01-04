<?php
namespace Htec\Traits\Service;

use Htec\Service\Airport;

trait AirportServiceTrait
{
    private Airport $airportService;

    /**
     * @return Airport
     */
    public function getAirportService(): Airport
    {
        if (!isset($this->airportService)) {
            $this->setAirportService(new Airport());
        }

        return $this->airportService;
    }

    /**
     * @param Airport $airportService
     */
    public function setAirportService(Airport $airportService): void
    {
        $this->airportService = $airportService;
    }

}
