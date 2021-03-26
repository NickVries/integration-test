<?php
declare(strict_types=1);
namespace App\Console;

use App\Console\Commands\CacheAddresses;
use App\Console\Commands\CacheItems;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use MyParcelCom\ConcurrencySafeMigrations\Commands\Migrate;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Migrate::class,
        CacheAddresses::class,
        CacheItems::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('exact:cache:addresses')->everySixHours();
        $schedule->command('exact:cache:items')->everySixHours();
    }
}
