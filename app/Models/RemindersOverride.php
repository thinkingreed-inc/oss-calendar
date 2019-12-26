<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemindersOverride extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'calendarlist_id',
        'user_id',
        'overrides_method_id',
        'overrides_minutes',
    ];

}
