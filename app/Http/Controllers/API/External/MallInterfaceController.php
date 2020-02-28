<?php
namespace App\Http\Controllers\API\External;


use App\Http\Toolkit\AppResponse;
use App\Http\Toolkit\RESPONSE;
use App\Models\BNotifyLogModel;
use App\Models\CashCardsModel;
use App\Models\CrmRequestLogModel;
use App\Models\LoginLogModel;
use App\Models\PaymentOrdersModel;
use App\Models\PurchaseRecordsModel;
use App\Models\RefundRecordsModel;
use App\Models\RegisterActivationLogModel;
use App\Models\RequestGuideMailRecordsModel;
use App\Models\SendMailLogModel;
use App\Models\UserOperateLogModel;
use Illuminate\Http\Request;
use App\Models\GoodsInfoModel;
use Illuminate\Support\Facades\DB;

class MallInterfaceController
{

	use AppResponse;

	/**
	 * 分页项数
	 *
	 * @var int
	 */
	private $page_items = 50;

	/**
	 * 验证日期的正则表达式
	 *
	 * @var string
	 */
	private $date_preg = '/^([12]\d\d\d)-(0?[1-9]|1[0-2])-(0?[1-9]|[12]\d|3[0-1]) ([0-1]\d|2[0-4]):([0-5]\d)(:[0-5]\d)?$/';

	/**
	 * 验证正整数的正则表达式
	 *
	 * @var string
	 */
	private $int_preg = '/^[1-9][0-9]*$/';

	/**
	 * 查询起始时间
	 *
	 * @var
	 */
	private $begin_date;

	/**
	 * 查询结束时间
	 *
	 * @var
	 */
	private $end_date;

	/**
	 * 是否分页 默认分页
	 *
	 * @var bool
	 */
	private $is_paginate = false;

	/**
	 * MallInterfaceController constructor.
	 */
	public function __construct()
	{

		$date_time = date('YmdHis');
		$days = config('system.mall.date_range');
		$days = intval($days) + 1;
		$this->begin_date = date('Y-m-d H:i:s',strtotime($date_time." -".$days." day"));
		$this->end_date = date('Y-m-d H:i:s',strtotime($date_time." +1 day"));

	}

	/**
	 * 获取查询时间范围
	 *
	 * @param Request $request
	 */
	private function getSearchParams(Request $request){

		if($request->has('is_paginate')){
			$is_paginate = $request->input('is_paginate');
			if($is_paginate == true){
				$this->is_paginate = true;
			}
		}

		if($request->has('page_items')){
			$page_items = $request->input('page_items');
			if(preg_match($this->int_preg,$page_items)){
				$this->page_items = intval($page_items);
			}
		}

		if($request->has('begin_date')){
			$begin_date = $request->input('begin_date');
			if(!empty($begin_date) && preg_match($this->date_preg,$begin_date)){
				$this->begin_date = $begin_date;
			}
		}

		if($request->has('end_date')){
			$end_date = $request->input('end_date');
			if(!empty($end_date) && preg_match($this->date_preg,$end_date)){
				$this->end_date = $end_date;
			}
		}

	}

	/**
	 * 获取商城客户信息的接口
	 *
	 * @uri	/api/mall/customers-list
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
	 * 			login_fail_times:int	//登陆失败次数(登陆失败达5次，即被锁定)
	 *		    active_status:int		//激活状态(0:未激活客户,1:已激活客户)
	 * 			complete_percent:float	//资料完整度( 0~1 之间)
	 *			created_at:DateTime		//注册时间 fmt('Y-m-d H:i:s')
	 * 			updated_at:DateTime		//更新时间 fmt('Y-m-d H:i:s')
	 * 		 },...
	 *    ]
	 * 	  message:string //响应信息
	 * }
	 *
	 */
	public function getCustomers(Request $request){

		$this->getSearchParams($request);

		$list = DB::table('users')
			->where('users.user_type_code',config('system.user.customer.code'))
			->leftJoin('customer_info','users.id','=','customer_info.users_id')
			->select(['customer_info.users_id','users.code','users.name','users.email','users.phone','users.login_fail_times','users.active_status',
				'users.language_type_code','customer_info.complete_percent','users.created_at','users.updated_at'])
			->when($this->is_paginate,function($query){
				return $query->paginate($this->page_items);
			},function($query){
				return $query->get();
			});

		return $this->app_response(RESPONSE::SUCCESS,'get customers list success',$list);

	}

	/**
	 * 获取所有商品信息的接口
	 *
	 * @uri	/api/mall/goods-list
	 *
	 * @param Request $request
	 * request data [json]:
	 * {
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
	 * 			id:int							//商品id
	 * 			code:string						//商品编号
	 *	 		store_info_code:string			//所属商家编号
	 * 			price:numeric(15,2)				//商品价格
	 * 			currency_type_code:string		//货币类型	 enum('USD','CNY')
	 * 			litpic:string					//商品缩略图地址  (相对地址)
	 * 			buy_limit:int					//购买数量下限
	 * 			buy_stop:int					//购买数量上限
	 *			created_at:DateTime				//生成时间 fmt('Y-m-d H:i:s')
	 * 			updated_at:DateTime				//更新时间 fmt('Y-m-d H:i:s')
	 * 			name:{ word:string }			//商品名称-文字
	 * 			features:{ word:string }		//商品特点-文字
	 * 			introduce:{ word:string }		//商品简介-文字
	 * 		 },...
	 *    ]
	 * 	  message:string //响应信息
	 * }
	 *
	 */
	public function getGoods(Request $request){

		$this->getSearchParams($request);

		$list = GoodsInfoModel::with('name','features','introduce')
			->orderBy('created_at','desc')
			->when($this->is_paginate,function($query){
				return $query->paginate($this->page_items);
			},function($query){
				return $query->get();
			});

		foreach ($list as $key => $goods){
			$name = $goods->name;
			$name_obj = [];
			foreach ($name as $index => $value){
				$name_obj[$value->language_type_code] = $value->word;
			}
			unset($list[$key]['name']);
			$list[$key]['name'] = $name_obj;

			$features = $goods->features;
			$features_obj = [];
			foreach ($features as $index => $value){
				$features_obj[$value->language_type_code] = $value->word;
			}
			unset($list[$key]['features']);
			$list[$key]['features'] = $features_obj;

			$introduce = $goods->introduce;
			$introduce_obj = [];
			foreach ($introduce as $index => $value){
				$introduce_obj[$value->language_type_code] = $value->word;
			}
			unset($list[$key]['introduce']);
			$list[$key]['introduce'] = $introduce_obj;
		}

		return $this->app_response(RESPONSE::SUCCESS,'get goods list success',$list);

	}

	/**
	 * 获取时间段内的所有购买记录的接口
	 *
	 * @uri	/api/mall/purchase-list
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
	 *	 		users_code:string				//所属用户编号
	 * 			store_info_code:string			//商家编号
	 * 			goods_info_code:string			//购买的商品编号
	 * 			price:numeric(15,2)				//商品价格
	 * 			buy_number:int					//购买数量
	 * 			total_amount:numeric(15,2)		//购买总金额
	 * 			currency_type_code:string		//货币类型	 enum('USD','CNY')
	 * 			payment_times:int				//支付次数
	 * 			payment_status:int				//支付状态
	 * 			success_time:DateTime			//支付成功时间 fmt('Y-m-d H:i:s')
	 * 			order_no:string					//成功支付的订单号
	 *			created_at:DateTime				//生成时间 fmt('Y-m-d H:i:s')
	 * 			updated_at:DateTime				//更新时间 fmt('Y-m-d H:i:s')
	 * 		 },...
	 *    ]
	 * 	  message:string //响应信息
	 * }
	 *
	 */
	public function getPurchaseRecords(Request $request){

		$this->getSearchParams($request);

		$list = PurchaseRecordsModel::where('created_at','>=',$this->begin_date)
			->where('created_at','<=',$this->end_date)
			->when($this->is_paginate,function($query){
				return $query->paginate($this->page_items);
			},function($query){
				return $query->get();
			});

		return $this->app_response(RESPONSE::SUCCESS,'get purchase records success',$list);

	}

	/**
	 * get payment order records
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function getPaymentOrders(Request $request){

		$this->getSearchParams($request);

		$list = PaymentOrdersModel::where('payment_orders.created_at','>=',$this->begin_date)
			->where('payment_orders.created_at','<=',$this->end_date)
			->leftJoin('users','payment_orders.users_id','=','users.id')
			->select(['payment_orders.*','users.email'])
			->orderBy('payment_orders.created_at','desc')
			->when($this->is_paginate,function($query){
				return $query->paginate($this->page_items);
			},function($query){
				return $query->get();
			});

		return $this->app_response(RESPONSE::SUCCESS,'get payment orders success',$list);

	}

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
	public function getCashCards(Request $request){

		$this->getSearchParams($request);

		$list = CashCardsModel::where('created_at','>=',$this->begin_date)
			->where('created_at','<=',$this->end_date)
			->when($this->is_paginate,function($query){
				return $query->paginate($this->page_items);
			},function($query){
				return $query->get();
			});

		return $this->app_response(RESPONSE::SUCCESS,'get cards list success',$list);

	}

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
	public function getRefundRecords(Request $request){

		$this->getSearchParams($request);

		$list = RefundRecordsModel::where('created_at','>=',$this->begin_date)
			->where('created_at','<=',$this->end_date)
			->when($this->is_paginate,function($query){
				return $query->paginate($this->page_items);
			},function($query){
				return $query->get();
			});

		return $this->app_response(RESPONSE::SUCCESS,'get refund list success',$list);

	}

	/**
	 * get register logs
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function getRegisterLogs(Request $request){

		$this->getSearchParams($request);

		$list = RegisterActivationLogModel::where('created_at', '>=', $this->begin_date)
			->where('created_at', '<=', $this->end_date)
			->orderBy('created_at', 'desc')
			->when($this->is_paginate, function ($query) {
				return $query->paginate($this->page_items);
			}, function ($query) {
				return $query->get();
			});

		return $this->app_response(RESPONSE::SUCCESS, 'get register log success', $list);

	}

	/**
	 * get login logs
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function getLoginLogs(Request $request){

		$this->getSearchParams($request);

		$list = LoginLogModel::where('login_at', '>=', $this->begin_date)
			->where('login_at', '<=', $this->end_date)
			->orderBy('login_at', 'desc')
			->when($this->is_paginate, function ($query) {
				return $query->paginate($this->page_items);
			}, function ($query) {
				return $query->get();
			});

		return $this->app_response(RESPONSE::SUCCESS, 'get login log success', $list);

	}

	/**
	 * get user operate logs
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function getOperateLogs(Request $request){

		$this->getSearchParams($request);

		$list = UserOperateLogModel::with('operate_type')
			->where('user_operate_log.time', '>=', $this->begin_date)
			->where('user_operate_log.time', '<=', $this->end_date)
			->orderBy('user_operate_log.time', 'desc')
			->leftJoin('users', 'user_operate_log.users_id', '=', 'users.id')
			->select(['user_operate_log.*', 'users.email'])
			->when($this->is_paginate, function ($query) {
				return $query->paginate($this->page_items);
			}, function ($query) {
				return $query->get();
			});

		return $this->app_response(RESPONSE::SUCCESS, 'get operate log success', $list);

	}

	/**
	 * get CRM activation logs
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function getActivationLogs(Request $request){

		$this->getSearchParams($request);

		$list = CrmRequestLogModel::where('request_time', '>=', $this->begin_date)
			->where('request_time', '<=', $this->end_date)
			->orderBy('request_time', 'desc')
			->when($this->is_paginate, function ($query) {
				return $query->paginate($this->page_items);
			}, function ($query) {
				return $query->get();
			});

		return $this->app_response(RESPONSE::SUCCESS, 'get crm request log success', $list);

	}

	/**
	 * get upstream notify logs
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function getUpstreamNotifyLogs(Request $request){

		$this->getSearchParams($request);

		$list = BNotifyLogModel::where('time', '>=', $this->begin_date)
			->where('time', '<=', $this->end_date)
			->orderBy('time', 'desc')
			->when($this->is_paginate, function ($query) {
				return $query->paginate($this->page_items);
			}, function ($query) {
				return $query->get();
			});

		return $this->app_response(RESPONSE::SUCCESS, 'get b notify log success', $list);

	}

	/**
	 * get merchant guide mail logs
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function getMerchantGuideMailLogs(Request $request){

		$this->getSearchParams($request);

		$list = RequestGuideMailRecordsModel::with('mail_type')
			->where('request_time','>=',$this->begin_date)
			->where('request_time','<=',$this->end_date)
			->orderBy('request_time','desc')
			->when($this->is_paginate,function($query){
				return $query->paginate($this->page_items);
			},function($query){
				return $query->get();
			});

		return $this->app_response(RESPONSE::SUCCESS,'get merchant guide mail log success',$list);

	}

	/**
	 * get the send mail logs
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function getSendMailLogs(Request $request){

		$this->getSearchParams($request);

		$list = SendMailLogModel::with('mail_type')
			->where('created_at', '>=', $this->begin_date)
			->where('created_at', '<=', $this->end_date)
			->orderBy('created_at', 'desc')
			->when($this->is_paginate, function ($query) {
				return $query->paginate($this->page_items);
			}, function ($query) {
				return $query->get();
			});

		return $this->app_response(RESPONSE::SUCCESS, 'get send mail log success', $list);

	}

}
