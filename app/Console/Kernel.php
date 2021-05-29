<?php

namespace App\Console;

use App\Console\Commands\ConfigDB\ConfigFlush;
use App\Console\Commands\ConfigDB\ConfigForget;
use App\Console\Commands\ConfigDB\ConfigUpdate;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\TestCommand::class,
        ConfigFlush::class,
        ConfigForget::class,
        ConfigUpdate::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('command:ad_refresh_token')->everyThirtyMinutes()->withoutOverlapping()->runInBackground(); // 每三十分钟
        // $schedule->command('command:ad_refresh_token')->everyFiveMinutes()->withoutOverlapping()->runInBackground(); // 每五分钟

        // $schedule->call(function () {
        //     $now = new \DateTime('now');
        //     Log::info("schedule " . $now->format('Y-m-d H:i:s'));
        // })->everyMinute();

    }
}
