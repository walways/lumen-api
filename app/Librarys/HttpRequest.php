<?php

namespace App\Librarys;

use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Log;

class HttpRequest
{
    private $headers    = ['Content-Type' => 'application/json'];
    private $host       = '';
    private $timeout    = 30;
    private $statusCode = 100;
    private $returnHeader = 0;

    public function setHost(string $host)
    {
        $this->host = $host;
        return $this;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setTimeout(int $timeout)
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function getTimeout()
    {
        return $this->timeout;
    }

    public function setHeaders($headers = [])
    {
        $this->headers = $headers;
        return $this;
    }

    public function getHeaders()
    {
        return $this->host;
    }

    public function getHttpCode()
    {
        return $this->statusCode;
    }

    public function setHttpCode($statusCode = 200)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    private function call($api = '', $raw = null, $method = 'GET', $addHeaders = [], $cookie = '', $auth = '', $proxy = 0)
    {

        $host = empty($this->host) ? '' : $this->host;

        $headers = array_merge($this->headers, $addHeaders);
        $trace_id = app('gc-api-trace')->getTrace();
        $headers['trace_id'] = $trace_id;
        foreach ($headers as $k => $v) {
            $headerArr[] = $k . ': ' . $v;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HEADER, $this->returnHeader);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $host . $api);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArr);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        switch ($proxy) {
            case 1:
                curl_setopt($ch, CURLOPT_PROXY, "http://127.0.0.1:1080");
                break;
            case 2:
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                curl_setopt($ch, CURLOPT_PROXY, "127.0.0.1");
                curl_setopt($ch, CURLOPT_PROXYPORT, "1081");
                break;
        }

        if (!empty($cookie)) {
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        }

        if (!empty($auth)) {
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($ch, CURLOPT_USERPWD, $auth);
        }

        if (!is_null($raw)) {
            if (is_array($raw) || is_object($raw)) {
                $raw = json_encode($raw, 320);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $raw);
        }

        $response   = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->setHttpCode($statusCode);

        $log = [
            'url'      => $host . $api,
            'method'   => $method,
            'header'   => $headers,
            'body'     => $raw,
            'response' => $response,
        ];

        Log::debug('=======CURL_LOG', $log);

        if (curl_errno($ch)) {
            throw new CustomException(curl_error($ch) . '[' . $statusCode . ']', curl_errno($ch));
        }

        $array    = json_decode($response, true);
        $isString = false;
        if ($array == false || $array == null) {
            $isString = true;
        }

        if ($statusCode >= 400) {
            curl_close($ch);
            if ($isString) {
                throw new CustomException($response, $statusCode);
            } else {
                if (isset($array['code']) && $array['code'] != 0) {
                    throw new CustomException($array['message'], $array['code']);
                }
                throw new CustomException(json_encode($array, 320), $statusCode);
            }
        }

        if (isset($array['code']) && $array['code'] != 0) {
            $message = 'http error';
            if (isset($array['msg'])) {
                $message = $array['msg'];
            }
            if (isset($array['message'])) {
                $message = $array['message'];
            }
            throw new CustomException($message, $array['code']);
        }

        curl_close($ch);
        if ($isString) {
            return $response;
        }

        return $array;
    }

    public function get($api, $headers = [], $cookie = '', $auth = '', $proxy = 0)
    {
        return $this->call($api, null, 'GET', $headers, $cookie, $auth, $proxy);
    }

    public function put($api, $raw = null, $headers = [], $cookie = '', $auth = '', $proxy = 0)
    {
        return $this->call($api, $raw, 'PUT', $headers, $cookie, $auth, $proxy);
    }

    public function post($api, $raw = null, $headers = [], $cookie = '', $auth = '', $proxy = 0)
    {
        return $this->call($api, $raw, 'POST', $headers, $cookie, $auth, $proxy);
    }

    public function patch($api, $raw = null, $headers = [], $cookie = '', $auth = '', $proxy = 0)
    {
        return $this->call($api, $raw, 'PATCH', $headers, $cookie, $auth, $proxy);
    }

    public function delete($api, $raw = null, $headers = [], $cookie = '', $auth = '', $proxy = 0)
    {
        return $this->call($api, $raw, 'DELETE', $headers, $cookie, $auth, $proxy);
    }
}
