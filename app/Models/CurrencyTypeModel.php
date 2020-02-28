<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyTypeModel extends Model
{
	/**
	 * 货币类型表
	 *
	 * @var string
	 */
	protected $table = "currency_type";
	
	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
	protected $fillable = [
		'code','name_word_code','symbol','b_number'
	];
}
