<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendee extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'schedule_id',
        'user_id',
        'attendee_id',
        'email',
        'display_name',
        'organizer',
        'self',
        'resource',
        'optional',
        'response_status_id',
        'comment',
        'additional_guests',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        "organizer" => "boolean"
    ];

    public function schedule()
    {
        return $this->belongsTo('App\Models\Schedule');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
