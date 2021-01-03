<?php
namespace Htec\Controller;

use Htec\Controller;
use Htec\Core\JsonResponse;
use Htec\Service\User;

final class AuthController extends Controller
{
    static public function getEndpointAccessScope(): array
    {
        return [
            'postRegister' => self::ACCESS_LEVEL_PUBLIC,
            'postIndex' => self::ACCESS_LEVEL_PUBLIC,
        ];
    }

    public function postRegisterAction(): JsonResponse
    {
        try {
            User::getInstance()->register($this->request->getParams());
            return $this->getSuccessResponse('Registration successful');
        } catch (\Exception $e) {
            return $this->getErrorResponse('Registration failed');
        }
    }

    public function postIndexAction(): JsonResponse
    {
        try {
            $token = User::getInstance()->authenticate($this->request->getParams());
            return $this->getSuccessResponse('Token generated', ['token' => $token]);
        } catch (\Exception $e) {
            return $this->getErrorResponse("Something's wrong");
        }
    }
}
