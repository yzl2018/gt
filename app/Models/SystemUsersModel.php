<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemUsersModel extends Model
{
	/**
	 * 指定表名
	 *
	 * @var string
	 */
	protected $table = "system_users";

	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
	protected $fillable = [
		'sys_ip','sys_name','authorization_code','communication_key','status'
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
