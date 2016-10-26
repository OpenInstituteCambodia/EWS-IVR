<?php
/**
 * Created by PhpStorm.
 * User: keodina
 * Date: 8/22/16
 * Time: 2:44 PM
 */

namespace App\Twilio\Repositories\CallLogs;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class CallLogRepository implements CallLogRepositoryInterface
{
    protected $callLogModel;

    /**
     * CallLogRepository constructor.
     * @param $callLogModel
     */
    public function __construct(Model $callLogModel)
    {
        $this->callLogModel = $callLogModel;
    }


    public function getFirstRecord()
    {
        Log::info("TEST");
    }
}