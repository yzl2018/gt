<?php

namespace App\Http\Controllers\API\Normal;

use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\CommunicateWithB;
use App\Http\Toolkit\RESPONSE;
use App\Models\CashCardsModel;
use App\Models\CustomerInfoModel;
use App\Models\RefundRecordsModel;
use Faker\Provider\DateTime;
use Illuminate\Http\Request;

class RefundController extends ApiController
{
	use CommunicateWithB;

	/**
	 * 获取时间段内的所有退款记录的接口
	 *
	 * @uri	/api/mall/refund-list
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
	 * 			id:int							//退款记录id
	 * 			users_id:int					//所属用户id
	 * 			order_no:string					//支付订单编号
	 * 			card_no:string					//充值卡卡号
	 * 			card_value:numeric(15,2)		//充值卡面额
	 * 			currency_type_code:string		//货币类型	 enum('USD','CNY')
	 * 			reason:string					//退款理由
	 * 			dispose_person:string			//退款处理人
	 * 			mark:string						//退款处理意见
	 * 			status:int						//退款状态 (0:未处理,1:同意退款,2:拒绝退款,3退款完成)
	 * 			finish_time:DateTime			//退款完成时间 fmt('Y-m-d H:i:s')
	 *			created_at:DateTime				//生成时间 fmt('Y-m-d H:i:s')
	 * 			updated_at:DateTime				//更新时间 fmt('Y-m-d H:i:s')
	 * 		 },...
	 *    ]
	 * 	  message:string //响应信息
	 * }
	 *
	 */
	public function showRefundsList(Request $request){

		$this->getUser();
		$this->getSearchParams($request);

        $list = RefundRecordsModel::
            when($this->isCustomer(),function($query){
                return $query->where('users_id',$this->user['id']);
            })
            ->leftJoin('users','refund_records.users_id','=','users.id')
            ->where('refund_records.created_at','>=',$this->begin_date)
            ->where('refund_records.created_at','<=',$this->end_date)
            ->select(['refund_records.*','users.email'])
            ->when($this->is_paginate,function($query){
                return $query->paginate($this->page_items);
            },function($query){
                return $query->get();
            });

		return $this->app_response(RESPONSE::SUCCESS,'get refunds success',$list);

	}

	/**
	 * 客户申请退款
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function applyRefund(Request $request){

		$this->getUser();

		$card_no = $request->input('card_no');
		$reason = "";
		if($request->has('reason')){
			$reason = $request->input('reason');
		}

		//判断客户资料是否齐全
		$complete = CustomerInfoModel::checkInfoIntegrity($this->user);
		if($complete == false){
			return $this->app_response(RESPONSE::WARNING,$this->say('!083'));
		}

		//判断该充值卡是否存在，及查询状态
		$cash_card = CashCardsModel::where('card_no',$card_no)->first();
		if(empty($cash_card)){
			return $this->app_response(RESPONSE::WARNING,$this->say('!080'));
		}
		if($cash_card->use_status == -1){
			return $this->app_response(RESPONSE::WARNING,$this->say('!084'));
		}

		//向B系统提交退款申请
		$resp = $this->apply_refund_to_b($cash_card->order_no,$cash_card->created_at,$reason);
		if(is_null(json_decode($resp))){
			return $this->app_response(RESPONSE::UN_EXCEPTED,'B:Unexpected error.');
		}
		$response = json_decode($resp,true);
		if($response['code'] != $this->b_return_success_code){
			if($response['code'] < $this->b_error_code_critical){
				return $this->app_response($response['code'],$response['message']);
			}
			else{
				return $this->app_response(RESPONSE::UN_EXCEPTED,$response['message']);
			}
		}

		//记录客户的退款申请到本系统
		$date_time = date('Y-m-d H:i:s');
		$params = [
			'users_id'				=> $cash_card->users_id,
			'order_no'				=> $cash_card->order_no,
			'card_no'				=> $cash_card->card_no,
			'card_value'			=> $cash_card->card_value,
			'currency_type_code'	=> $cash_card->currency_type_code,
			'reason'				=> $reason,
			'created_at'			=> $date_time,
			'updated_at'			=> $date_time
		];
		$id = RefundRecordsModel::insertGetId($params);
		if($id > 0){
			return $this->app_response(RESPONSE::SUCCESS,$this->say('!085'));
		}

		return $this->app_response(RESPONSE::WARNING,$this->say('!086'));
	}

	/**
	 * 向B系统提交退款申请
	 *
	 * @param $order_id
	 * @param $create_time
	 * @param $reason
	 * @return string
	 */
	private function apply_refund_to_b($order_id,$create_time,$reason){

		$uri = $this->b_api['apply_refund'];

		$params = [
			'order_id'		=> $order_id,
			'description'	=> $reason,
			'info'			=> [
				'owner'	=> $this->user['email']
			],
			'begin_date'	=> date('Y-m-d H:i:s',strtotime($create_time." -5 day")),
			'end_date'		=> date('Y-m-d H:i:s',strtotime($create_time." +5 day"))
		];

		$headers = $this->create_user_auth($this->user['user_code'],$this->user['safety_code']);

		return $this->http_post_B($uri,$params,$headers);

	}

}
