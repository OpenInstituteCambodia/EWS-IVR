<?php
/**
 * Created by PhpStorm.
 * User: keodina
 * Date: 11/4/16
 * Time: 10:12 AM
 */

namespace App\SomlengEWS\Repositories\OutboundCalls;


interface OutboundCallRepositoryInterface
{
    public function create($phoneCallId, $callSid, $status, $duration);
}