<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoodsDetailsInfoModel extends Model
{
	/**
	 * 商品详情信息表
	 *
	 * @var string
	 */
	protected $table = "goods_details_info";

	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
	protected $fillable = [
		'goods_info_id','title_word_code','image','information_word_code','sort_number'
	];

    /**
     * 获取详情标题
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function title(){

        return $this->hasMany('App\Models\SysLanguageWordsModel','word_code','title_word_code')
            //->where('language_type_code','=',config('system.mall.language'))
            ;

    }

    /**
     * 获取详情信息
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function information(){

        return $this->hasMany('App\Models\SysLanguageWordsModel','word_code','information_word_code')
            //->where('language_type_code','=',config('system.mall.language'))
            ;

    }

    /**
     * 更改图片访问地址
     *
     * @param $value
     * @return string
     */
    public function getImageAttribute($value){

		if(empty($value)){
            return $value;
        }

        return config('system.upload.file_host').$value;

    }
}
