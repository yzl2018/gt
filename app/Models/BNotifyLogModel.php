<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BNotifyLogModel extends Model
{

	use SoftDeletes;

	/**
	 * B 系统通知日志表
	 *
	 * @var string
	 */
	protected $table = "b_notify_log";
	
	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
	protected $fillable = [
		'sys_ip','api','method','parameters','time','result','reprocess_times','reprocess_result','exception_response'
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
