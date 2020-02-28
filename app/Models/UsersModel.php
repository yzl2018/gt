<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsersModel extends Model
{
	/**
	 * 用户表
	 *
	 * @var string
	 */
    protected $table = "users";

	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
    protected $fillable = [
		'code', 'email','name','phone','login_fail_times','user_type_code','user_code','active_status','language_type_code'
	];

	/**
	 * 在数组中隐藏的属性
	 *
	 * @var array
	 */
    protected $hidden = [
		'password','operate_password','latest_login_time', 'remember_token',
	];

}
