<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class CustomerInfoModel extends Model
{
	/**
	 * 客户信息表
	 *
	 * @var string
	 */
	protected $table = "customer_info";

	/**
	 * 可以被批量赋值的属性.
	 *
	 * @var array
	 */
	protected $fillable = [
		'users_id','profile_photo','bank_name','bank_account','card_holder','id_card_number','card_photo','id_card_front','id_card_behind','complete_percent'
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
	 * 客户信息必填资料
	 *
	 * @var array
	 */
	private static $required_info = [
		'name'				=> NULL,
		'email'				=> '',
		'phone'				=> '',
		'bank_name'			=> '',
		'bank_account'		=> '',
		'card_holder'		=> '',
		'id_card_number'	=> '',
		'card_photo'		=> '',
		'id_card_front'		=> '',
		'id_card_behind'	=> ''
	];

	/**
	 * 检查客户信息的完整度
	 *
	 * @param $user
	 * @return bool
	 */
	public static function checkInfoIntegrity($user){

		if(!isset($user['id'])){
			Log::error(__FUNCTION__,'The user has no attribute id');
			return false;
		}

		$cus_info = self::where('users_id',$user['id'])->first();
		if($cus_info == null){
			return false;
		}

		$required = [];
		foreach (self::$required_info as $key => $value){
			if($value !== NULL){
				array_push($required,$key);
			}
		}

		self::$required_info['name'] = $user['name'];
		self::$required_info['email'] = $user['email'];
		self::$required_info['phone'] = $user['phone'];
		self::$required_info['bank_name'] = $cus_info->bank_name;
		self::$required_info['bank_account'] = $cus_info->bank_account;
		self::$required_info['card_holder'] = $cus_info->card_holder;
		self::$required_info['id_card_number'] = $cus_info->id_card_number;
		self::$required_info['card_photo'] = $cus_info->card_photo;
		self::$required_info['id_card_front'] = $cus_info->id_card_front;
		self::$required_info['id_card_behind'] = $cus_info->id_card_behind;

		$complete = true;
		$total = 0;$points = 0;
		foreach (self::$required_info as $key => $value){
			$total++;
			if(!empty($value)){
				$points++;
			}
			if($complete && in_array($key,$required) && empty($value)){
				$complete = false;
			}
		}
		$complete_percent = sprintf('%.2f',$points/$total);

		if($complete_percent != $cus_info->complete_percent){
			try{
				self::where('users_id',$user['id'])->update(['complete_percent'=>$complete_percent]);
			}
			catch(\Exception $e){
				Log::error(__FUNCTION__,[
					'msg'	=> $e->getMessage(),
					'trace'	=> $e->getTrace()
				]);
			}
		}

		return $complete;

	}
}
