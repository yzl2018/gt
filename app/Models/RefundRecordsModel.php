<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundRecordsModel extends Model
{
	/**
	 * 申请退款记录表
	 *
	 * @var string
	 */
	protected $table = "refund_records";
	
	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
	protected $fillable = [
		'cash_cards_id','apply_person','dispose_person','reason','mark','finish_time','status'
	];

	/**
	 * 在数组中隐藏的属性
	 *
	 * @var array
	 */
	protected $hidden = [
		'deleted_at'
	];
}
