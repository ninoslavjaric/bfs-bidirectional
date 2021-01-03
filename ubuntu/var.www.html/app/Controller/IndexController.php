<?php
namespace Htec\Controller;

use Htec\Controller;
use Htec\Core\JsonResponse;

final class IndexController extends Controller
{
    static public function getEndpointAccessScope(): array
    {
        return [
            'index' => self::ACCESS_LEVEL_PUBLIC,
        ];
    }

    public function indexAction(): JsonResponse
    {
        return $this->getSuccessResponse('Hello world!');
    }
}
