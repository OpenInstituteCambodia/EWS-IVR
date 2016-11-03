<?php

namespace App\Http\Controllers;

use App\CallFlow;
use App\CallLog;
use App\QueueCall;
use App\SomlengClient;
use App\SomlengEWS\Repositories\CallFlows\CallFlowRepositoryInterface;
use App\SomlengEWS\Repositories\PhoneCalls\PhoneCallRepositoryInterface;
use Aws\S3\S3Client;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Config;
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
    public function __construct(CallFlowRepositoryInterface $callFlow,  PhoneCallRepositoryInterface $phoneCall)
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
           $callFlowId =  $this->callFlow->create(1, $soundFilename, $phoneContactFileName, $activityId);

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
        $sound = $request->input('sound');
        $soundFilePath = $this->getSoundPath($sound);
        $response = new Twiml();
        $gather = $response->gather(
            ['numDigits' => '1', 'action' => route('ews-ivr-calling', ['sound' => $sound], false), 'finishOnKey' => '*']
        );
        $gather->play($soundFilePath);
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
        //3. create call_log and link to outbound call
        $retry = $request->input('retry');
        $status = $request->CallStatus;
        $duration = $request->CallDuration;
        $dateTime = Carbon::now('Asia/Phnom_Penh');
        $phone = substr_replace($request->To, '0', 0, 4);
        if ($status == 'busy' || $status == 'failed' || $status == 'no-answer' || $status == 'canceled') {
            $activityId = $request->input('activityId');
            $maxRetry = $request->input('maxRetry');
            $retryTime = $request->input('retryTime');
            $callFlowId = $request->input('callFlowId');
            $duration = 0;
            if ($retry < $maxRetry) {
                QueueCall::create([
                    'phone' => $phone,
                    'time' => $dateTime->addMinute($retryTime)->toTimeString(),
                    'call_flow_id' => $callFlowId,
                    'retry' => $retry + 1,
                    'max_retry' => $maxRetry,
                    'retry_time' => $retryTime,
                    'activity_id' => $activityId
                ]);
                return;
            }
        }
        $activityId = $request->input('activityId');
        $maxRetry = $request->input('maxRetry');
        $retryTime = $request->input('retryTime');
        $callFlowId = $request->input('callFlowId');
        $callLogFields = [
            'phone' => $phone,
            'status' => $status,
            'duration' => $duration,
            'time' => $dateTime->toTimeString(),
            'date' => $dateTime->toDateString(),
            'retries' => $retry,
            'project_id' => 1,
            'call_flow_id' => $callFlowId,
            'retry_time' => $retryTime,
            'max_retry' => $maxRetry,
            'activity_id' => $activityId
        ];
        CallLog::create($callLogFields);
        $data = array("api_token" => "ZtMSokqFGpEnXPcVG1gMguouKS1ZyVdZCpk5wYFypsePYQksMGqRdJSQ90Hi", "clog" => json_encode($callLogFields));
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

    /**
     * @param $soundId
     * @return string
     */
    private
    function getSoundPath($soundId)
    {
        $soundFileId = $soundId;
        $soundFilename = CallFlow::where('id', '=', $soundFileId)->first()->sound_file_path;
        $soundFilePath = Config::get('constants.EWS-SOUND-URL') . $soundFilename;
        return $soundFilePath;
    }
}
