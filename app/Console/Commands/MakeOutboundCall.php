<?php

namespace App\Console\Commands;

use App\OutboundCall;
use App\PhoneCall;
use App\SomlengClient;
use App\SomlengEWS\Repositories\OutboundCalls\OutboundCallRepository;
use App\SomlengEWS\Repositories\OutboundCalls\OutboundCallRepositoryInterface;
use App\SomlengEWS\Repositories\PhoneCalls\PhoneCallRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        Log::info($_ENV);
        // Make Call with Twilio API or Somleng API according to ENV set
        $accountSid = env(env('VOICE_PLATFORM') . '_ACCOUNT_SID');
        $authToken = env(env('VOICE_PLATFORM') . '_AUTH_TOKEN');
        $number = env(env('VOICE_PLATFORM') . '_NUMBER');
        $client = new SomlengClient($accountSid, $authToken);
        // Find all phone calls records with status failed OR busy OR no_answer and
        // current time minus record modified time > retry_duration
        $recordsToMakeCall = DB::table('phone_calls')
            ->join('call_flows', 'call_flows.id', '=', 'phone_calls.call_flow_id')
            ->where('phone_calls.status', '=', 'queued')
            ->orWhere(function ($query) {
                $query->where('phone_calls.outbound_calls_count', '<', 3)
                    ->whereIn('phone_calls.status', ['busy', 'no-answer'])
                    ->whereRaw("TIMESTAMPDIFF(MINUTE,phone_calls.last_tried_at,NOW()) > call_flows.retry_duration ");
            })
            ->select(['phone_calls.id', 'phone_calls.phone_number', 'call_flows.sound_file_path'])
            ->get();
        if (count($recordsToMakeCall) > 0) {
            foreach ($recordsToMakeCall as $phoneCall) {
                $phoneNumber = substr_replace($phoneCall->phone_number, '+855', 0, 1);
                $soundFilePath = Config::get('constants.EWS-SOUND-URL') . $phoneCall->sound_file_path;
                $call = $client->calls->create(
                    $phoneNumber,
                    $number,
                    array(
                        'url' => route('ews-ivr-calling', ['soundUrl' => $soundFilePath]),
                        'StatusCallbackEvent' => ['completed'],
                        'StatusCallback' => route('ews-call-status-check')
                    )
                );
                // Create call record in outbound_calls table with status queued
                $this->outboundCallObject->create($phoneCall->id, $call->sid, 'queued', 0);
                // Update phone call record status to sent
                PhoneCall::where('id', '=', $phoneCall->id)->update(['status' => 'sent']);
            }
        }
    }
}
