<?php

namespace App\Console\Commands;

use App\PhoneCall;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateNoSeccessSentCall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:sent_record';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all record with status sent and last more than one day in table phone_calls to status failed';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Update all queued calls that sent last 12 hours agon
        PhoneCall::where('status', 'sent')
            ->whereRaw('TIMESTAMPDIFF(HOUR,phone_calls.updated_at,?) >= ?', [Carbon::now()->toDateTimeString(), config('constants.HOUR_TO_CLEAR_SENT_CALLS')])
            ->update(['status' => 'failed']);
    }
}
