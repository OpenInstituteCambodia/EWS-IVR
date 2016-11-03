<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /**
     * Get the user that own the project
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get Call_Flows from the project
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function callFlows()
    {
        return $this->hasMany(CallFlow::class);
    }
}
