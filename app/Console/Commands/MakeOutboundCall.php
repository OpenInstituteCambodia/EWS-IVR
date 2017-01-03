<?php

namespace App\Console\Commands;

use App\OutboundCall;
use App\PhoneCall;
use App\SomlengClient;
use App\SomlengEWS\Repositories\OutboundCalls\OutboundCallRepository;
use App\SomlengEWS\Repositories\OutboundCalls\OutboundCallRepositoryInterface;
use App\SomlengEWS\Repositories\PhoneCalls\PhoneCallRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Twilio\Exceptions\RestException;

class MakeOutboundCall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:call';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make outbound call with record from phone_calls table.';

    protected $phoneCallObject;
    protected $outboundCallObject;
    protected $accountSid;
    protected $authToken;
    protected $number;
    protected $client;

    /**
     * Create a new command instance.
     *
     * @param PhoneCallRepositoryInterface $phoneCallObject
     * @param OutboundCallRepositoryInterface $outboundCallObject
     * @internal param OutboundCallRepositoryInterface $outboutCallObject
     */
    public function __construct(PhoneCallRepositoryInterface $phoneCallObject, OutboundCallRepositoryInterface $outboundCallObject)
    {
        parent::__construct();
        $this->phoneCallObject = $phoneCallObject;
        $this->outboundCallObject = $outboundCallObject;
        // Make Call with Twilio API or Somleng API according to ENV set
        $this->accountSid = env(env('VOICE_PLATFORM') . '_ACCOUNT_SID');
        $this->authToken = env(env('VOICE_PLATFORM') . '_AUTH_TOKEN');
        $this->number = env(env('VOICE_PLATFORM') . '_NUMBER');
        $this->client = new SomlengClient($this->accountSid, $this->authToken);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Count phone calls are still active(status is sent)
        $activeCalls = PhoneCall::where('status', '=', 'sent')->count();
        // number record to be called
        $numberOfRecordTobeCalled = (int)env('MAX_SIMULTANEOUS_CALLS') - $activeCalls;
        // Get record with status queued to make call first
        $queuedRecords = PhoneCall::where('status', '=', 'queued')->limit($numberOfRecordTobeCalled)->get();
        // check if we enough queued record to be called
        if (count($queuedRecords) == $numberOfRecordTobeCalled) {
            foreach ($queuedRecords as $row) {
                // make call to each queued records
                $phoneNumber = substr_replace($row->phone_number, '+855', 0, 1);
                $this->makeCall($phoneNumber, $row->id);
            }
        } else { // don't have queued record or not have enough to be called
            // query record that need to retry call
            $numberOfRecordToBeRetry = $numberOfRecordTobeCalled - count($queuedRecords);
            $sql = <<<EOT
                SELECT
                phone_calls.*
                FROM
                    phone_calls
                INNER JOIN call_flows ON call_flows.id = phone_calls.call_flow_id
                WHERE
                (
                    (phone_calls. STATUS = 'error')
                    AND (
                        phone_calls.platform_http_status_code LIKE '5%'
                    )
                    AND (
                        phone_calls.outbound_calls_count < phone_calls.max_retries
                    )
                    AND TIMESTAMPDIFF(
                        MINUTE,
                        phone_calls.updated_at,
                        ?
                    ) > call_flows.retry_duration
                )
                OR (
                    (
                        phone_calls.outbound_calls_count < phone_calls.max_retries
                    )
                    AND (
                        phone_calls. STATUS IN ('busy', 'no-answer')
                    )
                    AND TIMESTAMPDIFF(
                        MINUTE,
                        phone_calls.updated_at,
                        ?
                    ) > call_flows.retry_duration
                ) LIMIT ?;
EOT;
            $retryRecords = DB::select($sql, [Carbon::now()->toDateTimeString(), Carbon::now()->toDateTimeString(), $numberOfRecordToBeRetry]);
            $queuedAndRetryRecords = array_merge(json_decode(json_encode($queuedRecords)), $retryRecords);
            // retry each record to be retried
            foreach ($queuedAndRetryRecords as $row) {
                $phoneNumber = substr_replace($row->phone_number, '+855', 0, 1);
                $this->makeCall($phoneNumber, $row->id);
            }
        }
    }

    /**
     * @param $phoneNumber
     * @param $phoneCallId
     */
    private function makeCall($phoneNumber, $phoneCallId)
    {
        try {
            $call = $this->client->calls->create(
                $phoneNumber,
                $this->number,
                array(
                    'url' => route('ews-ivr-calling'),
                    'StatusCallbackEvent' => ['completed'],
                    'StatusCallback' => route('ews-call-status-check')
                )
            );
            // Create call record in outbound_calls table with status queued
            $this->outboundCallObject->create($phoneCallId, $call->sid, 'queued', 0);
            // Update phone call record status to sent
            PhoneCall::where('id', '=', $phoneCallId)->update(['status' => 'sent']);
        } catch (RestException $e) {
            // Create a record in outbound_calls table with status error
            $this->outboundCallObject->create($phoneCallId, '', 'error', 0);
            // Update phone call record status to error
            PhoneCall::where('id', '=', $phoneCallId)->update(['status' => 'error', 'platform_http_status_code' => $e->getCode()]);
        }
    }
}
