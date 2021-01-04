<?php
namespace Htec;

use Htec\Core\JsonResponse;
use Htec\Core\Logger;
use Htec\Core\Request;
use Htec\Service\User as UserService;

abstract class Controller
{
    protected const ACCESS_LEVEL_PUBLIC = 'public';
    protected const ACCESS_LEVEL_TOKEN = 'user';
    protected const ACCESS_LEVEL_ADMIN = 'admin';

    static protected array $endpointAccessScopeMap;
    static public string $errorMessage = 'Something went wrong';

    protected Request $request;

    abstract static public function getEndpointAccessScope(): array;

    final static public function getEndpointAccessScopeMap()
    {
        if (!isset(static::$endpointAccessScopeMap)) {
            static::$endpointAccessScopeMap = static::getEndpointAccessScope();
            static::$endpointAccessScopeMap['error'] = self::ACCESS_LEVEL_PUBLIC;
        }

        return static::$endpointAccessScopeMap;
    }

    final static public function checkPermission(string $action): string
    {
        $endpoints = static::getEndpointAccessScopeMap();

        if (!array_key_exists($action, $endpoints)) {
            return 'Controller ' . static::class . ' doesn\'t have endpoint ' . $action;
        }

        $validAccessLevels = [self::ACCESS_LEVEL_PUBLIC, self::ACCESS_LEVEL_TOKEN, self::ACCESS_LEVEL_ADMIN];

        if (!in_array($endpoints[$action], $validAccessLevels)) {
            return "Action {$action} doesn't have valid access level";
        }

        if ($endpoints[$action] == self::ACCESS_LEVEL_PUBLIC) {
            return '';
        }

        $accessToken = Request::getInstance()->getToken();

        if (empty($accessToken)) {
            return 'No access token provided';
        }

        $userService = UserService::getInstance();

        $userData = $userService->getByToken($accessToken);

        if (empty($userData)) {
            return "No user found with token {$accessToken}";
        }

        if ($userService->isTokenExpired($userData)) {
            return "User token is expired";
        }

        $requiredRole = $endpoints[$action];

        if ($requiredRole == self::ACCESS_LEVEL_ADMIN && $userData['role'] != self::ACCESS_LEVEL_ADMIN) {
            return 'User has no permission';
        }

        return '';
    }

    final public function __construct()
    {
        $this->request = Request::getInstance();
    }

    final public function errorAction()
    {
        return $this->getErrorResponse(self::$errorMessage);
    }

    private function getResponse(string $message, array $data, bool $success, int $statusCode = 200): JsonResponse
    {
        $responseArray = [
            'message' => $message,
            'success' => $success,
        ];

        if (!empty($data)) {
            $responseArray['data'] = $data;
        }

        return new JsonResponse($responseArray, $statusCode);
    }

    final protected function getSuccessResponse($message, $data = []): JsonResponse
    {
        return $this->getResponse($message, $data, true);
    }

    final protected function getErrorResponse($message): JsonResponse
    {
        return $this->getResponse($message, [], false, 404);
    }
}
