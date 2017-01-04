<?php

namespace App\Http\Controllers;

use App\CallFlow;
use App\CallLog;
use App\OutboundCall;
use App\PhoneCall;
use App\QueueCall;
use App\SomlengEWS\Repositories\CallFlows\CallFlowRepositoryInterface;
use App\SomlengEWS\Repositories\PhoneCalls\PhoneCallRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Twilio\Twiml;

class EwsIVRController extends Controller
{
    protected $callFlow;
    protected $phoneCall;

    /**
     * EwsIVRController constructor.
     * @param CallFlowRepositoryInterface $callFlow
     * @param PhoneCallRepositoryInterface $phoneCall
     */
    public function __construct(CallFlowRepositoryInterface $callFlow, PhoneCallRepositoryInterface $phoneCall)
    {
        $this->callFlow = $callFlow;
        $this->phoneCall = $phoneCall;
    }

    public function processDataUpload(Request $request)
    {
        $phoneContactJson = $request->input('contacts');
        $activityId = $request->input('activity_id');
        $max_retries = ($request->input('no_of_retry')) ? $request->input('no_of_retry') : Config::get('constants.DEFAULT_RETRY_CALL');
        $retryDifferentTime = ($request->input('retry_time')) ? $request->input('retry_time') : Config::get('constants.DEFAULT_RETRY_DIFFERENT_TIME');
        $soundUrl = $request->input('sound_url');

        // Create record in call_flows table and get inserted record id
        $callFlowId = CallFlow::create(
            [
                'project_id' => 1,
                'sound_file_path' => $soundUrl,
                'activity_id' => $activityId,
                'retry_duration' => $retryDifferentTime
            ]
        )->id;
        $contacts = json_decode($phoneContactJson);
        foreach ($contacts as $contact) {
            // Create record in phone_calls table with status queued
            PhoneCall::create(
                [
                    'max_retries' => $max_retries,
                    'phone_number' => preg_replace('/^(\s855|\+855|855)/', '0', $contact->phone),// phone number with +855( request url encode + sign to space) or 855 must replace with 0
                    'status' => 'queued',
                    'last_tried_at' => Carbon::now()->toDateTimeString(),
                    'call_flow_id' => $callFlowId
                ]
            );
        }
    }

    /**
     * Start calling IVR in EWS System
     * @param Request $request
     * @return Twiml
     * @internal param $sound
     */
    public
    function ivrCalling(Request $request)
    {
        $callSid = $request->CallSid;
        $outboundCallObject = OutboundCall::where('call_sid', '=', $callSid)->first();
        $soundUrl = $outboundCallObject->phoneCall->callFlow->sound_file_path;
        $response = new Twiml();
        $gather = $response->gather(
            ['numDigits' => '1', 'action' => route('ews-ivr-calling'), 'finishOnKey' => '*']
        );
        $gather->play($soundUrl);
        return $response;
    }

    /**
     * @param Request $request
     * @internal param $retry
     * @internal param $activityId
     * @internal param $maxRetry
     * @internal param $retryTime
     * @internal param $callFlowId
     * @internal param $differentTime
     * @internal param $makeRetry
     */
    public
    function statusChecking(Request $request)
    {
        // 1. check request for call_sid
        // 2. look up call_sid in outbound_calls table
        // 3. update outbound call with request info
        $callSid = $request->CallSid;
        $status = $request->CallStatus;
        $duration = $request->CallDuration;
        OutboundCall::where('call_sid', '=', $callSid)
            ->update(
                [
                    'status' => $status,
                    'duration' => $duration
                ]
            );
        // Update phone call status
        $phoneCallId = OutboundCall::where('call_sid', '=', $callSid)->first()->phoneCall->id;
        PhoneCall::where('id', '=', $phoneCallId)->update(['status' => $status, 'last_tried_at' => Carbon::now('Asia/Phnom_Penh')->toDateTimeString()]);
        /*
         * Get call information data to inserting to EWS database
         * Raw SQL
         * SELECT
         *  outbound_calls.*, phone_calls.outbound_calls_count,
         *  phone_calls.phone_number,
         *  phone_calls.call_flow_id,
         *  call_flows.project_id,
         *  call_flows.activity_id
         * FROM
         *  outbound_calls
         *  INNER JOIN phone_calls ON outbound_calls.phone_call_id = phone_calls.id
         *  INNER JOIN call_flows ON call_flows.id = phone_calls.call_flow_id
         * WHERE
         *  outbound_calls.call_sid = ?
         *
         * */
        $callLogData = DB::table('outbound_calls')
            ->join('phone_calls', 'phone_calls.id', '=', 'outbound_calls.phone_call_id')
            ->join('call_flows', 'call_flows.id', '=', 'phone_calls.call_flow_id')
            ->where('outbound_calls.call_sid', '=', $callSid)
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
        // Check if status completed and retries time  equal to make retries insert data to EWS database
        if (($status == 'completed') || $status == 'failed') {
            $this->insertToEWSCallLogDb($callArrayToInsert);
        } else {
            if (($callLogData->max_retries == $callLogData->outbound_calls_count)) {
                $this->insertToEWSCallLogDb($callArrayToInsert);
            }
        }
        return;
    }

    /**
     * Insert call log data to EWS database with EWS API
     * @param array $callLogRecord
     */
    private function insertToEWSCallLogDb($callLogRecord = [])
    {
        $data = array("api_token" => Config::get('constants.EWS-API-TOKEN'), "clog" => json_encode($callLogRecord));
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
    }
}
