<?php
namespace App\Http\Controllers\API\Normal;

use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\RESPONSE;
use App\Models\CashCardsModel;
use App\Models\PaymentOrdersModel;
use App\Models\PurchaseRecordsModel;
use App\Models\UsersModel;
use Illuminate\Http\Request;

class SearchController extends ApiController
{

    /**
     * 搜索类型 （0：时间段搜索，1：全字段模糊搜索，2：单个字段模糊搜索，3：单个字段精准搜索）
     *
     * @var int
     */
    private $search_type = 0;

    /**
     * 是否模糊搜索
     *
     * @var bool
     */
    private $vague = true;

    /**
     * 搜索字段
     *
     * @var null
     */
    private $field = NULL;

    /**
     * 搜索的值
     *
     * @var null
     */
    private $value = NULL;

    /**
     * 模糊搜索值
     *
     * @var null
     */
    private $vague_value = NULL;

    /**
     * 请求参数是否有误
     *
     * @var bool
     */
    private $request_has_error = false;

    /**
     * 请求的错误信息
     *
     * @var string
     */
    private $request_error_message = "";

    /**
     * 搜索起始时间
     *
     * @var null
     */
    protected $begin_date = NULL;

    /**
     * 搜索结束时间
     *
     * @var null
     */
    protected $end_date = NULL;

    /**
     * 允许搜索的字段数组
     *
     * @var null
     */
    private $allow_search_fields = NULL;

    /**
     * 获取搜索查询字段
     *
     * @param Request $request
     */
    private function getRequestParams(Request $request){

        if(!$request->has('value')){
            if(!$request->has('begin_date') && !$request->has('end_date')){
                $this->request_has_error = true;
                $this->request_error_message = "Missing parameters";
            }
        }

        else if($request->input('value') === NULL || $request->input('value') === ""){
            $this->request_has_error = true;
            $this->request_error_message = "The search value can not be empty";
        }

        else if(is_array($request->input('value'))){
            $this->request_has_error = true;
            $this->request_error_message = "The search value format error";
        }

        else {
            $this->value = $request->input('value');
            $this->vague_value = "%".$this->value."%";
            $this->search_type = 1;

            if($request->has('field')){
                $this->field = $request->input('field');
                if(empty($this->field)){
                    $this->request_has_error = true;
                    $this->request_error_message = "The search field can not be empty";
                }
                else if(!is_string($this->field)){
                    $this->request_has_error = true;
                    $this->request_error_message = "The search field format error";
                }
                else{
                    $this->search_type = 2;
                    if(empty($this->allow_search_fields)){
                        $this->request_has_error = true;
                        $this->request_error_message = "The search fields are not be defined";
                    }
                    else if(!in_array($this->field,$this->allow_search_fields)){
                        $this->request_has_error = true;
                        $this->request_error_message = "Invalid search field,access denied.";
                    }
                    else{
                        if($request->has('vague')){
                            if($request->input('vague') == false || $request->input('vague') == 0){
                                $this->vague = false;
                                $this->search_type = 3;
                            }
                        }
                    }
                }
            }

        }

        if($this->request_has_error === false && $request->has('begin_date')){
            $begin_date = $request->input('begin_date');
            if(!empty($begin_date) && preg_match($this->date_preg,$begin_date)){
                $this->begin_date = $begin_date;
            }
            else{
                $this->request_has_error = true;
                $this->request_error_message = "The begin date format error.";
            }
        }

        if($this->request_has_error === false && $request->has('end_date')){
            $end_date = $request->input('end_date');
            if(!empty($end_date) && preg_match($this->date_preg,$end_date)){
                $this->end_date = $end_date;
            }
            else{
                $this->request_has_error = true;
                $this->request_error_message = "The end date format error.";
            }
        }

        if(!empty($this->begin_date) && !empty($this->end_date)){
            if($this->begin_date >= $this->end_date){
                $this->request_has_error = true;
                $this->request_error_message = "The end date must be greater than begin date.";
            }
        }

    }

    /**
     * 搜索客户的接口
     *
     * @param Request $request
     * @return mixed
     */
    public function searchUsers(Request $request){

        $this->allow_search_fields = [
            'code','name','email','phone','user_type_code','user_code','login_fail_times','active_status','language_type_code','created_at'
        ];

        $this->getRequestParams($request);
        if($this->request_has_error){
            return $this->message($this->request_error_message);
        }

        //响应数据的键名数组
        $response_data_keys = [
            'id',
            'code',
            'name',
            'email',
            'phone',
            'user_type_code',
            'user_code',
            'login_fail_times',
            'active_status',
            'language_type_code',
            'created_at',
            'updated_at'
        ];

        switch ($this->search_type){
            //时间段搜索
            case 0:
                $results = UsersModel::
                when($this->begin_date !== null,function($query){
                    return $query->where('created_at','>=',$this->begin_date);
                })
                    ->when($this->end_date !== null,function($query){
                        return $query->where('created_at','<=',$this->end_date);
                    })
                    ->orderBy('created_at','desc')
                    ->select($response_data_keys)
                    ->get();
                break;
            //全字段模糊搜索
            case 1:
                $results = UsersModel::where('code','like',$this->vague_value)
                    ->orwhere('name','like',$this->vague_value)
                    ->orwhere('email','like',$this->vague_value)
                    ->orwhere('phone','like',$this->vague_value)
                    ->orderBy('created_at','desc')
                    ->when($this->begin_date !== null,function($query){
                        return $query->where('created_at','>=',$this->begin_date);
                    })
                    ->when($this->end_date !== null,function($query){
                        return $query->where('created_at','<=',$this->end_date);
                    })
                    ->select($response_data_keys)
                    ->get();
                break;

            //单个字段模糊搜索
            case 2:
                $results = UsersModel::where($this->field,'like',$this->vague_value)
                    ->when($this->begin_date !== null,function($query){
                        return $query->where('created_at','>=',$this->begin_date);
                    })
                    ->when($this->end_date !== null,function($query){
                        return $query->where('created_at','<=',$this->end_date);
                    })
                    ->select($response_data_keys)
                    ->get();
                break;

            //单个字段精准搜索
            case 3:
                $results = UsersModel::where($this->field,'=',$this->value)
                    ->when($this->begin_date !== null,function($query){
                        return $query->where('created_at','>=',$this->begin_date);
                    })
                    ->when($this->end_date !== null,function($query){
                        return $query->where('created_at','<=',$this->end_date);
                    })
                    ->select($response_data_keys)
                    ->get();
                break;

            //参数组合错误，未能匹配到相应的搜索类型
            default:return $this->app_response(RESPONSE::WARNING,'Search parameters format error');
        }

        return $this->app_response(RESPONSE::SUCCESS,'Search success',$results);

    }

    /**
     * 搜索购买记录的接口
     *
     * @param Request $request
     * @return mixed
     */
    public function searchPurchaseRecords(Request $request){

        $this->allow_search_fields = [
            'code','email','users_code','goods_info_code','price','buy_number','total_amount','currency_type_code','payment_times','payment_status','order_no','card_no','created_at','success_time'
        ];

        $this->getRequestParams($request);
        if($this->request_has_error){
            return $this->message($this->request_error_message);
        }

        switch ($this->search_type){
            //时间段搜索
            case 0:
                $results = PurchaseRecordsModel::
                when($this->begin_date !== null,function($query){
                    return $query->where('purchase_records.created_at','>=',$this->begin_date);
                })
                    ->when($this->end_date !== null,function($query){
                        return $query->where('purchase_records.created_at','<=',$this->end_date);
                    })
                    ->orderBy('purchase_records.created_at','desc')
                    ->leftJoin('users','purchase_records.users_id','=','users.id')
                    ->select(['purchase_records.*','users.email'])
                    ->get();
                break;
            //全字段模糊搜索
            case 1:
                $users_id = UsersModel::where('email','like',$this->vague_value)->pluck('id');
                $results = PurchaseRecordsModel::with('goods.name','cards.name')
                    ->when($this->begin_date !== null,function($query){
                        return $query->where('purchase_records.created_at','>=',$this->begin_date);
                    })
                    ->when($this->end_date !== null,function($query){
                        return $query->where('purchase_records.created_at','<=',$this->end_date);
                    })
                    ->where('purchase_records.code','like',$this->vague_value)
                    ->orwhere('purchase_records.goods_info_code','like',$this->vague_value)
                    ->orWhereIn('purchase_records.users_id',$users_id)
                    ->orwhere('purchase_records.order_no','like',$this->vague_value)
                    ->orwhere('purchase_records.card_no','like',$this->vague_value)
                    ->orderBy('purchase_records.created_at','desc')
                    ->leftJoin('users','purchase_records.users_id','=','users.id')
                    ->select(['purchase_records.*','users.email'])
                    ->get();
                break;

            //单个字段模糊搜索
            case 2:
                if($this->field == "email"){
                    $users_id = UsersModel::where('email','like',$this->vague_value)->pluck('id');
                    $results = PurchaseRecordsModel::with('goods.name','cards.name')
                        ->when($this->begin_date !== null,function($query){
                            return $query->where('purchase_records.created_at','>=',$this->begin_date);
                        })
                        ->when($this->end_date !== null,function($query){
                            return $query->where('purchase_records.created_at','<=',$this->end_date);
                        })
                        ->whereIn('purchase_records.users_id',$users_id)
                        ->orderBy('purchase_records.created_at','desc')
                        ->leftJoin('users','purchase_records.users_id','=','users.id')
                        ->select(['purchase_records.*','users.email'])
                        ->get();
                }
                else{
                    $results = PurchaseRecordsModel::with('goods.name','cards.name')
                        ->when($this->begin_date !== null,function($query){
                            return $query->where('purchase_records.created_at','>=',$this->begin_date);
                        })
                        ->when($this->end_date !== null,function($query){
                            return $query->where('purchase_records.created_at','<=',$this->end_date);
                        })
                        ->where('purchase_records.'.$this->field,'like',$this->vague_value)
                        ->orderBy('purchase_records.created_at','desc')
                        ->leftJoin('users','purchase_records.users_id','=','users.id')
                        ->select(['purchase_records.*','users.email'])
                        ->get();
                }
                break;

            //单个字段精准搜索
            case 3:
                if($this->field == "email"){
                    $users_id = UsersModel::where('email','=',$this->value)->value('id');
                    $results = PurchaseRecordsModel::with('goods.name','cards.name')
                        ->when($this->begin_date !== null,function($query){
                            return $query->where('purchase_records.created_at','>=',$this->begin_date);
                        })
                        ->when($this->end_date !== null,function($query){
                            return $query->where('purchase_records.created_at','<=',$this->end_date);
                        })
                        ->where('purchase_records.users_id','=',$users_id)
                        ->leftJoin('users','purchase_records.users_id','=','users.id')
                        ->select(['purchase_records.*','users.email'])
                        ->get();
                }
                else{
                    $results = PurchaseRecordsModel::with('goods.name','cards.name')
                        ->when($this->begin_date !== null,function($query){
                            return $query->where('purchase_records.created_at','>=',$this->begin_date);
                        })
                        ->when($this->end_date !== null,function($query){
                            return $query->where('purchase_records.created_at','<=',$this->end_date);
                        })
                        ->where('purchase_records.'.$this->field,'=',$this->value)
                        ->leftJoin('users','purchase_records.users_id','=','users.id')
                        ->select(['purchase_records.*','users.email'])
                        ->get();
                }
                break;

            //参数组合错误，未能匹配到相应的搜索类型
            default:return $this->app_response(RESPONSE::WARNING,'Search parameters format error');
        }

        return $this->app_response(RESPONSE::SUCCESS,'Search success',$results);

    }

    /**
     * 搜索支付订单的接口
     *
     * @param Request $request
     * @return mixed
     */
    public function searchPaymentOrders(Request $request){

        $this->allow_search_fields = [
            'purchase_code','order_code','email','order_no','order_amount','order_time','order_time_out','currency_type_code','trade_no','trade_status','success_time'
        ];

        $this->getRequestParams($request);
        if($this->request_has_error){
            return $this->message($this->request_error_message);
        }

        switch ($this->search_type){
            //时间段搜索
            case 0:
                $results = PaymentOrdersModel::
                when($this->begin_date !== null,function($query){
                    return $query->where('payment_orders.order_time','>=',$this->begin_date);
                })
                    ->when($this->end_date !== null,function($query){
                        return $query->where('payment_orders.order_time','<=',$this->end_date);
                    })
                    ->orderBy('payment_orders.order_time','desc')
                    ->leftJoin('users','payment_orders.users_id','=','users.id')
                    ->select(['payment_orders.*','users.email'])
                    ->get();
                break;
            //全字段模糊搜索
            case 1:
                $users_id = UsersModel::where('email','like',$this->vague_value)->pluck('id');
                $results = PaymentOrdersModel::where('purchase_code','like',$this->vague_value)
                    ->orwhereIn('payment_orders.users_id',$users_id)
                    ->orwhere('payment_orders.order_no','like',$this->vague_value)
                    ->orwhere('payment_orders.order_time','like',$this->vague_value)
                    ->orwhere('payment_orders.order_time_out','like',$this->vague_value)
                    ->orwhere('payment_orders.success_time','like',$this->vague_value)
                    ->when($this->begin_date !== null,function($query){
                        return $query->where('payment_orders.order_time','>=',$this->begin_date);
                    })
                    ->when($this->end_date !== null,function($query){
                        return $query->where('payment_orders.order_time','<=',$this->end_date);
                    })
                    ->orderBy('payment_orders.order_time','desc')
                    ->leftJoin('users','payment_orders.users_id','=','users.id')
                    ->select(['payment_orders.*','users.email'])
                    ->get();
                break;

            //单个字段模糊搜索
            case 2:
                if($this->field == 'email'){
                    $users_id = UsersModel::where('email','like',$this->vague_value)->pluck('id');
                    $results = PaymentOrdersModel::whereIn('payment_orders.users_id',$users_id)
                        ->when($this->begin_date !== null,function($query){
                            return $query->where('payment_orders.order_time','>=',$this->begin_date);
                        })
                        ->when($this->end_date !== null,function($query){
                            return $query->where('payment_orders.order_time','<=',$this->end_date);
                        })
                        ->orderBy('payment_orders.order_time','desc')
                        ->leftJoin('users','payment_orders.users_id','=','users.id')
                        ->select(['payment_orders.*','users.email'])
                        ->get();
                }
                else{
                    $results = PaymentOrdersModel::where('payment_orders.'.$this->field,'like',$this->vague_value)
                        ->when($this->begin_date !== null,function($query){
                            return $query->where('payment_orders.order_time','>=',$this->begin_date);
                        })
                        ->when($this->end_date !== null,function($query){
                            return $query->where('payment_orders.order_time','<=',$this->end_date);
                        })
                        ->orderBy('payment_orders.order_time','desc')
                        ->leftJoin('users','payment_orders.users_id','=','users.id')
                        ->select(['payment_orders.*','users.email'])
                        ->get();
                }
                break;

            //单个字段精准搜索
            case 3:
                if($this->field == 'email'){
                    $users_id = UsersModel::where('email','=',$this->value)->value('id');
                    $results = PaymentOrdersModel::where('payment_orders.users_id',$users_id)
                        ->when($this->begin_date !== null,function($query){
                            return $query->where('payment_orders.order_time','>=',$this->begin_date);
                        })
                        ->when($this->end_date !== null,function($query){
                            return $query->where('payment_orders.order_time','<=',$this->end_date);
                        })
                        ->leftJoin('users','payment_orders.users_id','=','users.id')
                        ->select(['payment_orders.*','users.email'])
                        ->get();
                }
                else{
                    $results = PaymentOrdersModel::where('payment_orders.'.$this->field,'=',$this->value)
                        ->when($this->begin_date !== null,function($query){
                            return $query->where('payment_orders.order_time','>=',$this->begin_date);
                        })
                        ->when($this->end_date !== null,function($query){
                            return $query->where('payment_orders.order_time','<=',$this->end_date);
                        })
                        ->leftJoin('users','payment_orders.users_id','=','users.id')
                        ->select(['payment_orders.*','users.email'])
                        ->get();
                }
                break;

            //参数组合错误，未能匹配到相应的搜索类型
            default:return $this->app_response(RESPONSE::WARNING,'Search parameters format error');
        }

        return $this->app_response(RESPONSE::SUCCESS,'Search success',$results);

    }

    /**
     * 搜索客户充值卡的接口
     *
     * @param Request $request
     * @return mixed
     */
    public function searchCashCards(Request $request){

        $this->allow_search_fields = [
            'email','purchase_code','order_no','trade_no','card_no','card_value','currency_type_code','use_status','email_notice_status','sms_notice_status','merchant_code','crm_order_no','success_time'
        ];

        $this->getRequestParams($request);
        if($this->request_has_error){
            return $this->message($this->request_error_message);
        }

        switch ($this->search_type){
            //时间段搜索
            case 0:
                $results = CashCardsModel::
                when($this->begin_date !== null,function($query){
                    return $query->where('cash_cards.created_at','>=',$this->begin_date);
                })
                    ->when($this->end_date !== null,function($query){
                        return $query->where('cash_cards.created_at','<=',$this->end_date);
                    })
                    ->orderBy('cash_cards.created_at','desc')
                    ->leftJoin('users','cash_cards.users_id','=','users.id')
                    ->select(['cash_cards.*','users.email'])
                    ->get();
                break;
            //全字段模糊搜索
            case 1:
                $users_id = UsersModel::where('email','like',$this->vague_value)->pluck('id');
                $results = CashCardsModel::where('purchase_code','like',$this->vague_value)
                    ->orwhereIn('cash_cards.users_id',$users_id)
                    ->orwhere('cash_cards.order_no','like',$this->vague_value)
                    ->orwhere('cash_cards.trade_no','like',$this->vague_value)
                    ->orwhere('cash_cards.card_no','like',$this->vague_value)
                    ->orwhere('cash_cards.merchant_code','like',$this->vague_value)
                    ->orwhere('cash_cards.crm_order_no','like',$this->vague_value)
                    ->when($this->begin_date !== null,function($query){
                        return $query->where('cash_cards.created_at','>=',$this->begin_date);
                    })
                    ->when($this->end_date !== null,function($query){
                        return $query->where('cash_cards.created_at','<=',$this->end_date);
                    })
                    ->orderBy('cash_cards.created_at','desc')
                    ->leftJoin('users','cash_cards.users_id','=','users.id')
                    ->select(['cash_cards.*','users.email'])
                    ->get();
                break;

            //单个字段模糊搜索
            case 2:
                if($this->field == 'email'){
                    $users_id = UsersModel::where('email','like',$this->vague_value)->pluck('id');
                    $results = CashCardsModel::whereIn('cash_cards.users_id',$users_id)
                        ->when($this->begin_date !== null,function($query){
                            return $query->where('cash_cards.created_at','>=',$this->begin_date);
                        })
                        ->when($this->end_date !== null,function($query){
                            return $query->where('cash_cards.created_at','<=',$this->end_date);
                        })
                        ->orderBy('cash_cards.created_at','desc')
                        ->leftJoin('users','cash_cards.users_id','=','users.id')
                        ->select(['cash_cards.*','users.email'])
                        ->get();
                }
                else{
                    $results = CashCardsModel::where('cash_cards.'.$this->field,'like',$this->vague_value)
                        ->when($this->begin_date !== null,function($query){
                            return $query->where('cash_cards.created_at','>=',$this->begin_date);
                        })
                        ->when($this->end_date !== null,function($query){
                            return $query->where('cash_cards.created_at','<=',$this->end_date);
                        })
                        ->orderBy('cash_cards.created_at','desc')
                        ->leftJoin('users','cash_cards.users_id','=','users.id')
                        ->select(['cash_cards.*','users.email'])
                        ->get();
                }
                break;

            //单个字段精准搜索
            case 3:
                if($this->field == 'email'){
                    $users_id = UsersModel::where('email','=',$this->value)->value('id');
                    $results = CashCardsModel::where('cash_cards.users_id',$users_id)
                        ->when($this->begin_date !== null,function($query){
                            return $query->where('cash_cards.created_at','>=',$this->begin_date);
                        })
                        ->when($this->end_date !== null,function($query){
                            return $query->where('cash_cards.created_at','<=',$this->end_date);
                        })
                        ->leftJoin('users','cash_cards.users_id','=','users.id')
                        ->select(['cash_cards.*','users.email'])
                        ->get();
                }
                else{
                    $results = CashCardsModel::where('cash_cards.'.$this->field,$this->value)
                        ->when($this->begin_date !== null,function($query){
                            return $query->where('cash_cards.created_at','>=',$this->begin_date);
                        })
                        ->when($this->end_date !== null,function($query){
                            return $query->where('cash_cards.created_at','<=',$this->end_date);
                        })
                        ->leftJoin('users','cash_cards.users_id','=','users.id')
                        ->select(['cash_cards.*','users.email'])
                        ->get();
                }
                break;

            //参数组合错误，未能匹配到相应的搜索类型
            default:return $this->app_response(RESPONSE::WARNING,'Search parameters format error');
        }

        return $this->app_response(RESPONSE::SUCCESS,'Search success',$results);

    }

}
