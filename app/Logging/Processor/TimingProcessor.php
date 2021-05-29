<?php


namespace App\Logging\Processor;

use Monolog\Processor\ProcessorInterface;

class TimingProcessor implements ProcessorInterface
{
    protected $beginTimeStamp;

    public function __construct()
    {
        $this->beginTimeStamp = $this->getStartTime();
    }

    public function __invoke(array $record)
    {
        $now = $this->getCurrentTime();

        $record['extra']['request_time'] = $now - $this->beginTimeStamp;

        return $record;
    }

    protected function getStartTime()
    {
        if (defined('LARAVEL_START')) {
            $start = LARAVEL_START;
        } else {
            $start = microtime(true);
        }

        return round($start*1000);
    }

    protected function getCurrentTime()
    {
        return round(microtime(true)*1000);
    }
}
