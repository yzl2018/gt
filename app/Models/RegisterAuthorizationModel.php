<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegisterAuthorizationModel extends Model
{
	
	/**
	 * 注册授权表
	 *
	 * @var string
	 */
	protected $table = "register_authorization";
	
	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
	protected $fillable = [
		'code','status','expires_time'
	];

}
