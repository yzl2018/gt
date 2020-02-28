<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailTypesConfigModel extends Model
{
	/**
	 * 邮件类型表
	 *
	 * @var string
	 */
	protected $table = "mail_types_config";

	/**
	 * 在数组中隐藏的属性
	 *
	 * @var array
	 */
	protected $hidden = [

	];
}
