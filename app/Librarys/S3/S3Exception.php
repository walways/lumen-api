<?php
/**
 * 类说明
 * Created by PhpStorm.
 * User: qpf
 * Date: 2021/5/25
 * Time: 10:52 上午
 */

namespace App\Librarys\S3;


use Exception;

class S3Exception extends Exception {
    /**
     * Class constructor
     *
     * @param string $message Exception message
     * @param string $file File in which exception was created
     * @param string $line Line number on which exception was created
     * @param int $code Exception code
     */
    function __construct($message, $file, $line, $code = 0)
    {
        parent::__construct($message, $code);
        $this->file = $file;
        $this->line = $line;
    }
}