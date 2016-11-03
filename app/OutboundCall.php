<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OutboundCall extends Model
{
    protected $fillable = ['phone_call_id', 'call_sid', 'status', 'duration'];

    /**
     * Get phone call that own outbound call
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function phoneCall()
    {
        return $this->belongsTo(PhoneCall::class);
    }
}
