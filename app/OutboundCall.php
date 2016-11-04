<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Nodes\CounterCache\CounterCacheable;
use Nodes\CounterCache\Traits\CounterCacheCreated;
use Nodes\CounterCache\Traits\CounterCacheRestored;

class OutboundCall extends Model implements CounterCacheable
{
    use CounterCacheCreated, CounterCacheRestored;

    protected $fillable = ['phone_call_id', 'call_sid', 'status', 'duration'];

    /**
     * Get phone call that own outbound call
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function phoneCall()
    {
        return $this->belongsTo(PhoneCall::class);
    }


    /**
     * Counter Cache outbound_calls_count field in table phone_call
     * when outbound call created and restored using trait above
     *
     * @author Morten Rugaard <moru@nodes.dk>
     *
     * @return array
     */
    public function counterCaches()
    {
        return [
            'outbound_calls_count' => 'phoneCall'
        ];
    }
}
