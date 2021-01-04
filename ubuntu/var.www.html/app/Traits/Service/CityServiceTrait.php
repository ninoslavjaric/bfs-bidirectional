<?php
namespace Htec\Traits\Service;

use Htec\Service\City;

trait CityServiceTrait
{
    private City $cityService;

    /**
     * @return City
     */
    public function getCityService(): City
    {
        if (!isset($this->cityService)) {
            $this->setCityService(new City());
        }

        return $this->cityService;
    }

    /**
     * @param City $cityService
     */
    public function setCityService(City $cityService): void
    {
        $this->cityService = $cityService;
    }
}
