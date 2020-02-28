<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoginLogModel extends Model
{
	use SoftDeletes;

	/**
	 * 用户登陆日志表
	 *
	 * @var string
	 */
	protected $table = "login_log";
	
	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
	protected $fillable = [
		'client_ip','ip_address','login_user','login_at','login_status','fail_reason'
	];

	/**
	 * 在数组中隐藏的属性
	 *
	 * @var array
	 */
	protected $hidden = [
		'deleted_at'
	];

}
