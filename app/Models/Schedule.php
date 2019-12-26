<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'calendarlist_id',
        'calendarlists_calendar_id',
        'kind',
        'etag',
        'event_id',
        'status_id',
        'created',
        'updated',
        'summary',
        'description',
        'location',
        'color_id',
        'creator',
        'organizer_id',
        'start_date',
        'end_date',
        'end_time_unspecified',
        'recurring_event_id',
        'original_start_time_id',
        'transparency',
        'visibility',
        'ical_uid',
        'sequence',
        'attendees_omitted',
        'extended_properties_id',
        'hangout_link',
        'gadget',
        'anyone_can_add_self',
        'guests_can_invite_others',
        'guests_can_modify',
        'guests_can_see_other_guests',
        'private_copy',
        'locked',
        'reminders_use_default',
        'source',
        'visibility_id',
        'public_setting_id',
        'event_type_id',
        'recurring',
        'recurring_id',
        'parent_id',
        'parent_uid',
        'deleted',
        'allday',
    ];

    protected $dates = [
        'start_date',
        'end_date'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendees()
    {
        return $this->hasMany('App\Models\Attendee')->orderBy("user_id", "asc");
    }
    public function recurrence()
    {
        return $this->hasOne('App\Models\Recurring');
    }
    public function event_type()
    {
        return $this->belongsTo('App\Models\EventType');
    }
}
