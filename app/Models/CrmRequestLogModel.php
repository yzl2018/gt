<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmRequestLogModel extends Model
{
	use SoftDeletes;

	/**
	 * CRM 激活请求日志表
	 *
	 * @var string
	 */
	protected $table = "crm_request_log";

	/**
	 * 在数组中隐藏的属性
	 *
	 * @var array
	 */
	protected $hidden = [
		'deleted_at'
	];
}
