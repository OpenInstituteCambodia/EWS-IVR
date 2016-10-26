<?php

namespace App\Http\Controllers;

use App\CallFlow;
use App\CallLog;
use App\QueueCall;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Log;
use Services_Twilio_Twiml;

class EwsIVRController extends Controller
{


    /**
     * Start calling IVR in EWS System
     * @param $sound
     * @return Services_Twilio_Twiml
     */
    public function ivrCalling($sound)
    {
        $soundFilePath = $this->getSoundPath($sound);
        Log::info($soundFilePath);
        $response = new Services_Twilio_Twiml();
        $gather = $response->gather(
            ['numDigits' => '1', 'action' => route('ews-ivr-calling', ['sound' => $sound], false), 'finishOnKey' => '*']
        );
        $gather->play($soundFilePath);
        return $response;
    }

    /**
     * @param $retry
     * @param $activityId
     * @param $maxRetry
     * @param $retryTime
     * @param $callFlowId
     * @param Request $request
     * @internal param $differentTime
     * @internal param $makeRetry
     */
    public function statusChecking($retry, $activityId, $maxRetry, $retryTime, $callFlowId, Request $request)
    {
        $status = $request->CallStatus;
        $duration = $request->CallDuration;
        $dateTime = Carbon::createFromFormat('D, d M Y H:i:s O', $request->Timestamp);
        $dateTime->timezone = new  DateTimeZone('Asia/Phnom_Penh');
        $phone = substr_replace($request->Called, '0', 0, 4);
        // Log::info($status . $duration . $dateTime->toTimeString() . $phone);
        if ($status == 'busy' || $status == 'failed' || $status == 'no-answer' || $status == 'canceled') {
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
    private function getSoundPath($soundId)
    {
        $soundFileId = $soundId;
        $soundFilename = CallFlow::where('id', '=', $soundFileId)->first()->sound_file_path;
        $soundFilePath = asset('uploads/sounds/' . $soundFilename);
        return $soundFilePath;
    }
}
