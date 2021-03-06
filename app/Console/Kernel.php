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
        Commands\ComputeUserStats::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('compute:stats')
            ->dailyAt('22:00');

        $schedule->command('compute:visits')
            ->hourly();

        // temporary disabled
        // todo: fix
        // $schedule->command('backup:run')
        //     ->weekly()
        //     ->appendOutputTo(storage_path('logs/schedule.log'));

        $schedule->exec('curl ' . config('bible-matcher.host') . ':' . config('bible-matcher.port') . '/match-songs')
            ->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
