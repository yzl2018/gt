<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantSecurityModel extends Model
{
	/**
	 * 商户密钥配置表
	 *
	 * @var string
	 */
	protected $table = "merchants_security";
	
	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
	protected $fillable = [
		'merchant_code','user_code','security_key','security_salt'
	];
	
	protected $hidden = [
		'deleted_at'
	];
}
