<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponseCrmLogModel extends Model
{
	/**
	 * 激活响应日志表
	 *
	 * @var string
	 */
	protected $table = "response_crm_log";
	
	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
	protected $fillable = [
		'merchant_code','to_url','parameters','sign_data','response_time'
	];
}
