<?php
namespace Htec\Controller;

use Htec\Controller;
use Htec\Core\JsonResponse;
use Htec\Exception\InvalidParamsException;
use Htec\Service\Route;
use Htec\Traits\Service\RouteServiceTrait;

final class RouteController extends Controller
{
    use RouteServiceTrait;

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
            $this->getRouteService()->importData($this->request->getParam('text'));
            return $this->getSuccessResponse('Import successful');
        }catch (InvalidParamsException $e) {
            return $this->getErrorResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->getErrorResponse("Import not successful");
        }
    }

    public function travelAction($cityOrigin, $cityDestination): JsonResponse
    {
        try {
            $data = $this->getRouteService()->getRoutes($cityOrigin, $cityDestination);
            return $this->getSuccessResponse('Optimal flight found', $data);
        } catch (\Exception $e) {
            return $this->getErrorResponse("Optimal flight not found");
        }
    }
}
