<?php

namespace App\Logging\Logger;

use Illuminate\Log\LogManager;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobExceptionOccurred;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\WorkerStopping;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Processor\IntrospectionProcessor;

use App\Logging\Handler\RotatingLevelFileHandler;
use App\Logging\Handler\BufferLevelHandler;
use App\Logging\Processor\UidProcessor;

class CustomLogger extends LogManager
{

    protected $handlers;

    public function __construct($app)
    {
        parent::__construct($app);
        $this->handlers=$this->app['log']->getHandlers();
        $this->restLogHandler();
    }

    public function initialization()
    {
        $config = config('logging.channels.daily');

        $streamHandler = new RotatingLevelFileHandler($config['path'], $config['days']??7, $this->level($config), true, $config['permission'] ?? null, $config['locking'] ?? false);
        $streamHandler ->setFormatter(new LineFormatter("[%datetime%] [%extra.uid%] %channel%.%level_name%: %message% %context%\n", null, true, true));
        $streamHandler->pushProcessor(new UidProcessor());

        $handler = new BufferLevelHandler($streamHandler, $config['buffer']??200, Logger::DEBUG, $config['bubble'] ?? true, true);
        $this->setLogHandler($handler);

        app('events')->listen(JobProcessed::class, function (JobProcessed $event) use ($handler) {
            $handler->flush();
        });

        app('events')->listen(JobExceptionOccurred::class, function (JobExceptionOccurred $event) use ($handler) {
            $handler->flush();
        });
    
        app('events')->listen(JobFailed::class, function (JobFailed $event) use ($handler) {
            $handler->flush();
        });

        app('events')->listen(WorkerStopping::class, function (WorkerStopping $event) use ($handler) {
            $handler->flush();
        });
    }


       /**
     * setLogHandler function
     *
     * @param [type] $handler
     * @return void
     */
    protected function setLogHandler($handler)
    {
        array_unshift($this->handlers, $handler);
        $this->app['log']->setHandlers($this->handlers);
    }

    /**
     * @return array
     */
    protected function restLogHandler()
    {
        if (!empty($this->handlers) && is_array($this->handlers)) {
            foreach ($this->handlers as $key => $handler) {
                if ($handler instanceof RotatingFileHandler) {
                    unset($this->handlers[$key]);
                }
            }
            return $this->handlers;
        } else {
            return [];
        }
    }
}
