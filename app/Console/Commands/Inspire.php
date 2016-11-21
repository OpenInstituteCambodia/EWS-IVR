<?php

namespace App\Console\Commands;

use App\CallFlow;
use App\OutboundCall;
use App\PhoneCall;
use App\SomlengEWS\Repositories\CallFlows\CallFlowRepositoryInterface;
use App\SomlengEWS\Repositories\OutboundCalls\OutboundCallRepositoryInterface;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Config;
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
        $outboundCallObject = OutboundCall::where('call_sid','=','9adc68da-bed1-4531-b657-d4581f15ff49')->first();
        Log::info($outboundCallObject->phoneCall->callFlow->sound_file_path);
        /*$call = DB::table('phone_calls')
            ->join('call_flows', 'call_flows.id', '=', 'phone_calls.call_flow_id')
            ->where('phone_calls.status', '=', 'queued')
            ->orWhere(function ($query) {
                $query->where('phone_calls.outbound_calls_count', '<', 3)
                    ->whereIn('phone_calls.status', ['busy', 'no-answer'])
                    ->whereRaw("TIMESTAMPDIFF(MINUTE,phone_calls.last_tried_at,NOW()) > call_flows.retry_duration ");
            })
            ->get();*/

        /*$phoneCallsBusy = PhoneCall::join('call_flows', 'call_flows.id', '=', 'phone_calls.call_flow_id')
            ->where('phone_calls.status', '=', 'queued')
            ->orWhereRaw("(phone_calls.`status` IN ('failed','busy','no-answer') AND (TIMESTAMPDIFF(MINUTE,phone_calls.last_tried_at,NOW()) > call_flows.retry_duration) AND (phone_calls.outbound_calls_count < phone_calls.max_retries))")
            ->get();*/

        /*$callLogData = $call = DB::table('outbound_calls')
            ->join('phone_calls', 'phone_calls.id', '=', 'outbound_calls.phone_call_id')
            ->join('call_flows', 'call_flows.id', '=', 'phone_calls.call_flow_id')
            ->where('outbound_calls.call_sid', '=', '61134fa6-9d59-4692-b8dc-2cb49eaad11e')
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

        $dateTimeArray = explode(' ', $callLogData->updated_at);
        $date = $dateTimeArray[0];
        $time = $dateTimeArray[1];
        $callArrayToInsert = [
            'phone' => $callLogData->phone_number,
            'status' => $callLogData->status,
            'duration' => $callLogData->duration,
            'time' => $time,
            'date' => $date,
            'retries' => $callLogData->outbound_calls_count,
            'project_id' => $callLogData->project_id,
            'call_flow_id' => $callLogData->call_flow_id,
            'retry_time' => $callLogData->retry_duration,
            'max_retry' => $callLogData->max_retries,
            'activity_id' => $callLogData->activity_id
        ];
        if (($callLogData->status == 'completed') || ($callLogData->max_retries == $callLogData->outbound_calls_count)) {
            Log::info(json_encode($callArrayToInsert));
            Log::info(Config::get('constants.EWS-API-TOKEN'));
            $data = array("api_token" => Config::get('constants.EWS-API-TOKEN'), "clog" => json_encode($callArrayToInsert));
            $data_string = json_encode($data);
            $ch = curl_init('http://ews-dashboard-production.ap-southeast-1.elasticbeanstalk.com/api/v1/receivingcalllog');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data_string))
            );
            $result = curl_exec($ch);
            return;
        }*/
    }
}
