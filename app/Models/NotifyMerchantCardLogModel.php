<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotifyMerchantCardLogModel extends Model
{
    use SoftDeletes;

    /**
     * 通知商户充值卡激活信息日志表
     *
     * @var string
     */
    protected $table = "notify_merchant_card_log";

    /**
     * 可以被批量赋值的属性.
     *
     * @var array
     */
    protected $fillable = [
        'sys_ip','ip_address','api','method','receive_params','receive_time','response_b','remarks','notify_url','notify_parameters','sign_data','notify_time','crm_response'
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
