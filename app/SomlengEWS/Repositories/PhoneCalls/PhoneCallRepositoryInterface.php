<?php
/**
 * Created by PhpStorm.
 * User: keodina
 * Date: 11/3/16
 * Time: 5:12 PM
 */

namespace App\SomlengEWS\Repositories\PhoneCalls;


interface PhoneCallRepositoryInterface
{
    public function create($maxRetries, $phoneNumber, $status, $outboundCallCount, $lastTriedAt, $callFlowId);

}