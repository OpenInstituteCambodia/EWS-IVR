<?php
/**
 * Created by PhpStorm.
 * User: keodina
 * Date: 10/18/16
 * Time: 10:59 AM
 */

namespace App;


use Twilio\Rest\Api;
use Twilio\Rest\Client;

/**
 * @property \App\SomlengV2010 v2010
 * @property \App\SomlengAccountContext account
 */

class SomlengApi extends Api
{

    public function __construct(Client $client)
    {
        parent::__construct($client);
        $this->baseUrl = env(env('VOICE_PLATFORM')."_API_ENDPOINT");
    }
}