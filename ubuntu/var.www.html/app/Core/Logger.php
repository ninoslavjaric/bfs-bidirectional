<?php
namespace Htec\Core;

final class Logger
{
    const INFO = 'INFO';
    const ERROR = 'ERROR';
    const WARNING = 'WARNING';
    const DEBUG = 'DEBUG';

    public static function logError($message)
    {
        return self::log($message, self::ERROR);
    }

    public static function logWarning($message)
    {
        return self::log($message, self::WARNING);
    }

    public static function logInfo($message)
    {
        return self::log($message, self::INFO);
    }

    private static function getIdentifier()
    {
        $identifier = 'ANONYMOUS';

        if (!empty($_COOKIE['PAMSESSID'])) {
            $identifier = $_COOKIE['PAMSESSID'];
        }

        return $identifier;
    }

    private static function log($message, $type)
    {
        return error_log(sprintf('%s: [%s] - session: %s', $type, $message, self::getIdentifier()));
    }

}
