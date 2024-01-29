<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        Commands\UpdatePriceCommand::class,
        Commands\UpdateUserCommand::class,
        Commands\UpdateManager::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('update_total_price_catalog:cron')
                 ->everyTwoMinutes();
        $schedule->command('login_user:cron')
                 ->everyTenMinutes();
        $schedule->command('login_manager:cron')
                 ->everyThirtyMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
