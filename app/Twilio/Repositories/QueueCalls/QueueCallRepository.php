<?php
/**
 * Created by PhpStorm.
 * User: keodina
 * Date: 9/6/16
 * Time: 11:41 AM
 */

namespace App\Twilio\Repositories\QueueCalls;


use Illuminate\Database\Eloquent\Model;

class QueueCallRepository implements QueueCallRepositoryInterface
{
    protected $queueCallModel;

    /**
     * QueueCallRepository constructor.
     * @param Model $queueCallModel
     */
    public function __construct(Model $queueCallModel)
    {
        $this->queueCallModel = $queueCallModel;
    }

    /**
     * Get Records in table Queue Call which time <= current
     *to make retry call
     * @param $time - current time
     */
    public function retryCallRecords($time)
    {
        $retryCallRecords = $this->queueCallModel->where('time', '<=', $time)->get();
        $this->queueCallModel->where('time', '<=', $time)->delete();
        return $retryCallRecords;
    }
}