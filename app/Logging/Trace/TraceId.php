<?php
namespace App\Logging\Trace;

class TraceId
{

    private $trace=null;

    /**
     * @return null
     */
    public function getTrace()
    {
        return $this->trace;
    }

    /**
     * @param  null  $trace
     */
    public function setTrace($trace): void
    {
        $this->trace = $trace;
    }

}