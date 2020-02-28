<?php

namespace App\Http\Controllers\API\Normal;

use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\CommunicateWithB;
use App\Http\Toolkit\RESPONSE;
use App\Models\CashCardsModel;
use App\Models\UsersModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CardsController extends ApiController
{
	use CommunicateWithB;

	/**
	 * 使用状态
	 *
	 * @var null
	 */
	private $use_status = null;

	/**
	 * 获取时间段内的所有充值卡记录的接口
	 *
	 * @uri	/api/mall/cards-list
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
	 * 			id:int							//购买记录id
	 * 			users_id:int					//所属用户id
	 *	 		purchase_records_id:int			//所属购买记录id
	 * 			order_no:string					//支付订单编号
	 * 			trade_no:string					//交易流水号
	 * 			card_no:string					//充值卡卡号
	 * 			card_value:numeric(15,2)		//充值卡面额
	 * 			currency_type_code:string		//货币类型	 enum('USD','CNY')
	 * 			use_status:int					//充值卡使状态 (0:未使用，1:已使用，-1:已退款)
	 *			sms_notice_status:int			//短信通知状态 (0:未通知，1:通知成功，-1:通知失败)
	 * 			merchant_code:string			//激活充值卡的商户号
	 * 			crm_order_no:string				//CRM 激活请求单号
	 * 			attempt_times:int				//尝试激活的次数
	 * 			activation_message:string		//激活响应信息
	 * 			success_time:DateTime			//激活成功时间 fmt('Y-m-d H:i:s')
	 *			created_at:DateTime				//生成时间 fmt('Y-m-d H:i:s')
	 * 			updated_at:DateTime				//更新时间 fmt('Y-m-d H:i:s')
	 * 		 },...
	 *    ]
	 * 	  message:string //响应信息
	 * }
	 *
	 */
	public function showCardsList(Request $request){

		$this->getUser();
		$this->getSearchParams($request);
        $use_status = null;
        if($request->has('use_status')){
            if(in_array(strval($request->input('use_status')),['0','1','-1'])){
                $this->use_status = intval($request->input('use_status'));
            }
        }

        if($this->isCustomer()){
            $list = CashCardsModel::where('cash_cards.users_id',$this->user['id'])
                ->when($this->use_status !== null,function($query){
                    return $query->where('cash_cards.use_status',$this->use_status);
                })
                ->leftJoin('users','cash_cards.users_id','=','users.id')
				->leftJoin('purchase_records','cash_cards.purchase_records_id','=','purchase_records.id')
                ->select(['cash_cards.*','users.email','purchase_records.goods_info_code','purchase_records.attach_info'])
                ->orderBy('cash_cards.created_at','desc')
                ->when($this->is_paginate,function($query){
                    return $query->paginate($this->page_items);
                },function($query){
                    return $query->get();
                });
        }
        else{
            $list = CashCardsModel::
                when($this->use_status !== null,function($query){
                    return $query->where('use_status',$this->use_status);
                })
                ->leftJoin('users','cash_cards.users_id','=','users.id')
                ->where('cash_cards.created_at','>=',$this->begin_date)
                ->where('cash_cards.created_at','<=',$this->end_date)
                ->select(['cash_cards.*','users.email'])
                ->orderBy('cash_cards.created_at','desc')
                ->when($this->is_paginate,function($query){
                    return $query->paginate($this->page_items);
                },function($query){
                    return $query->get();
                });
        }

		return $this->app_response(RESPONSE::SUCCESS,'get cards success',$list);

	}

	/**
	 * 查看充值卡密码
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function showPassword(Request $request){

		$this->getUser();

		$card = CashCardsModel::where('card_no',$request->input('card_no'))->first();

		if($card == null){
			return $this->message($this->say('!080'));
		}

		if($card->use_status != 0){
			return $this->message($this->say('!081'));
		}

		if($this->isCustomer() && $this->user['id'] != $card->users_id){
			return $this->message('Sorry,You have no such permission.');
		}

		if(empty($card->card_key)){

            $result = $this->post_data_from_B('voucher_seckey',[
                'voucher_id'	=> $request->input('card_no')
            ],$this->user['user_code'],$this->sys_security_code);

            if($result['code'] != RESPONSE::SUCCESS){
                return $this->message($result['message']);
            }

            return $this->app_response(RESPONSE::SUCCESS,$result['message'],$result['data']['security_code']);
        }

		return $this->app_response(RESPONSE::SUCCESS,$this->say('!082'),$card->card_key);

	}

    /**
     * 从B系统获取时间段内的所有充值卡记录的接口
     *
     * @param Request $request
     * @return mixed
     */
    public function showCardsListFromB(Request $request){

        $this->getUser();
        $this->getSearchParams($request);

        $result = $this->post_data_from_B('get_all_cards',[
            'begin_date'	=> $this->begin_date,
            'end_date'		=> $this->end_date
        ],$this->user['user_code'],$this->sys_security_code);

        if($result['code'] != RESPONSE::SUCCESS){
            return $this->message($result['message']);
        }

        return $this->app_response(RESPONSE::SUCCESS,'get cards success',$result['data']);

    }

    /**
     * 从B系统查看充值卡密码
     *
     * @param Request $request
     * @return mixed
     */
    public function showPasswordFromB(Request $request){

        $this->getUser();
        $this->getSearchParams($request);

        $result = $this->post_data_from_B('voucher_seckey',[
            'voucher_id'	=> $request->input('card_no')
        ],$this->user['user_code'],$this->sys_security_code);

        if($result['code'] != RESPONSE::SUCCESS){
            return $this->message($result['message']);
        }

        return $this->app_response(RESPONSE::SUCCESS,$this->say('!082'),$result['data']);

    }

    /**
     * 关闭或打开某张充值卡 是否允许客户在后台查看
     *
     * @param Request $request
     * @return mixed
     */
    public function setAllowViewOrNot(Request $request){

        $this->getUser();

        $count = UsersModel::where('id',$this->user['id'])->where('operate_password',$request->input('operate_password'))->count();
        if($count == 0){
            return $this->message($this->say('!034'));
        }

        $cards_id = $request->input('cards_id');
        $is_allow = $request->input('is_allow');
        if(empty($is_allow)){
            $is_allow = 0;
        }

        $allow_view = CashCardsModel::where('id',$cards_id)->value('allow_view');
        if($allow_view == $is_allow){
            return $this->message($this->say('!121'));
        }

        $params = [
            'allow_view'	=> $is_allow
        ];
        $isChanged = CashCardsModel::where('id',$cards_id)->update($params);

        if($isChanged > 0){
            return $this->app_response(RESPONSE::SUCCESS,$this->say('!030'));
        }

        return $this->app_response(RESPONSE::UPDATE_FAIL,$this->say('!029'));

    }

}
