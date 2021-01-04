<?php
namespace Htec\Traits\Service;

use Htec\Service\Country;

trait CountryServiceTrait
{
    private Country $countryService;

    /**
     * @return Country
     */
    public function getCountryService(): Country
    {
        if (!isset($this->countryService)) {
            $this->setCountryService(new Country());
        }

        return $this->countryService;
    }

    /**
     * @param Country $countryService
     */
    public function setCountryService(Country $countryService): void
    {
        $this->countryService = $countryService;
    }


}
