<?php
/**
 * Created by PhpStorm.
 * User: keodina
 * Date: 11/4/16
 * Time: 10:12 AM
 */

namespace App\SomlengEWS\Repositories\OutboundCalls;


use Illuminate\Database\Eloquent\Model;

/**
 * Class OutboundCallRepository
 * @package App\SomlengEWS\Repositories\OutboundCalls
 */
class OutboundCallRepository implements OutboundCallRepositoryInterface
{
    protected $outboundCallModel;

    /**
     * OutboundCallRepository constructor.
     * @param $outboundCallModel
     */
    public function __construct(Model $outboundCallModel)
    {
        $this->outboundCallModel = $outboundCallModel;
    }


    /**
     * Create outbound call record
     * @param $phoneCallId
     * @param $callSid
     * @param $status
     * @param $duration
     * @return outbound call id
     */
    public function create($phoneCallId, $callSid, $status, $duration)
    {
        $outboundCall = $this->outboundCallModel->create(
            [
                'phone_call_id' => $phoneCallId,
                'call_sid' => $callSid,
                'status' => $status,
                'duration' => $duration
            ]
        );
        return $outboundCall->id;
    }
}