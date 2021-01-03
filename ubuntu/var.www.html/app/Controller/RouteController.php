<?php
namespace Htec\Controller;

use Htec\Controller;
use Htec\Core\JsonResponse;
use Htec\Service\Route;

final class RouteController extends Controller
{
    static public function getEndpointAccessScope(): array
    {
        return [
            'postImport' => self::ACCESS_LEVEL_ADMIN,
            'travel' => self::ACCESS_LEVEL_TOKEN,
        ];
    }

    public function postImportAction(): JsonResponse
    {
        try {
            Route::getInstance()->importData($this->request->getParam('text'));
            return $this->getSuccessResponse('Import successful');
        } catch (\Exception $e) {
            return $this->getErrorResponse("Import not successful");
        }
    }

    public function travelAction($cityOrigin, $cityDestination): JsonResponse
    {
        try {
            $data = Route::getInstance()->getRoutes($cityOrigin, $cityDestination);
            return $this->getSuccessResponse('Optimal flight found', $data);
        } catch (\Exception $e) {
            return $this->getErrorResponse("Optimal flight not found");
        }
    }
}
