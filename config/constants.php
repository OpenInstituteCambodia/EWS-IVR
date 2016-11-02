<?php
/**
 * Created by PhpStorm.
 * User: keodina
 * Date: 8/12/16
 * Time: 8:50 AM
 */
return [
    'TWILIO_ACCOUNT_SID' => env('TWILIO_ACCOUNT_SID'),
    'TWILIO_AUTH_TOKEN' => env('TWILIO_AUTH_TOKEN'),
    'TWILIO_NUMBER' => env('TWILIO_NUMBER'),
    'DEFAULT_RETRY_CALL' => 3, // max retry is 3 times
    'DEFAULT_RETRY_DIFFERENT_TIME' => 10, // different time for every retry is 10 minutes
    'EWS-API-TOKEN' => 'ZtMSokqFGpEnXPcVG1gMguouKS1ZyVdZCpk5wYFypsePYQksMGqRdJSQ90Hi',
    'BONG-PHEAK-API_TOKEN' => 'C5hMvKeegj3l4vDhdLpgLChTucL9Xgl8tvtpKEjSdgfP433aNft0kbYlt77h',
    'BONG-PHEAK-STORE_SHARE_RECORD_API' => 'http://bongpheak.com/api/v1/storeSharedRecord',
    'BONG-PHEAK-STORE_APPLY_RECORD_API' => 'http://bongpheak.com/api/v1/storeApplyRecord',
    'EWS-SOUND-URL' => 'https://s3-ap-southeast-1.amazonaws.com/twilio-ews-resources/sounds/',
    'EWS-CONTACT-URL' => 'https://s3-ap-southeast-1.amazonaws.com/twilio-ews-resources/phone_contacts/',
];