<?php

namespace App\Http\Controllers;

use App\CallLog;
use App\OutboundCall;
use App\PhoneCall;
use App\QueueCall;
use App\SomlengEWS\Repositories\CallFlows\CallFlowRepositoryInterface;
use App\SomlengEWS\Repositories\PhoneCalls\PhoneCallRepositoryInterface;
use Aws\S3\S3Client;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
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
        $numberOfRetry = ($request->input('no_of_retry')) ? $request->input('no_of_retry') : Config::get('constants.DEFAULT_RETRY_CALL');
        $retryDifferentTime = ($request->input('retry_time')) ? $request->input('retry_time') : Config::get('constants.DEFAULT_RETRY_DIFFERENT_TIME');
        $soundFileObject = $request->file('soundFile');

        $stringDateTime = str_replace(' ', ':', $stringDateTime = Carbon::now('Asia/Phnom_Penh')->toDateTimeString());
        $soundFilename = $stringDateTime . "_" . $request->file('soundFile')->getClientOriginalName();
        $phoneContactFileName = $stringDateTime . '_phone_contacts.json';

        // Upload sound file and contact as json to AWS s3 storage
        $storage = Storage::disk('s3');
        // Upload sound file as public access
        $uploadedSound = $storage->put('sounds/' . $soundFilename, fopen($soundFileObject->getRealPath(), 'r+'), 'public');
        // Upload contact as private access
        $uploadedPhoneContact = $storage->put('phone_contacts/' . $phoneContactFileName, $phoneContactJson);
        // Check if we upload successfully
        if ($uploadedSound == true && $uploadedPhoneContact == true) {
            // Create resource information in CallFlow table
            $callFlowId = $this->callFlow->create(1, $soundFilename, $phoneContactFileName, $activityId);

            // Get content of contacts.json file: phone number
            // For get contents of AWS s3 private file content we must use AWS S3 Client
            $s3Client = new S3Client([
                'credentials' => [
                    'key' => env('S3_KEY'),
                    'secret' => env('S3_SECRET')
                ],
                'region' => env('S3_REGION'),
                'version' => '2006-03-01', // latest AWS S3 API Version :http://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html
            ]);
            // Use FlySystem to upload and download file from AWS s3
            $adapter = new AwsS3Adapter($s3Client, env('S3_BUCKET')); // AWS s3 Adapter for FlySystem
            $filesystem = new Filesystem($adapter);
            $contacts = json_decode($filesystem->read('phone_contacts/' . $phoneContactFileName));
            foreach ($contacts as $contact) {
                $this->phoneCall->create($numberOfRetry, $contact->phone, 'queued', 0, Carbon::now('Asia/Phnom_Penh')->toDateTimeString(), $retryDifferentTime, $callFlowId);
            }
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
        $soundUrl = $request->input('soundUrl');
        $response = new Twiml();
        $gather = $response->gather(
            ['numDigits' => '1', 'action' => route('ews-ivr-calling', ['soundUrl' => $soundUrl], false), 'finishOnKey' => '*']
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
        $callLogData = $call = DB::table('outbound_calls')
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
        if (($status == 'completed') || ($callLogData->max_retries == $callLogData->outbound_calls_count)) {
            $this->insertToEWSCallLogDb($callArrayToInsert);
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
