<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recurring extends Model
{
    //
    protected $fillable = [
        'schedule_id',
        'start_date',
        'end_date',
        'frequency',
        'parent_id',
        'deleted'
    ];
    public function schedule()
    {
        return $this->belongsTo('App\Models\Schedule');
    }
}

