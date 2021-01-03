<?php
namespace Htec;

use Htec\Core\Logger;
use Throwable;

class Exception extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        Logger::logWarning($message);
    }
}
