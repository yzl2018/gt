<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class PurchaseRecordsModel extends Model
{
	/**
	 * 用户购买记录表
	 *
	 * @var string
	 */
	protected $table = "purchase_records";

	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
	protected $fillable = [
        'code','users_id','store_info_id','goods_info_id','number','total_amount','currency_type_code','payment_times','payment_status','payment_orders_id'
	];

	/**
	 * 在数组中隐藏的属性
	 *
	 * @var array
	 */
	protected $hidden = [
		'deleted_at'
	];

    /**
     * 获取所购买的商品信息
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
	public function goods(){

        return $this->hasOne('App\Models\GoodsInfoModel','code','goods_info_code');

    }

    /**
     * 获取所购买的充值卡信息
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cards(){

        return $this->hasOne('App\Models\VirtualCardsModel','code','goods_info_code');

    }

}
