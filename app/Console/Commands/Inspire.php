<?php

namespace App\Console\Commands;

use App\CallFlow;
use App\SomlengEWS\Repositories\CallFlows\CallFlowRepositoryInterface;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Log;

class Inspire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    /**
     * Execute the console command.
     *
     * @param CallFlowRepositoryInterface $callFlow
     * @return mixed
     */
    public function handle(CallFlowRepositoryInterface $callFlow)
    {
        Log::info($callFlow->create(1, 'test2', 'test2', 1));
    }
}
