<?php

require_once __DIR__ . '/../vendor/autoload.php';

define('BASE_DIR', realpath(__DIR__ . '/../'));
ini_set('display_errors', 1);

try {
    // mysqldump -psecret htec --no-data -R | sed 's/ DEFINER=`htec`@`%`//g'
    \Htec\App::init();
} catch (\Exception $ex) {
    \Htec\Core\Logger::logError($ex->getMessage());
    die($ex->getMessage());
}

