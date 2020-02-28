<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestGuideMailRecordsModel extends Model
{
	/**
	 * 注册授权表
	 *
	 * @var string
	 */
	protected $table = "request_guide_mail_records";

	/**
	 * 在数组中隐藏的属性
	 *
	 * @var array
	 */
	protected $hidden = [
		'send_time','resp_parameters'
	];

	/**
	 * 获取邮件类型
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function mail_type(){

		return $this->hasOne('App\Models\MailTypeModel','code','guide_type');

	}
}
