<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashCardsModel extends Model
{
	/**
	 * 充值卡记录表
	 *
	 * @var string
	 */
	protected $table = "cash_cards";

    /**
     * 可以被批量赋值的属性.
     *
     * @var array
     */
    protected $fillable = [
        'users_id','payment_orders_id','card_no','card_value','currency_type_code','use_status','email_notice_status','sms_notice_status','merchant_code','crm_order_no','attempt_times','activation_message','success_time'
    ];

    /**
     * 在数组中隐藏的属性
     *
     * @var array
     */
    protected $hidden = [
       'card_key','deleted_at'
    ];
}
