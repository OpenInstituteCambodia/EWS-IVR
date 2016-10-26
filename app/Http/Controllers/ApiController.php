<?php

namespace App\Http\Controllers;

use App\CallFlow;
use App\Twilio\Repositories\CallLogs\CallLogRepositoryInterface;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Services_Twilio;

class ApiController extends Controller
{
    protected $callLog;

    /**
     * ApiController constructor.
     * @param CallLogRepositoryInterface $callLog
     */
    public function __construct(CallLogRepositoryInterface $callLog)
    {
        $this->callLog = $callLog;
    }

    public function processDataUpload(Request $request)
    {
//        /* Validation */
//        $messages = [
//            'soundFile.is_wav_file' => 'Your file must be .WAV file',
//        ];
//        /* We extend validate rule so go to checkout app/Provider/AppServiceProvider.php  */
//        $rules = [
//            'soundFile' => 'is_wav_file',
//        ];
//
//        $validator = Validator::make($request->all(), $rules, $messages);
//        if ($validator->fails()) {
//            return response()->json($validator->errors(), 203);
//        }
        // Upload resource file(sound file and contacts json file)
        $phoneContactJson = $request->input('contacts');
        $activityId = $request->input('activity_id');
        $numberOfRetry = ($request->input('no_of_retry')) ? $request->input('no_of_retry') : Config::get('constants.DEFAULT_RETRY_CALL');
        $retryDifferentTime = ($request->input('retry_time')) ? $request->input('retry_time') : Config::get('constants.DEFAULT_RETRY_DIFFERENT_TIME');
        $soundFileObject = $request->file('soundFile');

        $stringDateTime = str_replace(' ', ':', $stringDateTime = Carbon::now()->toDateTimeString());
        $soundFilename = $stringDateTime . "_" . $request->file('soundFile')->getClientOriginalName();
        $phoneContactFileName = $stringDateTime . '_phone_contacts.json';

        $storage = Storage::disk('local');
        $uploadedSound = $storage->put('sounds/' . $soundFilename, fopen($soundFileObject->getRealPath(), 'r+'));
        $uploadedPhoneContact = $storage->put('phone_contacts/' . $phoneContactFileName, $phoneContactJson);
        if ($uploadedSound == true && $uploadedPhoneContact == true) {
            // Create resource information in CallFlow table
            $callFlow = CallFlow::create([
                'project_id' => 1,
                'sound_file_path' => $soundFilename,
                'contact_file_path' => $phoneContactFileName,
                'date' => Carbon::now()->toDateString()
            ]);
            // Make Call with Twilio
            // $host = parse_url($request->url(), PHP_URL_HOST);
            $accountSID = env('TWILIO_ACCOUNT_SID');
            $authToken = env('TWILIO_AUTH_TOKEN');
            $twilioNumber = env('TWILIO_NUMBER');

            $client = new Services_Twilio($accountSID, $authToken);
            $path = public_path('uploads/phone_contacts/' . $phoneContactFileName);
            $contacts = json_decode(file_get_contents($path));
            foreach ($contacts as $contact) {
                $phone = substr_replace($contact->phone, '+855', 0, 1);
                $call = $client->account->calls->create(
                    $twilioNumber,
                    $phone,
                    route('ews-ivr-calling', ['sound' => $callFlow->id]),
                    //'http://b0fb74c6.ngrok.io/ewsIVR/ews-ivr-calling/' . $callFlow->id,
                    array(
                        'StatusCallbackEvent' => ['completed'],
                        'StatusCallback' => route('ews-call-status-check', ['retry' => 0, 'activityId' => $activityId, 'maxRetry' => $numberOfRetry, 'retryTime' => $retryDifferentTime, 'callFlowId' => $callFlow->id]),
                        //'StatusCallback' => 'http://b0fb74c6.ngrok.io/ewsIVR/ews-call-status-check/0/' . $activityId . '/' . $numberOfRetry . '/' . $retryDifferentTime . '/' . $callFlow->id,
                    )
                );
            }
            return json_encode(['success' => true]);
        }
        return json_encode(['success' => false]);
    }
}
