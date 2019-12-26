<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calendarlist extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'calendar_id','kind','etag','summary','description','location',
        'time_zone','summary_override','color_id','background_color','foreground_color',
        'hidden','selected','access_role','primary','deleted',
    ];
}
