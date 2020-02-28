<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens,Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code','name', 'email','phone', 'password','operate_password','user_type_code','user_code','safety_code','language_type_code','active_status','latest_login_time'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password','operate_password','latest_login_time', 'remember_token',
    ];

	/**
	 * 修改默认验证用户名为email
	 *
	 * @param $username
	 * @return mixed
	 */
	public function findForPassport($username)
	{
		return $this->Where('email', $username)->first();
	}
}
