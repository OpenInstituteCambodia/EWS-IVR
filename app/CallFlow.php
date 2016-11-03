<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CallFlow extends Model
{
    protected $fillable = ['project_id', 'activity_id', 'sound_file_path', 'contact_file_path'];

    /**
     * Get the project that own the call_flow
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get Phone Calls for the call flow
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function phoneCalls()
    {
        return $this->hasMany(PhoneCall::class);
    }
}
