<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SysLanguageWordsModel extends Model
{
	/**
	 * 系统语言文字记录表
	 *
	 * @var string
	 */
	protected $table = "sys_language_words";

	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
	protected $fillable = [
		'word_code','language_type_code','word'
	];

	/**
	 * 在数组中隐藏的属性
	 *
	 * @var array
	 */
	protected $hidden = [
		'id','word_code','created_at','updated_at','deleted_at'
	];
}
