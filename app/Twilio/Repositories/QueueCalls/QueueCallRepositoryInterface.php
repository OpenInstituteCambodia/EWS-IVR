<?php
/**
 * Created by PhpStorm.
 * User: keodina
 * Date: 9/6/16
 * Time: 11:42 AM
 */

namespace App\Twilio\Repositories\QueueCalls;


interface QueueCallRepositoryInterface
{
    /* get Records in table Queue Call which time <= current
        to make retry call
    */
    public function retryCallRecords($time);
}