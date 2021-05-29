<?php


namespace App\Logging\Processor;

use Monolog\Processor\ProcessorInterface;
use Monolog\ResettableInterface;
use Illuminate\Support\Arr;

/**
 * Adds a unique identifier into records
 *
 * @author Simon Mönch <sm@webfactory.de>
 */
class UidProcessor implements ProcessorInterface, ResettableInterface
{
    private $uid;

    public function __construct($length = 7)
    {
        if (!is_int($length) || $length > 32 || $length < 1) {
            throw new \InvalidArgumentException('The uid length must be an integer between 1 and 32');
        }

        $this->uid = $this->getMultiUid($length);
        app('gc-api-trace')->setTrace($this->uid);
    }

    public function __invoke(array $record)
    {
        $record['extra']['uid'] = $this->uid;
        return $record;
    }

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    public function reset()
    {
        $this->uid = $this->getMultiUid(strlen($this->uid));
    }

    private function getMultiUid($length)
    {
        return $this->getUidFromHeader() ?: $this->generateUid($length);
    }

    private function getUidFromHeader()
    {
        $trace_id =  Arr::get($_SERVER, 'HTTP_X_TRACE_ID', '');
        return $trace_id;
    }

    private function generateUid($length)
    {
        return substr(hash('md5', uniqid('', true)), 0, $length);
    }
}
