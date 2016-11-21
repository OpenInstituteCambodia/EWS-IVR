<?php
/**
 * Created by PhpStorm.
 * User: keodina
 * Date: 11/3/16
 * Time: 4:25 PM
 */

namespace App\SomlengEWS\Repositories\CallFlows;


interface CallFlowRepositoryInterface
{
    public function create($projectId, $soundFilePath, $activityId, $retryDuration, $contactFilePath = '');
}