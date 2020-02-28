<?php
namespace App\Http\Controllers\API\Normal;

use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\RESPONSE;
use App\Models\CustomerInfoModel;
use App\Models\UsersModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends ApiController
{

	/**
	 * 响应数据的键名数组
	 *
	 * @var array
	 */
	protected $response_data_keys = [
		'customer_info.users_id','customer_info.profile_photo','users.name','users.email','users.phone',
		'customer_info.bank_name','customer_info.bank_account','customer_info.card_holder','.customer_info.id_card_number',
		'customer_info.card_photo','customer_info.id_card_front','customer_info.id_card_behind','customer_info.complete_percent',
		'users.created_at','users.updated_at'
	];

	/**
	 * 获取商城客户信息的接口
	 *
	 * @uri	/api/customer/all-list
	 *
	 * @param Request $request
	 * request data [json]:
	 * {
	 * 		begin_date:DateTime //起始时间 fmt('Y-m-d H:i:s')	[可不传]
	 * 		end_date:DateTime	//结束时间 fmt('Y-m-d H:i:s')	[可不传]
	 * 		is_paginate:bool	//是否分页 默认为true			[可不传]
	 * 		page_items:int		//每页项数 必须为正整数			[可不传]
	 * }
	 *
	 * @return \Illuminate\Http\JsonResponse
	 * response data [json]:
	 * {
	 * 	  code:string //响应状态码 只有 0xFFF 表示成功 其它均为失败
	 *    data:[ //响应数据
	 * 		 {
	 * 			users_id:int			//用户编号
	 * 			name:string				//姓名
	 *	 		email:string			//邮箱
	 *			phone:string			//电话
	 * 			bank_name:string		//银行名称
	 *		    bank_account:string		//银行账户
	 * 			card_holder:string		//银行卡持有者
	 * 			id_card_number:string	//身份证号码
	 * 			card_photo:string		//银行卡图片
	 * 			id_card_front:string	//身份证正面
	 * 			id_card_behind:string	//身份证反面
	 * 			complete_percent:float	//资料完整度( 0~1 之间)
	 *			created_at:DateTime		//注册时间 fmt('Y-m-d H:i:s')
	 * 			updated_at:DateTime		//更新时间 fmt('Y-m-d H:i:s')
	 * 		 },...
	 *    ]
	 * 	  message:string //响应信息
	 * }
	 *
	 */
	public function showCustomersList(Request $request){

		$this->getUser();
		$this->getSearchParams($request);

		$list = DB::table('users')
			->where('users.user_type_code',config('system.user.customer.code'))
			->where('users.created_at','>=',$this->begin_date)
			->where('users.created_at','<=',$this->end_date)
			->leftJoin('customer_info','users.id','=','customer_info.users_id')
			->select($this->response_data_keys)
			->when($this->is_paginate,function($query){
				return $query->paginate($this->page_items);
			},function($query){
				return $query->get();
			});

		return $this->app_response(RESPONSE::SUCCESS,'get customers success',$list);

	}

	/**
	 * 获取某个客户的信息
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function getCustomerInfo(Request $request){

		$this->getUser();

		$cus_info = CustomerInfoModel::where('users_id',$this->user['id'])->first();
		if($cus_info == null){
			$cus_info = [
				'name'				=> $this->user['name'],
				'email'				=> $this->user['email'],
				'phone'				=> $this->user['phone'],
                'profile_photo'		=> null,
				'bank_name'			=> null,
				'bank_account'		=> null,
				'card_holder'		=> null,
				'id_card_number'	=> null,
				'card_photo'		=> null,
				'id_card_front'		=> null,
				'id_card_behind'	=> null,
				'complete_percent'	=> 0.3,
			];
			if($cus_info['name'] == null){
				$cus_info['complete_percent'] = 0.2;
			}
		}
		else{
			$cus_info->name  = $this->user['name'];
			$cus_info->email = $this->user['email'];
			$cus_info->phone = $this->user['phone'];
		}

		return $this->app_response(RESPONSE::SUCCESS,'get customer info success',$cus_info);

	}

	/**
	 * 更新客户信息
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function updateCustomerInfo(Request $request){

		$this->getUser();

		$params = $request->input();
		if(count($params) == 0){
			return $this->message($this->say('!042'));
		}

		if($request->has('email')){
            return $this->message($this->say('!115'));
        }

        if($request->has('phone')){
            return $this->message($this->say('!116'));
        }

		if($request->has('name')){
			UsersModel::where('id',$this->user['id'])->update(['name'=>$request->input('name')]);
			unset($params['name']);
			if(count($params) == 0){
				return $this->app_response(RESPONSE::SUCCESS,$this->say('!030'));
			}
		}

		$count = CustomerInfoModel::where('users_id',$this->user['id'])->count();
		if($count == 0){
			$params['users_id'] = $this->user['id'];
			$id = CustomerInfoModel::insertGetId($params);

			if($id > 0){
				return $this->app_response(RESPONSE::SUCCESS,$this->say('!030'));
			}

			return $this->app_response(RESPONSE::SUCCESS,$this->say('!029'));
		}
		else{
			$isUpdate = CustomerInfoModel::where('users_id',$this->user['id'])->update($params);

			if($isUpdate > 0){
				return $this->app_response(RESPONSE::SUCCESS,$this->say('!030'));
			}

			return $this->app_response(RESPONSE::SUCCESS,$this->say('!029'));
		}

	}

}
