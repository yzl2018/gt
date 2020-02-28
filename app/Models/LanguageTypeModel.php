<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LanguageTypeModel extends Model
{
	/**
	 * 语言类型表
	 *
	 * @var string
	 */
	protected $table = "language_type";
	
	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
	protected $fillable = [
		'code','name'
	];
}
