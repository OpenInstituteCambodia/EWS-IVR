<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\Inspire::class,
        Commands\MakeOutboundCall::class,
        Commands\UpdateNoSeccessSentCall::class
    ];

    /**
     * Kernel constructor.
     * @param Application $app
     * @param Dispatcher $event
     */
    public function __construct(Application $app, Dispatcher $event)
    {
        parent::__construct($app, $event);
        array_walk($this->bootstrappers, function (&$bootstrapper) {
            if ($bootstrapper === 'Illuminate\Foundation\Bootstrap\ConfigureLogging') {
                $bootstrapper = 'Bootstrap\ConfigureLogging';
            }
        });
    }

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /*$schedule->command('make:call')->everyMinute();
        $schedule->command('update:sent_record')->everyMinute();*/
    }
}
