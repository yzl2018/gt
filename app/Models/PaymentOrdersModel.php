<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentOrdersModel extends Model
{
	/**
	 * 支付订单记录表
	 *
	 * @var string
	 */
	protected $table = "payment_orders";
	
	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
	protected $fillable = [
		'users_id','purchase_records_id','order_no','order_time','order_time_out','order_amount','currency_type_code','trade_no','trade_status','success_time'
	];

	/**
	 * 在数组中隐藏的属性
	 *
	 * @var array
	 */
	protected $hidden = [
		'created_at','updated_at','deleted_at'
	];
}
