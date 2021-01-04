<?php
namespace Htec\Controller;

use Htec\Controller;
use Htec\Core\JsonResponse;
use Htec\Exception\InvalidParamsException;
use Htec\Service\User;
use Htec\Traits\Service\UserServiceTrait;

final class AuthController extends Controller
{
    use UserServiceTrait;

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
            $this->getUserService()->register($this->request->getParams());
            return $this->getSuccessResponse('Registration successful');
        } catch (InvalidParamsException $e) {
            return $this->getErrorResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->getErrorResponse('Registration failed');
        }
    }

    public function postIndexAction(): JsonResponse
    {
        try {
            $token = $this->getUserService()->authenticate($this->request->getParams());
            return $this->getSuccessResponse('Token generated', ['token' => $token]);
        } catch (InvalidParamsException $e) {
            return $this->getErrorResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->getErrorResponse("Something's wrong");
        }
    }
}
