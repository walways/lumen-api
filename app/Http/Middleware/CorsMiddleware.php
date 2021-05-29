<?php
namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;

class CorsMiddleware
{

    public function handle(Request $request, \Closure $next)
    {
        Log::info('cookie:', [$_COOKIE]);
        $reffer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER']: Arr::get($_SERVER, 'HTTP_ORIGIN');
        $scheme    = parse_url($reffer, PHP_URL_SCHEME);
        $domain    = parse_url($reffer, PHP_URL_HOST);
        $port      = parse_url($reffer, PHP_URL_PORT);

        $refferUrl = !$port ? $scheme . '://' . $domain : $scheme . '://' . $domain .':'. $port;
        if ($request->isMethod('options')) {
            $headers = [
                'Access-Control-Allow-Headers' => $request->header('Access-Control-Request-Headers'),
                'Access-Control-Allow-Origin'  => $refferUrl,
            ];
            return new Response('ok', 200, $headers);
        }

        Log::info('reffer_domain:', [$refferUrl]);

        $trace_id = substr(hash('md5', uniqid('', true)), 0, 7);
        $response = $next($request);

        $response->headers->set('HTTP_X_TRACE_ID', $trace_id);
        $response->headers->set('Access-Control-Allow-Origin', $refferUrl);
        return $response;
    }
}
