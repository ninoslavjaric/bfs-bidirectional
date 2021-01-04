<?php
namespace Htec\Traits\Service;

use Htec\Service\Airline;

trait AirlineServiceTrait
{
    private Airline $airlineService;

    /**
     * @return Airline
     */
    public function getAirlineService(): Airline
    {
        if (!isset($this->airlineService)) {
            $this->setAirlineService(new Airline());
        }

        return $this->airlineService;
    }

    /**
     * @param Airline $airlineService
     */
    public function setAirlineService(Airline $airlineService): void
    {
        $this->airlineService = $airlineService;
    }

}
