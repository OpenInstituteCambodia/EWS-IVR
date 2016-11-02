<?php

namespace App\Console\Commands;

use App\SomlengClient;
use App\Twilio\Repositories\CallLogs\CallLogRepositoryInterface;
use App\Twilio\Repositories\QueueCalls\QueueCallRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Services_Twilio;

class RetryCall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'retry:call';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry call with phone number in queue call table';

    /**
     * Queue Call Repository Object
     * @var
     */
    protected $queueCallObject;

    /**
     * Call Log Repository Object
     * @var
     */
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
        /* current time */
        $currentTime = Carbon::now('Asia/Phnom_Penh')->toTimeString();
        $recordNeedToRetryCall = $this->queueCallObject->retryCallRecords($currentTime);
        if (count($recordNeedToRetryCall) == 0) {
            return;
        }
        // Make Call with Twilio
        $accountSid = env(env('VOICE_PLATFORM') . '_ACCOUNT_SID');
        $authToken = env(env('VOICE_PLATFORM') . '_AUTH_TOKEN');
        $number = env(env('VOICE_PLATFORM') . '_NUMBER');
        $client = new SomlengClient($accountSid, $authToken);

        /* make outbound call for each number */
        foreach ($recordNeedToRetryCall as $row) {
            $phoneNumber = substr_replace($row['phone'], '+855', 0, 1);
            $callFlowId = $row['call_flow_id'];
            $retry = $row['retry'];
            $maxRetry = $row['max_retry'];
            $retryTime = $row['retry_time'];
            $activityId = $row['activity_id'];
            $call = $client->calls->create(
                $phoneNumber,
                $number,
                array(
                    'url' => route('ews-ivr-calling', ['sound' => $callFlowId]),
                    'StatusCallbackEvent' => ['completed'],
                    'StatusCallback' => route('ews-call-status-check', ['retry' => $retry, 'activityId' => $activityId, 'maxRetry' => $maxRetry, 'retryTime' => $retryTime, 'callFlowId' => $callFlowId]),
                )
            );
        }
    }
}
