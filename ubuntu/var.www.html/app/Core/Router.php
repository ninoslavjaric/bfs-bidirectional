<?php
namespace Htec\Core;

use Htec\Controller;
use Htec\Traits\SingletonTrait;

final class Router
{
    use SingletonTrait;

    private Controller $controller;
    private string $controllerMethod;
    private array $controllerMethodParams;

    private function __construct()
    {
        $this->setControllerData();
    }

    public function getController(): Controller
    {
        return $this->controller;
    }

    public function getControllerMethod(): string
    {
        return $this->controllerMethod;
    }

    public function getControllerMethodParams(): array
    {
        return $this->controllerMethodParams;
    }

    private function setControllerData(): void
    {
        $request = Request::getInstance();
        $pathParams = $request->getPathParams();
        $controller = array_shift($pathParams);
        $endpoint = array_shift($pathParams);

        if (empty($controller)) {
            $controller = 'index';
            $endpoint = 'index';
        }

        if (empty($endpoint)) {
            $endpoint = 'index';
        }

        $action = $endpoint;

        if (($httpMethod = $request->getMethod()) != 'get') {
            $action = $httpMethod . ucfirst($endpoint);
        }

        list($this->controller, $this->controllerMethod, $this->controllerMethodParams) = $this->checkControllerData(
            $controller, $action, $pathParams
        );
    }

    private function checkControllerData($controller, $action, $pathParams): array
    {
        /** @var Controller $controller */
        $controller = Controller::class . '\\' . ucfirst($controller) . 'Controller';

        if (!class_exists($controller)) {
            $this->handleRoutingErrors("Controller {$controller} doesn't exist", $action);
            $controller = Controller\IndexController::class;
        }

        $method = $action . 'Action';

        if (!method_exists($controller, $method)) {
            $this->handleRoutingErrors("Controller {$controller} doesn't have method {$method}", $action);
        }

        if ($message = $controller::checkPermission($action)) {
            Controller::$errorMessage = $message;
            $this->handleRoutingErrors($message, $action);
        }

        $method = $action . 'Action';

        if ($action != 'error' && !$this->validatePathParams($pathParams, $controller, $method)) {
            $httpPath = Request::getInstance()->getPath();
            Controller::$errorMessage = "Path {$httpPath} not valid.";
            $this->handleRoutingErrors(Controller::$errorMessage, $action);
            $method = $action . 'Action';
        }

        return [new $controller(), $method, $pathParams];
    }

    private function handleRoutingErrors(string $message, &$action): void
    {
        Logger::logError($message);
        $action = 'error';
    }

    private function validatePathParams(array $pathParams, string $controller, string $method): bool
    {
        try {
            $reflectionMethod = new \ReflectionMethod($controller, $method);
            $paramsCounter = count($pathParams);
            $status = $reflectionMethod->getNumberOfRequiredParameters() <= $paramsCounter
                && $reflectionMethod->getNumberOfParameters() >= $paramsCounter;

        } catch (\ReflectionException $e) {
            Logger::logError($e->getMessage());
            $status = false;
        }

        return $status;
    }
}
