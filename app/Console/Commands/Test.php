<?php

namespace App\Console\Commands;

use App\SomlengClient;
use App\Twilio\Repositories\CallLogs\CallLogRepositoryInterface;
use App\Twilio\Repositories\QueueCalls\QueueCallRepositoryInterface;
use Aws\S3\S3Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use Twilio\Exceptions\RestException;

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
      /* $client = new S3Client([
           'credentials' => [
               'key'    => env('S3_KEY'),
               'secret' => env('S3_SECRET')
           ],
           'region' => env('S3_REGION'),
           'version' => '2006-03-01',
       ]);

        $adapter = new AwsS3Adapter($client, env('S3_BUCKET'));
        $filesystem = new Filesystem($adapter);
        Log::info($filesystem->read('phone_contacts/2016-11-02:09:59:33_phone_contacts.json'));*/

        /*$accountSid = env(env('VOICE_PLATFORM') . '_ACCOUNT_SID');
        $authToken = env(env('VOICE_PLATFORM') . '_AUTH_TOKEN');
        $number = env(env('VOICE_PLATFORM') . '_NUMBER');
        $somlengClient = new SomlengClient($accountSid, $authToken);
        try {
            $call = $somlengClient->calls->create(
                '+85586234665',
                $number,
                array(
                    'url' => 'http://demo.twilio.com/docs/voice.xml',
                    'StatusCallbackEvent' => 'completed',
                    'StatusCallback' => 'http://1db7c3a1.ngrok.io/ewsIVR/ews-call-status-check'
                )
            );
        } catch (RestException $e) {
            Log::info($e->getMessage());
        }*/
    }
}
