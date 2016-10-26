<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QueueCall extends Model
{
    protected $fillable = ['phone', 'time', 'call_flow_id', 'retry', 'max_retry', 'retry_time', 'activity_id'];
    public $timestamps = false;
}
