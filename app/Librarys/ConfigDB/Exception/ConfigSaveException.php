<?php
/**
 * Created by PhpStorm.
 * User: tan wei
 * Date: 2018/7/24
 * Time: 19:30
 */

namespace App\Librarys\ConfigDB\Exception;

use Throwable;

class ConfigSaveException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        // todo exchange code to messages
        parent::__construct($message, $code, $previous);
    }
}