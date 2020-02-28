<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTypeModel extends Model
{
	/**
	 * 用户类型表
	 *
	 * @var string
	 */
	protected $table = "user_type";
	
	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
	protected $fillable = [
		'code','name_word_code'
	];
}
