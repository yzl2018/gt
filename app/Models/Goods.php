<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Goods extends Model
{
	/**
	 * 指定表名
	 *
	 * @var string
	 */
    protected $table = "mall_goods";
	
	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
    protected $fillable = [
    	'goods_no','goods_name','goods_price','currency','goods_status'
	];
	
	/**
	 * 应该被调整为日期的属性
	 *
	 * @var array
	 */
    protected $dates = ['deleted_at'];
	
	/**
	 * 在数组中隐藏的属性
	 *
	 * @var array
	 */
    protected $hidden = [
    
	];
	
	/**
	 * 属性转换
	 *
	 * @var array
	 */
    protected $casts = [
    	'goods_status' => 'boolean'
	];
	
	/**
	 * 获取时修改商品名称
	 *
	 * @param $value
	 * @return string
	 */
    public function getGoodsNameAttribute($value){
    	return ucfirst($value);
	}
	
	/**
	 * 设置时修改商品名称
	 *
	 * @param $value
	 * @return string
	 */
	public function setGoodsNameAttribute($value){
    	return strtolower($value);
	}
	
	/**
	 * 获取关联到商品的图片
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
    public function image()
	{
		return $this->hasMany('App\Image');
	}
	
	/**
	 * 获取该商品所归属的商家
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function merchant()
	{
		return $this->belongsTo('App\Merchant');
	}
	
}
