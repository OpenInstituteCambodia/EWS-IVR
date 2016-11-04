<?php

namespace App\Console\Commands;

use App\CallFlow;
use App\PhoneCall;
use App\SomlengEWS\Repositories\CallFlows\CallFlowRepositoryInterface;
use App\SomlengEWS\Repositories\OutboundCalls\OutboundCallRepositoryInterface;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\DB;
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
     * @param OutboundCallRepositoryInterface $outboundCall
     * @return mixed
     */
    public function handle(CallFlowRepositoryInterface $callFlow, OutboundCallRepositoryInterface $outboundCall)
    {
        $call = DB::table('phone_calls')
            ->join('call_flows', 'call_flows.id', '=', 'phone_calls.call_flow_id')
            ->where('phone_calls.status', '=', 'queued')
            ->orWhere(function ($query) {
                $query->where('phone_calls.outbound_calls_count', '<', 3)
                    ->whereIn('phone_calls.status', ['busy', 'no-answer'])
                    ->whereRaw("TIMESTAMPDIFF(MINUTE,phone_calls.last_tried_at,NOW()) > call_flows.retry_duration ");
            })
            ->get();

        /*$phoneCallsBusy = PhoneCall::join('call_flows', 'call_flows.id', '=', 'phone_calls.call_flow_id')
            ->where('phone_calls.status', '=', 'queued')
            ->orWhereRaw("(phone_calls.`status` IN ('failed','busy','no-answer') AND (TIMESTAMPDIFF(MINUTE,phone_calls.last_tried_at,NOW()) > call_flows.retry_duration) AND (phone_calls.outbound_calls_count < phone_calls.max_retries))")
            ->get();*/
        Log::info($call);
    }
}
