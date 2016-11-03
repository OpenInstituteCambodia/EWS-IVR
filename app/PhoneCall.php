<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PhoneCall extends Model
{
    protected $fillable = ['max_retries', 'phone_number', 'status', 'outbound_calls_count', 'last_tried_at', 'call_flow_id', 'retry_duration'];

    /**
     * Get Call flow that own the phone call
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function callFlow()
    {
        return $this->belongsTo(CallFlow::class);
    }

    /**
     * Get outbound call for the phone call
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function outboundCalls()
    {
        return $this->hasMany(OutboundCall::class);
    }
}
