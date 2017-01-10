<?php

namespace App\Console\Commands;

use App\CallFlow;
use App\OutboundCall;
use App\PhoneCall;
use App\SomlengClient;
use App\SomlengEWS\Repositories\CallFlows\CallFlowRepositoryInterface;
use App\SomlengEWS\Repositories\OutboundCalls\OutboundCallRepositoryInterface;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Twilio\Exceptions\RestException;

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
//        // Count phone calls are still active(status is sent)
//        $activeCalls = PhoneCall::where('status', '=', 'sent')->count();
//        // number record to be called
//        $numberOfRecordTobeCalled = (int)env('MAX_SIMULTANEOUS_CALLS') - $activeCalls;
//        // Get record with status queued to make call first
//        $queuedRecords = PhoneCall::where('status', '=', 'queued')->limit($numberOfRecordTobeCalled)->get();
//        // check if we enough queued record to be called
//        if (count($queuedRecords) == $numberOfRecordTobeCalled) {
//            foreach ($queuedRecords as $row) {
//                // make call to each record
//            }
//        } else { // don't have queued record or not have enough to be called
//            // query record that need to retry call
//            $numberOfRecordToBeRetry = $numberOfRecordTobeCalled - count($queuedRecords);
//            $sql = <<<EOT
//                SELECT
//                phone_calls.*
//                FROM
//                    phone_calls
//                INNER JOIN call_flows ON call_flows.id = phone_calls.call_flow_id
//                WHERE
//                (
//                    (phone_calls. STATUS = 'error')
//                    AND (
//                        phone_calls.platform_http_status_code LIKE '5%'
//                    )
//                    AND (
//                        phone_calls.outbound_calls_count < phone_calls.max_retries
//                    )
//                    AND TIMESTAMPDIFF(
//                        MINUTE,
//                        phone_calls.updated_at,
//                        ?
//                    ) > call_flows.retry_duration
//                )
//                OR (
//                    (
//                        phone_calls.outbound_calls_count < phone_calls.max_retries
//                    )
//                    AND (
//                        phone_calls. STATUS IN ('busy', 'no-answer')
//                    )
//                    AND TIMESTAMPDIFF(
//                        MINUTE,
//                        phone_calls.updated_at,
//                        ?
//                    ) > call_flows.retry_duration
//                ) LIMIT ?;
//EOT;
//            $retryRecords = DB::select($sql, [Carbon::now()->toDateTimeString(), Carbon::now()->toDateTimeString(), $numberOfRecordToBeRetry]);
//            $arrayMerge = array_merge(json_decode(json_encode($queuedRecords)), $retryRecords);
//            // retry each record to be retried
//            $i = 0;
//            foreach ($arrayMerge as $row) {
//                $i++;
//                Log::info($row->phone_number);
//                Log::info($i);
//            }
//        }
        $callLogData = DB::table('outbound_calls')
            ->join('phone_calls', 'phone_calls.id', '=', 'outbound_calls.phone_call_id')
            ->join('call_flows', 'call_flows.id', '=', 'phone_calls.call_flow_id')
            ->where('outbound_calls.call_sid', '=', 'bd8ad889-b1b2-4c1a-8795-897a1325fd80')
            ->select([
                'outbound_calls.*',
                'phone_calls.max_retries',
                'phone_calls.outbound_calls_count',
                'phone_calls.phone_number',
                'phone_calls.call_flow_id',
                'call_flows.project_id',
                'call_flows.activity_id',
                'call_flows.retry_duration'])
            ->first();

            Log::info(DB::select($sql));
            Log::info($callLogData->max_retries);
    }
}
