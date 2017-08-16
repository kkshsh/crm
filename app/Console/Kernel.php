<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\Inspire::class,
        \App\Console\Commands\GetGmailLables::class,
        \App\Console\Commands\GetGmailCredentials::class,
        \App\Console\Commands\GetMessages::class,
        \App\Console\Commands\SendMessages::class,
        \App\Console\Commands\SetMessageRead::class,
        \App\Console\Commands\GetMutiChannelMessages::class,
        \App\Console\Commands\SendChannelMessages::class,
        \App\Console\Commands\GetEbayCases::class,
        \App\Console\Commands\SyncGmailAccountLabels::class,
        \App\Console\Commands\ChangeMutiGmailStatus::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('inspire')->hourly();
/*        $schedule->command('message:get')->everyMinute();
        $schedule->command('message:send')->everyMinute();*/
       // $schedule->command('message:setRead')->everyMinute();

        $schedule->command('syncGmailLabels:get')->daily();
        $schedule->command('import:message:get amazon')->everyMinute();
        $schedule->command('import:message:get aliexpress')->everyThirtyMinutes();



    }
}
