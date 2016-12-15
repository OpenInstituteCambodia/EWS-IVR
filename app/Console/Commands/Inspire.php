<?php

namespace App\Console\Commands;

use App\CallFlow;
use App\OutboundCall;
use App\PhoneCall;
use App\SomlengClient;
use App\SomlengEWS\Repositories\CallFlows\CallFlowRepositoryInterface;
use App\SomlengEWS\Repositories\OutboundCalls\OutboundCallRepositoryInterface;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Twilio\Exceptions\RestException;

class Inspire extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display an inspiring quote';

    /**
     * Execute the console command.
     *
     * @param CallFlowRepositoryInterface $callFlow
     * @param OutboundCallRepositoryInterface $outboundCall
     * @return mixed
     */
    public function handle(CallFlowRepositoryInterface $callFlow, OutboundCallRepositoryInterface $outboundCall)
    {
        $accountSid = env(env('VOICE_PLATFORM') . '_ACCOUNT_SID');
        $authToken = env(env('VOICE_PLATFORM') . '_AUTH_TOKEN');
        $number = env(env('VOICE_PLATFORM') . '_NUMBER');
        $client = new SomlengClient($accountSid, $authToken);
        try {
            $call = $client->calls->create(
                '+85586234665',
                $number,
                array(
                    'url' => 'http://demo.twilio.com/docs/voice.xml'
                )
            );
            Log::info($call->sid);
        } catch (RestException $e) {
            Log::info("TEST");
        }
    }
}
