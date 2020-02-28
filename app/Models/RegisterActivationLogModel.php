<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RegisterActivationLogModel extends Model
{

	use SoftDeletes;

	/**
	 * 注册激活日志表
	 *
	 * @var string
	 */
	protected $table = "register_activation_log";
	
	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
	protected $fillable = [
		'users_id','client_ip','active_code','active_status','response_message'
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
