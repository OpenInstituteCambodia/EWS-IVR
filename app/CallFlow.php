<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CallFlow extends Model
{
    protected $fillable = ['project_id', 'sound_file_path', 'contact_file_path', 'date'];
    public $timestamps = false;
}
