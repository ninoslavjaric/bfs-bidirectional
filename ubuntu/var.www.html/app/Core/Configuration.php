<?php
namespace Htec\Core;

use Htec\Traits\SingletonTrait;

final class Configuration
{
    use SingletonTrait;

    public function getConfig($name)
    {
        $filePath = BASE_DIR . "/config/{$name}.local.php";

        if (!file_exists($filePath)) {
            throw new \Exception("Config file {$filePath} dowsn't exist.");
        }

        return include $filePath;
    }
}
