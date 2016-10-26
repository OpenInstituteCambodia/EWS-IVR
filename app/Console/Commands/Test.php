<?php

namespace App\Console\Commands;

use App\QueueCall;
use App\Twilio\Repositories\CallLogs\CallLogRepositoryInterface;
use App\Twilio\Repositories\QueueCalls\QueueCallRepositoryInterface;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Services_Twilio;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $queueCallObject;
    protected $callLogObject;

    /**
     * Create a new command instance.
     *
     * @param QueueCallRepositoryInterface $queueCallObject
     * @param CallLogRepositoryInterface $callLogObject
     */
    public function __construct(QueueCallRepositoryInterface $queueCallObject, CallLogRepositoryInterface $callLogObject)
    {
        parent::__construct();
        $this->queueCallObject = $queueCallObject;
        $this->callLogObject = $callLogObject;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $comUrl = 'http://bongpheak.com/files/sounds/kh/jobs/job_10.mp3';
        $devUrl = 'http://dev.bongpheak.com/files/sounds/kh/jobs/job_43.mp3';
        $comUrlPiece = explode('/', $comUrl);
        $devUrlPiece = explode('/', $devUrl);
        Log::info($comUrlPiece);
        Log::info($devUrlPiece);
        /* file_put_contents(public_path('bong_pheak_resources/IVR-MONO-WAV/interaction.txt'), '');
         Log::info(config('constants.BONG-PHEAK-API_TOKEN'));
         Log::info(config('constants.BONG-PHEAK-STORE_SHARE_RECORD_API'));
         Log::info(config('constants.BONG-PHEAK-STORE_APPLY_RECORD_API'));*/
        /*Log::info(asset('bong_pheak_resources/IVR-MONO-WAV/wrong_number.wav'));
        return null;*/
        /* $accountSID = env('TWILIO_ACCOUNT_SID');
         $authToken = env('TWILIO_AUTH_TOKEN');
         $twilioNumber = env('TWILIO_NUMBER');
         $client = new Services_Twilio($accountSID, $authToken);
         $phone = '+85517641855';
         $call = $client->account->calls->create(
             $twilioNumber,
             $phone,
             //route('outboundCalling', ['sound' => $callFlowId]),
             'http://8dae19c2.ngrok.io/bongPheakIVR/job-offer',
             array(
                 'StatusCallbackEvent' => ['completed'],
                 'StatusCallback' => 'http://8dae19c2.ngrok.io/bongPheakIVR/status-checking'
                 //'StatusCallback' => route('statusCheck', ['retry' => $retry, 'activityId' => $activityId, 'maxRetry' => $maxRetry, 'retryTime' => $retryTime, 'callFlowId' => $callFlowId]),
                 //'StatusCallback' => 'http://7f39eff9.ngrok.io/api/statusChecking/' . $retry . '/' . $activityId . '/' . $maxRetry . '/' . $retryTime . '/' . $callFlowId,
             )
         );*/
    }
}
