<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CallLog extends Model
{
    protected $fillable = ['phone', 'status', 'duration', 'time', 'date', 'retries', 'project_id', 'call_flow_id', 'retry_time', 'max_retry','activity_id'];
    public $timestamps = false;
}
