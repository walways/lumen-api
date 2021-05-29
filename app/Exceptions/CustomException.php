<?php
namespace App\Exceptions;

use Exception;
use Throwable;

class CustomException extends Exception
{

    public function __construct($message = "", $code = -1, Throwable $previous = null)
    {
        // todo exchange code to messages
        parent::__construct($message, $code, $previous);
    }
}
