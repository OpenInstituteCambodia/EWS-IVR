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


    /**
     * Create phone call record
     * @param $maxRetries
     * @param $phoneNumber
     * @param $status
     * @param $outboundCallCount
     * @param $lastTriedAt
     * @param $callFlowId
     * @internal param $retryDuration
     * @return phone call id
     */
    public function create($maxRetries, $phoneNumber, $status, $outboundCallCount, $lastTriedAt, $callFlowId)
    {
        $phoneCall = $this->phoneCallModel->create(
            [
                'max_retries' => $maxRetries,
                'phone_number' => $phoneNumber,
                'status' => $status,
                'outbound_calls_count' => $outboundCallCount,
                'last_tried_at' => $lastTriedAt,
                'call_flow_id' => $callFlowId
            ]
        );
        return $phoneCall->id;
    }
}