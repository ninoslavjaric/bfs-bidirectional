<?php
namespace Htec\Controller;

use Htec\Controller;
use Htec\Core\JsonResponse;
use Htec\Service\City;

final class CityController extends Controller
{
    static public function getEndpointAccessScope(): array
    {
        return [
            'index' => self::ACCESS_LEVEL_TOKEN,
            'search' => self::ACCESS_LEVEL_TOKEN,
            'create' => self::ACCESS_LEVEL_ADMIN,
        ];
    }

    public function indexAction(): JsonResponse
    {
        try {
            $cities = City::getInstance()->getAll();
            return $this->getSuccessResponse('Cities found', $cities);
        } catch (\Exception $e) {
            return $this->getErrorResponse("Cities not found");
        }
    }

    public function searchAction($searchTerm): JsonResponse
    {
        try {
            $cities = City::getInstance()->searchBy($searchTerm);
            return $this->getSuccessResponse('Cities found', $cities);
        } catch (\Exception $e) {
            return $this->getErrorResponse("Cities not found");
        }
    }

    public function postCreateAction(): JsonResponse
    {
        return $this->getSuccessResponse('Hello world!');
    }
}
