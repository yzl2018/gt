<?php
namespace App\Http\Controllers\API;

use App\Jobs\SendEmail;
use App\Http\Controllers\API\Entity\EmailEntity;
use App\Http\Toolkit\AppResponse;
use App\Http\Toolkit\AutoGenerate;
use App\Http\Toolkit\Message;
use App\Models\CodeConfigModel;
use App\Models\SendMailLogModel;
use App\Models\SysLanguageWordsModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Log;

class ApiController
{
	use Message,AppResponse,AutoGenerate,DispatchesJobs;

	/**
	 * B系统错误码临界值：若错误码小于该值则直接显示错误信息，若错误码大于或等于该值则隐藏错误信息
	 *
	 * @var int
	 */
	protected $b_error_code_critical = 0x0FFF;

	/**
	 * 当前网站地址
	 *
	 * @var string
	 */
	protected $web_site;

	/**
	 * 接口权限配置文件
	 *
	 * @var string
	 */
	private $interface_config = "apiinterface.";

	/**
	 * 用户实体
	 *
	 * @var \Illuminate\Contracts\Auth\Authenticatable|null
	 */
	protected $user;

	/**
	 * 用户类型编码
 	 *
	 * @var null|int
	 */
	protected $user_type_code = null;

	/**
	 * 管理员编码配置
	 *
	 * @var string
	 */
	protected $admin_code_config = "system.user.admin.code";

	/**
	 * 客服编码配置
	 *
	 * @var string
	 */
	protected $service_code_config = "system.user.service.code";

	/**
	 * 客户编码配置
	 *
	 * @var string
	 */
	protected $customer_code_config = "system.user.customer.code";

	/**
	 * 时间格式
	 *
	 * @var string
	 */
	protected $time_fmt = 'Y-m-d H:i:s';

	/**
	 * 响应数据的键名数组
	 *
	 * @var array
	 */
	protected $response_data_keys = ['id'];

	/**
	 * 分页项目数
	 *
	 * @var int
	 */
	protected $page_items = 50;

	/**
	 * 订单过期时间，以秒计
	 *
	 * @var float|int
	 */
	protected $order_expires_seconds = 40 * 60;

    /**
     * 延迟执行过期任务，以分钟计
     *
     * @var int
     */
    protected $overdue_delay_minutes = 40;

	/**
	 * 验证日期的正则表达式
	 *
	 * @var string
	 */
	protected $date_preg = '/^([12]\d\d\d)-(0?[1-9]|1[0-2])-(0?[1-9]|[12]\d|3[0-1]) ([0-1]\d|2[0-4]):([0-5]\d)(:[0-5]\d)?$/';

	/**
	 * 验证正整数的正则表达式
	 *
	 * @var string
	 */
	protected $int_preg = '/^[1-9][0-9]*$/';

	/**
	 * 查询起始时间
	 *
	 * @var
	 */
	protected $begin_date;

	/**
	 * 查询结束时间
	 *
	 * @var
	 */
	protected $end_date;

	/**
	 * 是否分页 默认分页
	 *
	 * @var bool
	 */
	protected $is_paginate = true;

	/**
	 * 获取当前用户实体
	 */
	protected function getUser(){

		$date_time = date('YmdHis');
		$days = config('system.mall.date_range');
		$days = intval($days) + 1;
		$this->begin_date = date('Y-m-d H:i:s',strtotime($date_time." -".$days." day"));
		$this->end_date = date('Y-m-d H:i:s',strtotime($date_time." +1 day"));

		if(env('APP_LIVE')){
			$this->web_site = config('system.mall.live_url');
		}
		else{
			$this->web_site = config('system.mall.demo_url');
		}

        $this->user = Auth::user();
		config(['system.mall.language'=>$this->user['language_type_code']]);
		if(isset($this->user['user_type_code'])){
			$this->user_type_code = $this->user['user_type_code'];
		}

	}
	
	/**
	 * 检查指定的货币类型是否允许在该商城交易
	 *
	 * @param String $currency
	 * @return array
	 */
	protected function checkCurrencyForAccess(String $currency){

		$allow_currency_types = config('system.mall.allow_currency_types',[]);

		$currency_type = implode(',',$allow_currency_types);

		if(!in_array(strtoupper($currency),$allow_currency_types)){
			return ['denied'=>true,'message'=>'Invalid currency,allowed to be traded in the mall is '.$currency_type];
		}

		return ['denied'=>false,'message'=>'Access success'];

	}

	/**
	 * 获取查询时间范围
	 *
	 * @param Request $request
	 */
	protected function getSearchParams(Request $request){

		if($request->has('is_paginate')){
			$is_paginate = $request->input('is_paginate');
			if($is_paginate == null || $is_paginate == "false"){
				$this->is_paginate = false;
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
	 * 判断该用户是否是管理员
	 *
	 * @return bool
	 */
	protected function isAdmin(){

		if($this->user_type_code == config($this->admin_code_config)){
			return true;
		}

		return false;
	}

	/**
	 * 判断该用户是否是客服
	 *
	 * @return bool
	 */
	protected function isCustomerService(){

		if($this->user_type_code == config($this->service_code_config)){
			return true;
		}

		return false;
	}

	/**
	 * 判断该用户是否是客户
	 *
	 * @return bool
	 */
	protected function isCustomer(){

		if($this->user_type_code == config($this->customer_code_config)){
			return true;
		}

		return false;
	}

	/**
	 * 判断该用户是否允许访问指定接口
	 *
	 * @param string $api
	 * @return bool
	 */
	protected function AccessDenied(string $api){

		$permission = config($this->interface_config.$api);

		if($permission == null){
			return true;
		}

		if(!in_array($this->user['user_type_code'],$permission)){
			return true;
		}

		return false;

	}

    /**
     * 添加各语言文字
     *
     * @param array $words
     * @return array
     */
	protected function AddWords(array $words){

	    $cn_name = $words['cn'];
	    $word_code = SysLanguageWordsModel::where('word',$cn_name)->value('word_code');
	    if($word_code){
	        return ['exists'=>true,'word_code'=>$word_code];
        }

	    $word_code = CodeConfigModel::getUniqueCode('word');
	    $data = [];
        foreach ($words as $key => $value){
        	$time = date('Y-m-d H:i:s');
            array_push($data,[
                'word_code'     => $word_code,
                'language_type_code'    => strtoupper($key),
                'word'  => $value,
				'created_at'	=> $time,
				'updated_at'	=> $time
            ]);
        }
        DB::table('sys_language_words')->insert($data);
        return ['exists'=>false,'word_code'=>$word_code];

    }

    /**
     * 获取指定文字编码的文字信息
     *
     * @param string $word_code
     * @return array
     */
    protected function getWordsByCode(string $word_code){

        $results = SysLanguageWordsModel::where('word_code',$word_code)->pluck('word','language_type_code');
        if($results){
            return $results;
        }

        return [];
    }

	/**
	 * 正确获取请求数据
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return array
	 * @throws \Exception
	 */
	protected function getRequestData(Request $request){

		$inputs = [];

		$headers = $request->headers->all();
		if (isset($headers['content-type']) && str_contains($headers['content-type'][0],'x-www-form-urlencoded'))
		{
			$content = $request->getContent();
			if(is_string($content)) {
				$content = trim($content);
				$is_encoded = (strpos(substr($content, strlen($content) - 3), '=') === false);
				if ($is_encoded) {
					// get data from input()
					// data may be encoded or not
					$inputs['Sign'] = $request->input('Sign');
					$inputs['Data'] = $request->input('Data');
				}
				else {
					// get data from getContent()
					// $content is not urlencoded
					$params = [];
					$keys = ['Data','Sign'];
					$arrays = explode("&", $content);
					foreach ($arrays as $arr){
						$key = substr($arr,0,4);
						$value = substr($arr,5);
						if(in_array($key, $keys)) {
							$params[$key] = $value;
						}
					}
					$inputs = $params;
				}
			}
			else {
                Log::debug(__FUNCTION__,['content_type'=>$headers['content-type']]);
                Log::debug(__FUNCTION__,['content'=> $request->getContent() ]);
                Log::debug(__FUNCTION__,[ "inputs" => $request->input() ]);
				throw new \Exception('error content data type of content-body in the x-www-form-urlencoded');
			}
		}
		else if($request->has('Sign') && $request->has('Data')){
			// others content-type : json, form-data
			$inputs['Sign'] = $request->input('Sign');
			$inputs['Data'] = $request->input('Data');
		}
		else{
			throw new \Exception('Can not get the data from the request. Please check the content type.');
		}

		return $inputs;

	}

	/**
	 * 派发邮件到队列任务
	 *
	 * @param string $mail_type_code
	 * @param array $mail_data
	 * @param string $customer_email
	 * @param $delay_seconds
	 * @param string|null $attach_path
	 * @return mixed
	 */
	protected function dispatchMailJob(string $mail_type_code,array $mail_data,string $customer_email,$delay_seconds = 0,string $attach_path = null){

		$date_time = date($this->time_fmt);

		$mail_log = [
			'mail_type_code'	=> $mail_type_code,
			'mail_data'			=> json_encode($mail_data),
			'mail_to'			=> $customer_email,
			'created_at'		=> $date_time,
			'updated_at'		=> $date_time
		];

		if(!empty($attach_path)){
			$mail_log['attach_path'] = $attach_path;
		}
		$mail_log_id = SendMailLogModel::insertGetId($mail_log);

		$mail = new EmailEntity($mail_type_code,$mail_data,$customer_email,$attach_path,$mail_log_id);
		$redis_key = $this->dispatch((new SendEmail($mail))->delay(now()->addSeconds($delay_seconds)));

		if(is_string($redis_key)){
            $data['mail_channel'] = $mail->mail_channel;
			$data['dispatch_status'] = 1;
			$data['redis_key']		 = $redis_key;
			SendMailLogModel::where('id',$mail_log_id)->update($data);
		}

		if(is_object($redis_key) || is_array($redis_key)){
			$redis_key = json_encode($redis_key);
		}
		return $redis_key;

	}

    /**
     * 将文字字段结果转换成指定格式
     *
     * @param $texts
     * @return array
     */
    protected function transformWords($texts){

        $text_obj = [];
        foreach ($texts as $key => $text){
            if(!empty($text)){
                $text_obj[$text->language_type_code] = $text->word;
            }
        }

        return $text_obj;

    }

    /**
     * 获取单页面Token信息
     *
     * @param string $first
     * @param string $second
     * @param $salt
     * @return string
     */
    protected function getSinglePageToken(string $first,string $second,$salt){

        return sha1($first.$salt).md5($second.$salt).".".$salt;

    }

    /**
     * 验证单页面Token信息
     *
     * @param string $first
     * @param string $second
     * @param string $token
     * @return bool
     */
    protected function verifyPageToken(string $first,string $second,string $token){

        if(!str_contains('.',$token)){
            return false;
        }

        $arr = explode('.',$token);

        $token_value = $arr[0];
        $salt = $arr[1];

        $page_token = $this->getSinglePageToken($first,$second,$salt);
        Log::debug('test page token',[
            'token_value'   => $token_value,
            'page_token'    => $page_token
        ]);
        if($token_value == $page_token){
            return true;
        }

        return false;

    }

    /**
     * 隐藏充值卡密码
     *
     * @param $vouchers
     * @return array
     */
    protected function encryptVoucherKey($vouchers){

        if(!is_array($vouchers)){
            return $vouchers;
        }

        foreach ($vouchers as $card => $key){
            $vouchers[$card] = '******';
        }

        return $vouchers;

    }

    /**
     * 轮询数组算法，按指定重复次数轮询数组内的元素
     *
     * @param array $robin_arr	被轮询的数组
     * @param int $repeat_times 单个元素一次轮询重复调用次数
     * @return mixed
     */
    protected function roundRobinArr(array $robin_arr,$repeat_times = 1){

        $length = count($robin_arr);
        if($length == 0){
            return null;
        }
        if($length == 1){
            return $robin_arr[0];
        }

        $robin_key = md5(json_encode($robin_arr));

        $pointer = Cache::store('redis')->rememberForever($robin_key,function(){
            return [
                'index'	=> 0,
                'times'	=> 0
            ];
        });

        if($pointer['times'] >= $repeat_times){
            if($pointer['index'] >= $length-1){
                $pointer['index'] = 0;
            }
            else{
                $pointer['index']++;
            }
            $pointer['times'] = 1;
        }
        else{
            $pointer['times']++;
        }


        Cache::store('redis')->forever($robin_key,$pointer);

        return $robin_arr[$pointer['index']];

    }
}
