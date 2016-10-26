<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Log;
use Services_Twilio;
use Services_Twilio_Twiml;
use GuzzleHttp\Client as GuzzleHttpClient;

class BongPheakIVRController extends Controller
{
    /**
     * Twilio Calling Service
     *
     * @var Services_Twilio
     */
    private $twilioService;
    /**
     * Twilio Twiml Service
     *
     * @var Services_Twilio_Twiml
     */
    private $twimlService;
    /**
     * Twilio Accound SID
     *
     * @var string
     */
    private $twilioAccoundSid;
    /**
     * Twilio Authentication Token
     *
     * @var string
     */
    private $twilioAuthToken;
    /**
     * Twilio Number
     *
     * @var string
     */
    private $twilioNumber;
    /**
     * IVR Call Interaction file path
     *
     * @var string
     */
    private $interactionFilePath;
    /**
     * Bong Pheak API token
     *
     * @var string
     */
    private $bongPheakApiToken;
    /**
     * Bong Pheak Store Share Record API
     *
     * @var string URI
     */
    private $bongPheakStoreShareRecordApi;
    /**
     * Bong Pheak Store Apply Record API
     *
     * @var string URL
     */
    private $bongPheakStoreApplyRecordApi;

    /**
     * BongPheakIVRController constructor.
     */
    public function __construct()
    {
        $this->twilioAccoundSid = env('TWILIO_ACCOUNT_SID');
        $this->twilioAuthToken = env('TWILIO_AUTH_TOKEN');
        $this->twilioNumber = env('TWILIO_NUMBER');
        $this->twilioService = new  Services_Twilio($this->twilioAccoundSid, $this->twilioAuthToken);
        $this->twimlService = new Services_Twilio_Twiml();
        $this->interactionFilePath = public_path('bong_pheak_resources/IVR-MONO-WAV/interaction.txt');
        $this->bongPheakApiToken = config('constants.BONG-PHEAK-API_TOKEN');
        $this->bongPheakStoreShareRecordApi = config('constants.BONG-PHEAK-STORE_SHARE_RECORD_API');
        $this->bongPheakStoreApplyRecordApi = config('constants.BONG-PHEAK-STORE_APPLY_RECORD_API');

    }

    /**
     * @param Request $request
     * Request key come from Bong Pheak
     * 'sharesGender', 'sharesName', 'sharesPhone', 'sharesWho'
     */
    public function bongPheakCallAPI(Request $request)
    {
        // Covert phone number to support Twilio format Example: +85517641855
        $sharesPhone = substr_replace($request->input('sharesPhone'), '+855', 0, 1);
        $sharesWho = $request->input('sharesWho');
        $soundUrl = $request->input('soundUrl');
        $sharesGender = $request->input('sharesGender');
        $sharesName = $request->input('sharesName');
        $fkAnnouncementsID = $request->input('fkAnnouncementsID');
        $fkUsersID = $request->input('fkUsersID');
        $fkCompaniesID = $request->input('fkCompaniesID');
        $fkPositionsID = $request->input('fkPositionsID');
        // Create Call instance
        $call = $this->twilioService->account->calls->create(
            $this->twilioNumber,
            $sharesPhone,
            route('job-offer', ['sharesWho' => $sharesWho, 'soundUrl' => $soundUrl]),
            array(
                'StatusCallbackEvent' => ['completed'],
                'StatusCallback' => route('status-checking',
                    [
                        'sharesWho' => $sharesWho,
                        'sharesGender' => $sharesGender,
                        'sharesName' => $sharesName,
                        'fkAnnouncementsID' => $fkAnnouncementsID,
                        'fkUsersID' => $fkUsersID,
                        'fkCompaniesID' => $fkCompaniesID,
                        'fkPositionsID' => $fkPositionsID
                    ]),
            )
        );
    }

    /**
     * Play job announcement sound
     *
     * @param Request $request
     * @return Services_Twilio_Twiml
     */
    public function playJobOffer(Request $request)
    {
        $soundJobUrl = $request->input('soundUrl');
        $sharesWho = $request->input('sharesWho');
        $response = $this->twimlService;
        $gather = $response->gather(
            ['numDigits' => '1', 'action' => route('job-offer-menu', false)]
        );
        $soundIntroduction = asset('bong_pheak_resources/IVR-MONO-WAV/introduction.wav');
        $soundAdviser = asset('bong_pheak_resources/IVR-MONO-WAV/friend.wav');
        switch ($sharesWho) {
            case 1: // Adviser is listener sibling
                $soundAdviser = asset('bong_pheak_resources/IVR-MONO-WAV/sibling.wav');
                break;
            case 2:// Adviser is listener relative
                $soundAdviser = asset('bong_pheak_resources/IVR-MONO-WAV/relative.wav');
                break;
            case 3: // Adviser is listener friend
                $soundAdviser = asset('bong_pheak_resources/IVR-MONO-WAV/friend.wav');
                break;
            case 4: // Adviser is listener Acquaintance
                $soundAdviser = asset('bong_pheak_resources/IVR-MONO-WAV/known.wav');
                break;
        }
        $soundOptions = asset('bong_pheak_resources/IVR-MONO-WAV/sound_option.wav');
        $gather->play($soundIntroduction);
        $gather->play($soundAdviser);
        $gather->play($soundJobUrl);
        $gather->play($soundOptions);
        // write interaction '[]' if listener press end call or they don't press any option and it hangup
        $this->writeInteraction($this->interactionFilePath, '[]');
        return $response;
    }

    /**
     * @return Services_Twilio_Twiml
     */
    public function playJobMenuAgain()
    {
        $response = $this->twimlService;
        $gather = $response->gather(
            ['numDigits' => '1', 'action' => route('job-offer-menu', false)]
        );
        $soundOptions = asset('bong_pheak_resources/IVR-MONO-WAV/sound_option.wav');
        $gather->play($soundOptions);
        return $response;
    }

    /**
     * Show job announcement select option menu
     *
     * @param Request $request
     * @return Services_Twilio_Twiml
     */
    public function showJobOfferMenu(Request $request)
    {
        $selectOption = $request->input('Digits');
        switch ($selectOption) {
            case 0: // Listener press 0 option to hangup call
                // store [] in interaction.txt file play thank-you.mp3 and hangup
                $this->writeInteraction($this->interactionFilePath, '[]');
                $this->twimlService->play(asset('bong_pheak_resources/IVR-MONO-WAV/thank_bye.wav'));
                $this->twimlService->hangup();
                return $this->twimlService;
            case 1: // Listener press 1 to apply for job
                // go to skill url
                $this->twimlService->redirect(route('skill-clarification', [], false));
                return $this->twimlService;
            case 2: // Listener press 2 to receive this announcement in an hour later
                // store [2] in interaction.txt file and play will-call-in-hour.mp3 and hangup
                $this->writeInteraction($this->interactionFilePath, '[2]');
                $this->twimlService->play(asset('bong_pheak_resources/IVR-MONO-WAV/one_hour_sound_reply.wav'));
                $this->twimlService->hangup();
                return $this->twimlService;
            case 3:// Listener press 2 to receive this announcement tomorrow at same time
                // store [3] in interaction.txt file and play will-call-in-hour.mp3 and hangup
                $this->writeInteraction($this->interactionFilePath, '[3]');
                $this->twimlService->play(asset('bong_pheak_resources/IVR-MONO-WAV/one_day_sound_reply.wav'));
                $this->twimlService->hangup();
                return $this->twimlService;
            case 4: // listen to job offer again
                $this->twimlService->redirect(route('job-offer', [], false));
                return $this->twimlService;
            default: // when listener press wrong key they will here the menu sound again and try to select option again
                $this->twimlService->play(asset('bong_pheak_resources/IVR-MONO-WAV/wrong_number.wav'));
                $this->twimlService->redirect(route('job-menu-again', [], false));
                return $this->twimlService;
        }
        // Delay 5 seconds before hangup
        $this->twimlService->pause(['length' => 5]);
        $this->twimlService->hangup();
        return $this->twimlService;
    }

    /**
     * @return Services_Twilio_Twiml
     */
    public function playSkillClarification()
    {
        $response = $this->twimlService;
        $gather = $response->gather(
            ['numDigits' => '1', 'action' => route('skill-clarification-menu', false)]
        );
        $soundOptions = asset('bong_pheak_resources/IVR-MONO-WAV/skill_no_skill_option.wav');
        $gather->play($soundOptions);
        $this->writeInteraction($this->interactionFilePath, '[1,0]');
        return $response;
    }

    /**
     * Skill menu interaction for listener
     *
     * @param Request $request
     * @return Services_Twilio_Twiml
     */
    public function showSkillClarificationMenu(Request $request)
    {
        // get listener select option
        $selectOption = $request->input('Digits');
        switch ($selectOption) {
            case 1: // if listener press key 1 means that she/he has skill or experience of this job
                $this->writeInteraction($this->interactionFilePath, '[1,1]');
                $this->twimlService->play(asset('bong_pheak_resources/IVR-MONO-WAV/company_will_contact.wav'));
                $this->twimlService->hangup();
                return $this->twimlService;
            case 2: // if listener press key 2 means that she/he has skill or experience of this job
                $this->writeInteraction($this->interactionFilePath, '[1,2]');
                $this->twimlService->play(asset('bong_pheak_resources/IVR-MONO-WAV/company_will_contact.wav'));
                $this->twimlService->hangup();
                return $this->twimlService;
            default:
                $this->twimlService->play(asset('bong_pheak_resources/IVR-MONO-WAV/wrong_number.wav'));
                $this->twimlService->redirect(route('skill-clarification', [], false));
                return $this->twimlService;
        }
        // Delay 5 seconds before hangup
        $this->twimlService->pause(['length' => 5]);
        $this->twimlService->hangup();
        return $this->twimlService;
    }

    /**
     * Checking call status after call completed
     *
     * @param Request $request
     */
    public function statusChecking(Request $request)
    {
        $date = Carbon::now()->toDateString();
        $time = Carbon::now()->toTimeString();
        $interaction = $this->getInteractionString($this->interactionFilePath);
        $shareGender = $request->input('sharesGender');
        $sharesName = $request->input('sharesName');
        $sharesWho = $request->input('sharesWho');
        $fkAnnouncementsID = $request->input('fkAnnouncementsID');
        $fkUsersID = $request->input('fkUsersID');
        $fkCompaniesID = $request->input('fkCompaniesID');
        $fkPositionsID = $request->input('fkPositionsID');
        $sharesPhone = substr_replace($request->To, '0', 0, 4);
        $callStatus = $request->CallStatus;
        $callDuration = $request->CallDuration;
        /* fields list to insert into  shares table of Bong Pheak System */
        $sharesRecordFields = [
            "sharesGender" => $shareGender,
            "sharesName" => $sharesName,
            "sharesPhone" => $sharesPhone,
            "sharesWho" => $sharesWho,
            "sharesTime" => $time,
            "sharesDuration" => $callDuration,
            "sharesStatusCall" => $callStatus,
            "sharesDate" => $date,
            "sharesType" => 1,
            'fkAnnouncementsID' => $fkAnnouncementsID,
            'sharesInteraction' => $interaction
        ];
        /* fields list to insert into apply table of Bong Pheak System*/
        $applyRecordFields = [
            'fkAnnouncementsID' => $fkAnnouncementsID,
            'fkUsersID' => $fkUsersID,
            'fkCompaniesID' => $fkCompaniesID,
            'fkPositionsID' => $fkPositionsID,
            'jobApplyGender' => $shareGender,
            'jobApplyName' => $sharesName,
            'jobApplyPhone' => $sharesPhone,
            'jobApplyBy' => 2   // job apply: 1= apply via web, 2 apply via phone, 3 apply via Facebook
        ];
        if ($callStatus == 'completed') {
            // after listener apply for job
            if ($interaction == '[1,0]' || $interaction == '[1,1]' || $interaction == '[1,2]') {
                $sharesRecordFields['sharesAppliedOrNot'] = 1;
                $fkSharesID = $this->createSharesRecord($this->bongPheakApiToken, json_encode($sharesRecordFields), json_encode($applyRecordFields));
                $applyRecordFields['fkSharesID'] = $fkSharesID->fkSharesID;
                switch ($interaction) {
                    /* listener apply but don't mention that he/she have skill or not */
                    case '[1,0]':
                        $applyRecordFields['jobApplyExperience'] = 0; // job apply experience 0 means none not mentioned
                        break;
                    /* listener apply but don't mention that he/she have suitable skill for this job */
                    case '[1,1]':
                        $applyRecordFields['jobApplyExperience'] = 1; // job apply experience 1 means have experience for this job
                        break;
                    /* listener apply but don't mention that he/she does't  have suitable skill for this job */
                    case '[1,2]':
                        $applyRecordFields['jobApplyExperience'] = 2; // job apply experience 2 means no experience with this type of job
                        break;
                }
                $this->createApplyRecord($this->bongPheakApiToken, json_encode($applyRecordFields));
            } // listener does not apply the job
            else {
                switch ($interaction) {
                    case '[2]':// Listener want to listen to this job announcement an hour later
                        $retryTime = Carbon::now()->addHour()->toTimeString();
                        $retryDate = Carbon::now()->toDateString();
                        $sharesRecordFields['sharesRetryTime'] = $retryTime;
                        $sharesRecordFields['sharesRetryDate'] = $retryDate;
                        break;
                    case '[3]': // Listener want to listen to this job announcement tomorrow at same time
                        $retryTime = Carbon::now()->toTimeString();
                        $retryDate = Carbon::now()->addDay()->toDateString();
                        $sharesRecordFields['sharesRetryTime'] = $retryTime;
                        $sharesRecordFields['sharesRetryDate'] = $retryDate;
                        break;
                }
                $this->createSharesRecord($this->bongPheakApiToken, json_encode($sharesRecordFields), json_encode($applyRecordFields));
            }
            /* After status checking completed we must clear interaction file */
            $this->writeInteraction($this->interactionFilePath, '');
        } // when called failed, no-answer, busy, or cancel
        else {
            $this->createSharesRecord($this->bongPheakApiToken, json_encode($sharesRecordFields), json_encode($applyRecordFields));
        }

    }

    /**
     * Insert share record to Bong Pheak database
     *
     * @param string $apiToken Bong Pheak API Token
     * @param string $sharesRecordJson
     * @param  string $sharesApplyRecordJson
     * @return integer inserted record ID
     */
    private function createSharesRecord($apiToken, $sharesRecordJson, $sharesApplyRecordJson)
    {
        $data = array("api_token" => $apiToken, "sharesRecord" => $sharesRecordJson, 'sharesApplyRecord' => $sharesApplyRecordJson);
        // Using laravel php libray GuzzleHttp for execute external API(Eg: Bong Pheak API)
        $client = new GuzzleHttpClient();
        $response = $client->request('POST', $this->bongPheakStoreShareRecordApi, ['json' => $data]);
        return json_decode($response->getBody());
    }

    /**
     * Insert apply record to Bong Pheak database
     *
     * @param $apiToken
     * @param $applyRecordJson
     * @return mixed|\Psr\Http\Message\ResponseInterface
     */
    private function createApplyRecord($apiToken, $applyRecordJson)
    {
        $data = array("api_token" => $apiToken, "sharesApplyRecord" => $applyRecordJson);
        // Using laravel php libray GuzzleHttp for execute external API(Eg: Bong Pheak API)
        $client = new GuzzleHttpClient();
        $response = $client->request('POST', $this->bongPheakStoreApplyRecordApi, ['json' => $data]);
        return true;
    }

    /**
     * Write IVR call interaction string to interaction.txt file
     *
     * @param string $filename the path of interaction.txt file(interaction.txt file in public/bong_pheak_resources/IVR-MONO-WAV)
     * @param string $interactionString interaction string of Bong Pheak IVR such as [], [1,0], [1,1], [1,2], [2], [3]
     */
    private function writeInteraction($filename, $interactionString)
    {
        file_put_contents($filename, $interactionString);
    }

    /**
     * Get IVR Calling Interaction String From interaction.txt file
     *
     * @param string $filename $filename the path of interaction.txt file(interaction.txt file in public/bong_pheak_resources/IVR-MONO-WAV)
     * @return string IVR Call interaction string
     */
    private function getInteractionString($filename)
    {
        return file_get_contents($filename);
    }
}
