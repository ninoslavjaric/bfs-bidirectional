<?php
namespace Htec\Traits\Service;

use Htec\Service\Route;

trait RouteServiceTrait
{
    private Route $routeService;

    /**
     * @return Route
     */
    public function getRouteService(): Route
    {
        if (!isset($this->routeService)) {
            $this->setRouteService(new Route());
        }

        return $this->routeService;
    }

    /**
     * @param Route $routeService
     */
    public function setRouteService(Route $routeService): void
    {
        $this->routeService = $routeService;
    }


}
