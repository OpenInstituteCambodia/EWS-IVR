<?php
/**
 * Created by PhpStorm.
 * User: keodina
 * Date: 11/3/16
 * Time: 4:25 PM
 */

namespace App\SomlengEWS\Repositories\CallFlows;


use Illuminate\Database\Eloquent\Model;

class CallFlowRepository implements CallFlowRepositoryInterface
{
    protected $callFlowModel;

    /**
     * CallFlowRepository constructor.
     * @param $callFlowModel
     */
    public function __construct(Model $callFlowModel)
    {
        $this->callFlowModel = $callFlowModel;
    }


    /**
     * Create Call Flow record
     * @param $projectId
     * @param $soundFilePath
     * @param $contactFilePath
     * @param $activityId
     * @param $retryDuration
     * @return mixed
     */
    public function create($projectId, $soundFilePath, $contactFilePath, $activityId, $retryDuration)
    {
        $callFlow = $this->callFlowModel->create(
            [
                'project_id' => $projectId,
                'sound_file_path' => $soundFilePath,
                'contact_file_path' => $contactFilePath,
                'activity_id' => $activityId,
                'retry_duration' => $retryDuration
            ]
        );
        return $callFlow->id;
    }
}