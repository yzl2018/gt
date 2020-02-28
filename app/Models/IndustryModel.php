<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndustryModel extends Model
{
	/**
	 * 行业信息表
	 *
	 * @var string
	 */
	protected $table = "industry";

	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
	protected $fillable = [
		'code','name_word_code','father_code','icon'
	];

	/**
	 * 在数组中隐藏的属性
	 *
	 * @var array
	 */
	protected $hidden = [
		'created_at','updated_at','deleted_at'
	];

	/**
	 * 获取行业名称
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function name(){

		return $this->hasMany('App\Models\SysLanguageWordsModel','word_code','name_word_code')
					//->where('language_type_code','=',config('system.mall.language'))
					;

	}
}
