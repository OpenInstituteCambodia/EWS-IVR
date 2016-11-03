<?php
/**
 * Created by PhpStorm.
 * User: keodina
 * Date: 11/3/16
 * Time: 5:11 PM
 */

namespace App\SomlengEWS\Repositories\PhoneCalls;


use Illuminate\Database\Eloquent\Model;

class PhoneCallRepository implements PhoneCallRepositoryInterface
{
    protected $phoneCallModel;

    /**
     * PhoneCallRepository constructor.
     * @param $phoneCallModel
     */
    public function __construct(Model $phoneCallModel)
    {
        $this->phoneCallModel = $phoneCallModel;
    }


    public function create($maxRetries, $phoneNumber, $status, $outboundCallCount, $lastTriedAt, $retryDuration, $callFlowId)
    {
        $this->phoneCallModel->create(
            [
                'max_retries' => $maxRetries,
                'phone_number' => $phoneNumber,
                'status' => $status,
                'outbound_calls_count' => $outboundCallCount,
                'last_tried_at' => $lastTriedAt,
                'retry_duration' => $retryDuration,
                'call_flow_id' => $callFlowId
            ]
        );
    }
}