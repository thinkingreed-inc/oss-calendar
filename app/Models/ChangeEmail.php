<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangeEmail extends Model
{
    //
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'user_id', 'token'
    ];
}
