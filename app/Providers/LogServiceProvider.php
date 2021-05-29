<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Logging\Logger\CustomLogger;

class LogServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $logger = new CustomLogger($this->app);
        $logger->initialization();

        // $this->app->singleton('log', function ($app) {
        //     //return new LogManager($app);
        //     $config = config('logging.channels.daily');
        //     // $request = app('request');
        //     // $extra = [
        //     //     'Host'        => $request->getHttpHost(),
        //     //     'Method'      => strtoupper($request->method()),
        //     //     'Headers'     => json_encode($request->headers->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        //     //     'PathInfo'    => $request->getPathInfo(),
        //     //     'QueryString' => $request->getQueryString(),
        //     //     'Protocol'    => $request->getProtocolVersion(),
        //     //     'IP'          => $request->ip(),
        //     //     'User-Agent'  => $request->userAgent(),
        //     //     'Params'      => json_encode($request->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        //     //     'RequestUri'  => $request->getRequestUri(),
        //     //     'RequestTime' => Carbon::now()->format('Y-m-d H:i:s'),
        //     // ];
        //     //
        //     // $streamHandler = new RotatingLevelFileHandler($config['path'], $config['days']??7, $this->level($config), true, $config['permission'] ?? null, $config['locking'] ?? false);
        //     // $streamHandler ->setFormatter(new LineFormatter("[%datetime%] [%extra.uid%] %channel%.%level_name%: %message% %context%\n", null, true, true));
        //     // $streamHandler->pushProcessor(new UidProcessor());
        //     //
        //     // $handler = new BufferLevelHandler($streamHandler, $config['buffer']??200, Logger::DEBUG, $config['bubble'] ?? true, true);
        //     //
        //     // return $this->app['log']->setHandlers($handler);;
        //
        // });
    }
}
