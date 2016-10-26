<?php
/**
 * Created by PhpStorm.
 * User: keodina
 * Date: 8/22/16
 * Time: 2:43 PM
 */

namespace App\Twilio\Repositories\CallLogs;


interface CallLogRepositoryInterface
{
    public function getFirstRecord();
}