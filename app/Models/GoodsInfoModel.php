<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsInfoModel extends Model
{
	/**
	 * 商品信息表
	 *
	 * @var string
	 */
	protected $table = "goods_info";

	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
	protected $fillable = [
		'id','code','original_price','price','currency_type_code','litpic','buy_limit','buy_stop','tag_label'
	];

	/**
	 * 在数组中隐藏的属性
	 *
	 * @var array
	 */
	protected $hidden = [
		'name_word_code','features_word_code','introduce_word_code','deleted_at'
	];

	/**
	 * 获取商品名称
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function name(){

		return $this->hasMany('App\Models\SysLanguageWordsModel','word_code','name_word_code')
			//->where('language_type_code','=',config('system.mall.language'))
			;

	}

	/**
	 * 获取商品特点
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function features(){

		return $this->hasMany('App\Models\SysLanguageWordsModel','word_code','features_word_code')
			//->where('language_type_code','=',config('system.mall.language'))
			;

	}

	/**
	 * 获取商品简介
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function introduce(){

		return $this->hasMany('App\Models\SysLanguageWordsModel','word_code','introduce_word_code')
			//->where('language_type_code','=',config('system.mall.language'))
			;

	}

    /**
     * 获取所属商品的详情信息
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function details(){

        return $this->hasMany('App\Models\GoodsDetailsInfoModel','goods_info_code','code')
            ->with('title','information')
            ->orderBy('sort_number')
            ;

    }

    /**
     * 更改图片访问地址
     *
     * @param $value
     * @return string
     */
    public function getLitpicAttribute($value){

		if(empty($value)){
            return $value;
        }

        return config('system.upload.file_host').$value;

    }
}
