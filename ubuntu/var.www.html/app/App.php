<?php
namespace Htec;

use Htec\Core\Logger;
use Htec\Core\Request;
use Htec\Core\Router;
use Htec\Traits\SingletonTrait;

final class App
{
    use SingletonTrait;

    public static function init()
    {
        $router = Router::getInstance();

        echo call_user_func_array(
            [$router->getController(), $router->getControllerMethod()], $router->getControllerMethodParams()
        );
    }
}
