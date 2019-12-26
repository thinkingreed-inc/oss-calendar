<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use \Illuminate\Database\Eloquent\SoftDeletes;
use App\Notifications\CustomPasswordReset;
use App\Notifications\ChangeEmail;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'lastname', 'firstname', 'email', 'password',
        'home_page_id','calendarlist_id','setting_id','role_id','is_enable'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'email_verified_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        "is_enable" => "boolean"
    ];

    public function AauthAcessToken(){
        return $this->hasMany('\App\Models\OauthAccessToken');
    }

    /**
     * パスワードリセット通知の送信
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomPasswordReset($token));
    }

    /**
     * メールアドレス変更通知の送信
     *
     * @param  string  $token
     * @return void
     */
    public function sendEmailChangeNotification($token)
    {
        $this->notify(new ChangeEmail($token));
    }
}
