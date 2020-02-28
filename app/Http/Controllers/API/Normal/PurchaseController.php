<?php
namespace App\Http\Controllers\API\Normal;

use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\API\Entity\OverdueRecords;
use App\Http\Toolkit\DomainLimitTradeAmount;
use App\Http\Toolkit\RESPONSE;
use App\Jobs\ProcessOverdueOrders;
use App\Models\CodeConfigModel;
use App\Models\GoodsInfoModel;
use App\Models\PurchaseRecordsModel;
use App\Models\UsersModel;
use App\Models\VirtualCardsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PurchaseController extends ApiController
{

    use DomainLimitTradeAmount;

    /**
     * 支付状态
     *
     * @var null
     */
    private $payment_status = null;

    /**
     * 是否是支付测试
     *
     * @var bool
     */
    private $is_test_pay = false;

	/**
	 * 获取时间段内的所有购买记录的接口-新
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function showPurchaseRecords(Request $request){

		$this->getUser();
		$this->getSearchParams($request);

		if(empty($this->user)){
			return $this->message('Invalid request');
		}

        if($request->has('payment_status')){
            if(in_array(strval($request->input('payment_status')),['0','1','-1'])){
                $this->payment_status = intval($request->input('payment_status'));
            }
        }

        if($this->isCustomer()){
			$list = PurchaseRecordsModel::
				where('purchase_records.users_id',$this->user['id'])
				->when($this->payment_status !== null,function($query){
					return $query->where('purchase_records.payment_status',$this->payment_status);
				})
				->orderBy('created_at','desc')
				->when($this->is_paginate,function($query){
					return $query->paginate($this->page_items);
				},function($query){
					return $query->get();
				});
		}
        else{
			$list = PurchaseRecordsModel::
			where('created_at','>=',$this->begin_date)
				->where('created_at','<=',$this->end_date)
				->when($this->isCustomer(),function($query){
					return $query->where('purchase_records.users_id',$this->user['id']);
				})
				->when($this->payment_status !== null,function($query){
					return $query->where('purchase_records.payment_status',$this->payment_status);
				})
				->orderBy('created_at','desc')
				->when($this->is_paginate,function($query){
					return $query->paginate($this->page_items);
				},function($query){
					return $query->get();
				});
		}


		return $this->app_response(RESPONSE::SUCCESS,'get purchase records success',$list);

	}

	/**
	 * 获取时间段内的所有购买记录的接口-旧
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function showPurchaseRecords0(Request $request){

		$this->getUser();
		$this->getSearchParams($request);
		if($request->has('payment_status')){
			if(in_array(strval($request->input('payment_status')),['0','1','-1'])){
				$this->payment_status = intval($request->input('payment_status'));
			}
		}

		$this->begin_date = date('Y-m-d H:i:s',strtotime(date('YmdHis')." -10 day"));

		$list = PurchaseRecordsModel::
		with('goods.name','cards.name')
			->when($this->isCustomer(),function($query){
				return $query->where('purchase_records.users_id',$this->user['id']);
			})
			->when($this->payment_status !== null,function($query){
				return $query->where('purchase_records.payment_status',$this->payment_status);
			})
			->leftJoin('users','purchase_records.users_id','=','users.id')
			->leftJoin('cash_cards','purchase_records.card_no','=','cash_cards.card_no')
			->where('purchase_records.created_at','>=',$this->begin_date)
			->where('purchase_records.created_at','<=',$this->end_date)
			->orderBy('purchase_records.created_at','desc')
			->select(['purchase_records.*','users.email','cash_cards.use_status'])
			->when($this->is_paginate,function($query){
				return $query->paginate($this->page_items);
			},function($query){
				return $query->get();
			});

		foreach ($list as $key =>$purchase){
			if(!empty($purchase->goods)){
				$list[$key]->goods->name_word = $this->transformWords($purchase->goods->name);
				unset($list[$key]->goods->name);
			}
			if(!empty($purchase->cards)){
				$list[$key]->cards->name_word = $this->transformWords($purchase->cards->name);
				unset($list[$key]->cards->name);
			}
		}

		return $this->app_response(RESPONSE::SUCCESS,'get purchase records success',$list);

	}

    /**
     * 获取单个订单购买记录
     *
     * @param Request $request
     * @return mixed
     */
    public function getOnePurchaseRecord(Request $request){

        $this->getUser();
        $purchase_id = $request->input('purchase_id');

        $purchase = PurchaseRecordsModel::where('id',$purchase_id)->first();
        if(empty($purchase)){
			return $this->app_response(RESPONSE::NOT_EXIST,'the purchase record is not exists.');
		}

		if($this->isCustomer() && $this->user['id'] != $purchase->users_id){
        	return $this->message('Sorry,You have no such permission.');
		}

        return $this->app_response(RESPONSE::SUCCESS,'get the purchase record success',$purchase);

    }

    /**
     * 客户购买商品
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function purchaseGoods(Request $request){

        $this->getUser();

		$allow_users = config('system.payment_test.allow_users');
		if(in_array($this->user['email'],$allow_users)){
			$this->is_test_pay = true;
		}

        $attach_info = [
        	'email'	=> $this->user['email']
		];
        $params = $request->input();
        $type = $request->input('goods_type');
        unset($params['goods_type']);

        if(strpos($params['goods_info_code'],'G') === 0 && $type != 0){
            $type = 0;
        }

        if(strpos($params['goods_info_code'],'C') === 0 && $type != 1){
            $type =1;
        }

        if($type == 0){
            $goods_info = GoodsInfoModel::where('code',$params['goods_info_code'])->first();
            if(empty($goods_info)){
                return $this->message('!119');
            }
            $params['store_info_code'] = $goods_info->store_info_code;
            $attach_info['original_price'] = $goods_info->original_price;
        }
        else{
            $goods_info = VirtualCardsModel::where('code',$params['goods_info_code'])->first();
            if(empty($goods_info)){
                return $this->message('!119');
            }
            $params['store_info_code'] = config('system.mall.code');
            $attach_info['original_price'] = $goods_info->price;
        }

        $attach_info['name_word'] = $this->getWordsByCode($goods_info->name_word_code);
        $attach_info['litpic'] = $goods_info->litpic;
        $params['attach_info'] = json_encode($attach_info);

        $params['code'] = CodeConfigModel::getUniqueCode('purchase');

        $params['users_id'] = $this->user['id'];
        $params['users_code'] = $this->user['code'];

        if($this->is_test_pay == false){
            if($params['buy_number'] < $goods_info->buy_limit){
                return $this->message($this->say('!010').":".$goods_info->buy_limit);
            }

            if($params['buy_number'] > $goods_info->buy_stop){
                return $this->message($this->say('!011').":".$goods_info->buy_stop);
            }
        }

        $params['price'] = $goods_info->price;
        $params['total_amount'] = intval($params['price'] * $params['buy_number']);
        $params['currency_type_code'] = $goods_info->currency_type_code;
        $params['created_at'] = $params['updated_at'] = date('Y-m-d H:i:s');
        $params['order_time_out'] = date('Y-m-d H:i:s',time() + $this->order_expires_seconds);

		$currency_access = $this->checkCurrencyForAccess($params['currency_type_code']);
		if($currency_access['denied']){
			return $this->message($currency_access['message']);
		}

        if($this->is_test_pay == false){
            $check_res = $this->checkTradeTotalMoney($request->getHttpHost(),$params['total_amount']);
            if($check_res['fail']){
                return $this->app_response(RESPONSE::BUY_FAIL,$check_res['message']);
            }
        }

        $id = PurchaseRecordsModel::insertGetId($params);
        if($id > 0){

            try{
                $pur_records = new OverdueRecords('purchase_records','code',$params['code'],'payment_status');
                ProcessOverdueOrders::dispatch($pur_records)->delay(now()->addMinutes($this->overdue_delay_minutes));
            }
            catch(\Exception $e){
                Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
                    'Failed to dispatch queue task:' => $pur_records,
                    'error message'	=> $e->getMessage()
                ]);
            }

            $params['purchase_id'] = $id;
            return $this->app_response(RESPONSE::SUCCESS,$this->say('!012'),$params);
        }

        return $this->app_response(RESPONSE::WARNING,$this->say('!013'));

    }

}
