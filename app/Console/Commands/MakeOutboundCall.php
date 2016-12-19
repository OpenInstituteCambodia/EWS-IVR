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
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Make Call with Twilio API or Somleng API according to ENV set
        $accountSid = env(env('VOICE_PLATFORM') . '_ACCOUNT_SID');
        $authToken = env(env('VOICE_PLATFORM') . '_AUTH_TOKEN');
        $number = env(env('VOICE_PLATFORM') . '_NUMBER');
        $client = new SomlengClient($accountSid, $authToken);
        // Find all phone calls records with status failed OR busy OR no_answer and
        $sql = <<<EOT
                SELECT
                phone_calls.*
                FROM
                    phone_calls
                INNER JOIN call_flows ON call_flows.id = phone_calls.call_flow_id
                WHERE
                    phone_calls. STATUS = 'queued'
                OR (
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
                        NOW()
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
                );
EOT;

        $recordsToMakeCall = DB::select($sql, [Carbon::now()->toDateTimeString()]);
        if (count($recordsToMakeCall) > 0) {
            foreach ($recordsToMakeCall as $phoneCall) {
                $phoneNumber = substr_replace($phoneCall->phone_number, '+855', 0, 1);
                try {
                    $call = $client->calls->create(
                        $phoneNumber,
                        $number,
                        array(
                            'url' => route('ews-ivr-calling'),
                            'StatusCallbackEvent' => ['completed'],
                            'StatusCallback' => route('ews-call-status-check')
                        )
                    );
                    // Create call record in outbound_calls table with status queued
                    $this->outboundCallObject->create($phoneCall->id, $call->sid, 'queued', 0);
                    // Update phone call record status to sent
                    PhoneCall::where('id', '=', $phoneCall->id)->update(['status' => 'sent']);
                } catch (RestException $e) {
                    // Create a record in outbound_calls table with status error
                    $this->outboundCallObject->create($phoneCall->id, '', 'error', 0);
                    // Update phone call record status to error
                    PhoneCall::where('id', '=', $phoneCall->id)->update(['status' => 'error','platform_http_status_code' => $e->getCode()]);
                }
            }
        }
    }
}
