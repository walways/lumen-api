<?php

namespace App\Logging\Handler;

use Illuminate\Support\Facades\Log;
use Monolog\Handler\BufferHandler;
use Monolog\Logger;
use App\Logging\Processor\CustomWebProcessor;
use App\Logging\Processor\TimingProcessor;
use Illuminate\Support\Arr;

/**
 * Undocumented class
 */
class BufferLevelHandler extends BufferHandler
{

    protected $timing;

    public function __construct(
        $handler,
        $bufferLimit = 0,
        $level = Logger::DEBUG,
        $bubble = true,
        $flushOnOverflow = false
    ) {
        parent::__construct($handler, $bufferLimit, $level, $bubble,
            $flushOnOverflow);

        //init timing
        $this->timing = new TimingProcessor();
    }

    /**
     * override handler function
     *
     * @param array $record
     *
     * @return void
     */
    public function handle(array $record): bool
    {
        if ($record['level'] < $this->level) {
            return false;
        }

        // if (empty($record['extra']) == false) {
        //     $traceID = Arr::get((array)$record['extra'], 'uid', '');
        //     //app('gc-api-trace')->setTrace($traceID);
        // }

        //level greater than warning
        if ($record['level'] > Logger::WARNING) {
            $pathInfo = pathinfo($this->handler->getFilename());
            $baseName = $pathInfo['basename'];

            //check whether is error level file
            if (!strpos($baseName, 'error')) {
                $file    = explode('.', $baseName);
                $newFile = $file[0] . '-error' . (!empty($file[1]) ? '.' . $file[1] : '');
                $this->handler->initFilename($pathInfo['dirname'] . '/' . $newFile);
            }
        }

        if (!$this->initialized) {
            // __destructor() doesn't get called on Fatal errors
            register_shutdown_function(array($this, 'close'));
            $this->initialized = true;
        }

        if ($this->bufferLimit > 0 && $this->bufferSize === $this->bufferLimit) {
            if ($this->flushOnOverflow) {
                $this->flush();
            } else {
                array_shift($this->buffer);
                $this->bufferSize--;
            }
        }

        if ($this->processors) {
            foreach ($this->processors as $processor) {
                $record = call_user_func($processor, $record);
            }
        }

        $this->buffer[] = $record;
        $this->bufferSize++;

        return false === $this->bubble;
    }


    /**
     * override flush function
     *
     * @return void
     */
    public function flush(): void
    {
        if ($this->bufferSize === 0) {
            return;
        }
        $extra      = ['extra' => []];
        $webProcess = new CustomWebProcessor();
        $webInfo    = $webProcess($extra);

        //add web info
        $timing  = $this->timing;
        $time    = $timing($extra);
        $context = array_merge($webInfo['extra'], $time['extra']);


        $headerRecord = [
            'message'    => 'request_info',
            'context'    => $context,
            'level'      => Logger::INFO,
            'level_name' => Logger::getLevelName(Logger::INFO),
            'channel'    => $this->buffer[0]['channel'],
            'datetime'   => $this->buffer[0]['datetime'],
            'extra'      => array(),
        ];


        array_unshift($this->buffer, $headerRecord);

        $this->handler->handleBatch($this->buffer);
        $this->clear();
    }
}
