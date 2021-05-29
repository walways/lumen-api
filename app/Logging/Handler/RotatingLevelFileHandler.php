<?php

namespace App\Logging\Handler;

use Monolog\Handler\RotatingFileHandler;

class RotatingLevelFileHandler extends RotatingFileHandler
{

    protected $appDirCreated;
    protected $errorMessage;

   
    public function initFilename($filename)
    {
        $this->__construct($filename, $this->maxFiles, $this->level, $this->bubble, $this->filePermission, $this->useLocking);
    }
    
    public function getFilename()
    {
        return $this->filename;
    }

    public function handle(array $record) :bool
    {
        if (!$this->isHandling($record)) {
            return false;
        }

        $record = $this->processRecord($record);

        $record['formatted'] = $this->getFormatter()->format($record);

        $this->write($record);

        return false === $this->bubble;
    }


    /**
     * override function
     *
     * @param array $records
     * @return void
     */
    public function handleBatch(array $records) : void
    {
        $content = [];
        foreach ($records as $record) {
            $record = $this->handle($record);
            if(!empty($record)){
                $content[] = $record['formatted'];
            }
        }

        if (null === $this->mustRotate) {
            $this->mustRotate = !file_exists($this->url);
        }

        if ($this->nextRotation < $records[0]['datetime']) {
            $this->mustRotate = true;
            $this->close();
        }

        // $this->writeContent(join($content, ''));
    }


    /**
     * override function
     *
     * @param string $content
     * @return void
     */
    protected function writeContent(string $content)
    {

        if (!is_resource($this->stream)) {
            if (null === $this->url || '' === $this->url) {
                throw new \LogicException('Missing stream url, the stream can not be opened. This may be caused by a premature call to close().');
            }
            
            // Do not try to create dir if it has already been tried.
            if (!$this->appDirCreated) {
                $dir = $this->getDirFromStream($this->url);
                
                if (null !== $dir && !is_dir($dir)) {
                    $this->errorMessage = null;
                    set_error_handler(array($this, 'customErrorHandler'));
                    $status = mkdir($dir, 0777, true);
                    restore_error_handler();
                    
                    if (false === $status && !is_dir($dir)) {
                        throw new \UnexpectedValueException(sprintf('There is no existing directory at "%s" and its not buildable: '.$this->errorMessage, $dir));
                    }
                }
                $this->appDirCreated = true;
            }

            
            $this->errorMessage = null;
            set_error_handler(array($this, 'customErrorHandler'));
            
            $this->stream = fopen($this->url, 'a');
            
            if ($this->filePermission !== null) {
                @chmod($this->url, $this->filePermission);
            }
            restore_error_handler();
            if (!is_resource($this->stream)) {
                $this->stream = null;
                throw new \UnexpectedValueException(sprintf('The stream or file "%s" could not be opened: '.$this->errorMessage, $this->url));
            }
        }

        if ($this->useLocking) {
            // ignoring errors here, there's not much we can do about them
            flock($this->stream, LOCK_EX);
        }

        $this->streamWriteContent($this->stream, $content);

        if ($this->useLocking) {
            flock($this->stream, LOCK_UN);
        }
    }


    /**
     * override function
     *
     * @param [type] $stream
     * @param string $content
     * @return void
     */
    protected function streamWriteContent($stream, string $content)
    {
        
        fwrite($stream, $content);
    }


    /**
     * override function
     *
     * @param [type] $stream
     * @return void
     */
    protected function getDirFromStream($stream)
    {
        $pos = strpos($stream, '://');
        if ($pos === false) {
            return dirname($stream);
        }

        if ('file://' === substr($stream, 0, 7)) {
            return dirname(substr($stream, 7));
        }

        return;
    }

    /**
     * override function
     *
     * @param [type] $code
     * @param [type] $msg
     * @return void
     */
    protected function customErrorHandler($code, $msg)
    {
        $this->errorMessage = preg_replace('{^(fopen|mkdir)\(.*?\): }', '', $msg);
    }
}
