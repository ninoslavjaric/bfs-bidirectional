<?php
namespace Htec\Controller;

use Htec\Controller;
use Htec\Core\JsonResponse;
use Htec\Service\Airport;
use Htec\Traits\Service\AirportServiceTrait;

final class AirportController extends Controller
{
    use AirportServiceTrait;

    static public function getEndpointAccessScope(): array
    {
        return [
            'postImport' => self::ACCESS_LEVEL_ADMIN,
        ];
    }

    public function postImportAction(): JsonResponse
    {
        try {
            $this->getAirportService()->importData($this->request->getParam('text'));
            return $this->getSuccessResponse('Import successful');
        } catch (\Exception $e) {
            return $this->getErrorResponse("Import not successful");
        }
    }
}
