<?php
/**
 * Author wuwenxiong
 * Date: 2019/04/03
 */

namespace App\Logging\Processor;

use Monolog\Processor\ProcessorInterface;

class CustomWebProcessor implements ProcessorInterface
{


    /**
     * @var array|\ArrayAccess
     */
    protected $serverData;

    /**
     * Default fields
     *
     * Array is structured as [key in record.extra => key in $serverData]
     *
     * @var array
     */
    protected $extraFields = array(
        'http_host'    => 'HTTP_HOST',
        'url'         => 'REQUEST_URI',
        'http_method' => 'REQUEST_METHOD',
        'full_url'    => ''
    );


    /**
     * @param array|\ArrayAccess $serverData  Array or object w/ ArrayAccess that provides access to the $_SERVER data
     * @param array|null         $extraFields Field names and the related key inside $serverData to be added. If not provided it defaults to: url, ip, http_method, server, referrer
     */
    public function __construct($serverData = null, array $extraFields = null)
    {
        if (null === $serverData) {
            $this->serverData = &$_SERVER;
        } elseif (is_array($serverData) || $serverData instanceof \ArrayAccess) {
            $this->serverData = $serverData;
        } else {
            throw new \UnexpectedValueException('$serverData must be an array or object implementing ArrayAccess.');
        }

        if (null !== $extraFields) {
            if (isset($extraFields[0])) {
                foreach (array_keys($this->extraFields) as $fieldName) {
                    if (!in_array($fieldName, $extraFields)) {
                        unset($this->extraFields[$fieldName]);
                    }
                }
            } else {
                $this->extraFields = $extraFields;
            }
        }
    }

    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        // skip processing if for some reason request data
        // is not present (CLI or wonky SAPIs)
        if (!isset($this->serverData['REQUEST_URI'])) {
            return $record;
        }

        $record['extra'] = $this->appendExtraFields($record['extra']);

        return $record;
    }

    /**
     * @param  string $extraName
     * @param  string $serverName
     * @return $this
     */
    public function addExtraField($extraName, $serverName)
    {
        $this->extraFields[$extraName] = $serverName;

        return $this;
    }

    /**
     * @param  array $extra
     * @return array
     */
    public function appendExtraFields(array $extra)
    {
        foreach ($this->extraFields as $extraName => $serverName) {
            $extra[$extraName] = isset($this->serverData[$serverName]) ? $this->serverData[$serverName] : null;
        }

        if (isset($this->serverData['UNIQUE_ID'])) {
            $extra['unique_id'] = $this->serverData['UNIQUE_ID'];
        }


        $extra = $this->getUrlFullPath($extra);
        $extra['ip'] = $this->getUserIP();
        return $extra;
    }


    /**
     * get full path function
     *
     * @param [type] $extra
     * @return void
     */
    public function getUrlFullPath($extra)
    {

        if (isset($this->extraFields['full_url'])) {
            $extra['full_url'] = $extra['http_host'].$extra['url'];
        }
        unset($extra['http_host']);
        unset($extra['url']);
        return $extra;
    }


    /**
     * ip function
     *
     * @return string
     */
    public function getUserIP()
    {
        // Get real visitor IP behind CloudFlare network
        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
                  $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
                  $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }
        $client  = @$_SERVER['HTTP_CLIENT_IP'];
        $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
        $remote  = $_SERVER['REMOTE_ADDR'];
    
        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }
    
        return $ip;
    }
}
