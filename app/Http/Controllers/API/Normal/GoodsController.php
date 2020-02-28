<?php
namespace App\Http\Controllers\API\Normal;

use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\RESPONSE;
use App\Models\CodeConfigModel;
use App\Models\GoodsDetailsInfoModel;
use App\Models\GoodsInfoModel;
use App\Models\StoreInfoModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GoodsController extends ApiController
{

	/**
	 * 上传的根目录
	 *
	 * @var string
	 */
	private $root_dir = "/public/upload";

	/**
	 * 上传图片的目录
	 *
	 * @var string
	 */
	private $img_dir = "/goods/";

	/**
	 * 获取所有商品信息的接口
	 *
	 * @uri	/api/goods/all-list
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
	public function showAllGoods(Request $request){

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
	 * 获取商品详情信息
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function getGoodsDetails(Request $request){

		if(!$request->has('goods_code')){
			return $this->message('Missing parameter');
		}
		$goods_code = $request->input('goods_code');

		$goods_info = GoodsInfoModel::
					with('name','features','introduce')
					->where('code',$goods_code)
					->first();

		if(!empty($goods_info)){
            $name = $this->transformWords($goods_info->name);
            unset($goods_info->name);$goods_info->name = $name;

            $features = $this->transformWords($goods_info->features);
            unset($goods_info->features);$goods_info->features = $features;

            $introduce = $this->transformWords($goods_info->introduce);
            unset($goods_info->introduce);$goods_info->introduce = $introduce;

			$goods_info->details = GoodsDetailsInfoModel::
				with('title','information')
				->where('goods_info_code',$goods_code)
				->get();

			foreach ($goods_info->details as $key => $detail){
			    $title = $this->transformWords($detail->title);
			    unset($goods_info->details[$key]['title']);
                $goods_info->details[$key]['title'] = $title;

                $information = $this->transformWords($detail->information);
                unset($goods_info->details[$key]['information']);
                $goods_info->details[$key]['information'] = $information;
            }
		}

		return $this->app_response(RESPONSE::SUCCESS,'get store details success',$goods_info);

	}

	/**
	 * 新增商品
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function newGoods(Request $request){

		$this->getUser();

		$currency_type_code = $request->input('currency_type_code');
		$currency_access = $this->checkCurrencyForAccess($currency_type_code);
		if($currency_access['denied']){
			return $this->message($currency_access['message']);
		}

		if(!$request->has('name_word')){
			return $this->app_response(RESPONSE::WARNING,$this->say('!066'));
		}
		$params = [];

		$store_info_code = $request->input('store_info_code');
		$count = StoreInfoModel::where('code',$store_info_code)->count();
		if($count == 0){
			return $this->message('The store is not exists');
		}
		$params['store_info_code'] = $request->input('store_info_code');

		$params['code'] = CodeConfigModel::getUniqueCode('goods');
        $params['original_price'] = sprintf("%.2f",$request->input('original_price'));
		$params['price'] = sprintf("%.2f",$request->input('price'));

		$add_name_result = $this->AddWords($request->input('name_word'));
		$params['name_word_code'] = $add_name_result['word_code'];

		$params['currency_type_code'] = $request->input('currency_type_code');

		if($request->has('litpic')){
			$params['litpic'] = $request->input('litpic');
		}

		$limit_amount = config('system.goods.'.$params['currency_type_code'].'.buy_limit');
		$stop_amount = config('system.goods.'.$params['currency_type_code'].'.buy_stop');

		if($request->has('buy_limit')){
			$buy_limit = $params['price'] * $request->input('buy_limit');
			if($buy_limit >= $limit_amount && $buy_limit <= $stop_amount){
				$params['buy_limit'] = $request->input('buy_limit');
			}
		}

		if(!isset($params['buy_limit'])){
			$params['buy_limit'] = ceil($limit_amount / $params['price']);
		}

		if($request->has('buy_stop')){
			if($request->input('buy_stop') <= $params['buy_limit']){
				return $this->message($this->say('!063'));
			}
			$buy_stop = $params['price'] * $request->input('buy_stop');
			if($buy_stop > $params['price'] * $params['buy_limit'] && $buy_stop <= $stop_amount){
				$params['buy_stop'] = $request->input('buy_stop');
			}
		}

		if(!isset($params['buy_stop'])){
			$params['buy_stop'] = floor($stop_amount / $params['price']);
		}

		if($request->has('features_word')){
			$add_features_result = $this->AddWords($request->input('features_word'));
			$params['features_word_code'] = $add_features_result['word_code'];
		}

		if($request->has('introduce_word')){
			$add_introduce_result = $this->AddWords($request->input('introduce_word'));
			$params['introduce_word_code'] = $add_introduce_result['word_code'];
		}

		$params['created_at'] = $params['updated_at'] = date('Y-m-d H:i:s');
		$id = GoodsInfoModel::insertGetId($params);
		if($id > 0){
			return $this->app_response(RESPONSE::SUCCESS,$this->say('!064'));
		}

		return $this->app_response(RESPONSE::WARNING,$this->say('!065'));

	}

	/**
	 * 更新商品
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function updateGoods(Request $request){

		$this->getUser();

		$goods_code = $request->input('goods_code');
		$params = [];
		$goods_info = GoodsInfoModel::where('code',$goods_code)->first();

		if(empty($goods_info)){
			return $this->message('此编号的商品不存在或已被删除');
		}

		if($request->has('name_word')){
			$add_name_result = $this->AddWords($request->input('name_word'));
			$params['name_word_code'] = $add_name_result['word_code'];

			if($goods_info->name_word_code == $params['name_word_code']){
				return $this->message('The value to be updated is the same as the original value.');
			}
		}

		if($request->has('litpic')){
			$params['litpic'] = $request->input('litpic');
		}

        if($request->has('original_price')){
            $params['original_price'] = $request->input('original_price');
        }

		if($request->has('price')){
			$params['price'] = $request->input('price');
		}

		if($request->has('currency_code')){
			if(in_array($request->input('currency_code'),['USD','CNY'])){
				return $this->message('Error currency code');
			}
			$params['currency_type_code'] = $request->input('currency_code');

			$currency_access = $this->checkCurrencyForAccess($params['currency_type_code']);
			if($currency_access['denied']){
				return $this->message($currency_access['message']);
			}
		}

		if(isset($params['price'])){
			$price = $params['price'];
		}
		else{
			$price = $goods_info->price;
		}

		if(isset($params['currency_type_code'])){
			$currency_code = $params['currency_type_code'];
		}
		else{
			$currency_code = $goods_info->currency_type_code;
		}

		$limit_amount = config('system.goods.'.$currency_code.'.buy_limit');
		$stop_amount = config('system.goods.'.$currency_code.'.buy_stop');

		if($request->has('buy_limit')){
			$buy_limit = $params['price'] * $request->input('buy_limit');
			if($buy_limit >= $limit_amount && $buy_limit <= $stop_amount){
				$params['buy_limit'] = $request->input('buy_limit');
			}
		}

		if(!isset($params['buy_limit'])){
			$params['buy_limit'] = ceil($limit_amount / $price);
		}

		if($request->has('buy_stop')){
			if($request->input('buy_stop') <= $params['buy_limit']){
				return $this->message('商品购买上限数量不能小于购买下限数量');
			}
			$buy_stop = $price * $request->input('buy_stop');
			if($buy_stop > $price * $params['buy_limit'] && $buy_stop <= $stop_amount){
				$params['buy_stop'] = $request->input('buy_stop');
			}
		}

		if(!isset($params['buy_stop'])){
			$params['buy_stop'] = floor($stop_amount / $price);
		}

		if($request->has('features_word')){
			$add_features_result = $this->AddWords($request->input('features_word'));
			$params['features_word_code'] = $add_features_result['word_code'];
		}

		if($request->has('introduce_word')){
			$add_introduce_result = $this->AddWords($request->input('introduce_word'));
			$params['introduce_word_code'] = $add_introduce_result['word_code'];
		}

		if(empty($params)){
			return $this->app_response(RESPONSE::WARNING,$this->say('!052'));
		}

		$isUpdate = GoodsInfoModel::where('code',$goods_code)->update($params);
		if($isUpdate){
			return $this->app_response(RESPONSE::SUCCESS,$this->say('!067'));
		}

		return $this->app_response(RESPONSE::WARNING,$this->say('!068'));

	}

	/**
	 * 上传商品详情图片
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function uploadGoodsDetails(Request $request){

        $goods_details = $request->input();
        if(!is_array($goods_details)){
            return $this->message('data format error');
        }

        $new_data = [];
        foreach ($goods_details as $goods_detail){
            if(!isset($goods_detail['goods_code'])){
                return $this->message('missing parameter:goods_code');
            }
            if(!isset($goods_detail['title_word'])){
                return $this->message($this->say('!069'));
            }
            if(!isset($goods_detail['image'])){
                return $this->message('missing parameter:image');
            }
            $goods_code = $goods_detail['goods_code'];
            $params = [];
            $params['goods_info_code'] = $goods_code;
            if(!is_array($goods_detail['title_word'])){
                return $this->message('title word format error');
            }
            $add_title_result = $this->AddWords($goods_detail['title_word']);
            $params['title_word_code'] = $add_title_result['word_code'];
            $params['image'] = $goods_detail['image'];
            if($request->has('info_word')){
                if(!is_array($goods_detail['info_word'])){
                    return $this->message('info word format error');
                }
                $add_info_result = $this->AddWords($goods_detail['info_word']);
                $params['information_word_code'] = $add_info_result['word_code'];
            }
            array_push($new_data,$params);
        }

        $success = true;
        try{
            DB::beginTransaction();
            GoodsDetailsInfoModel::insert($new_data);
            DB::commit();
        }
        catch(\Exception $e){
            Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===',[
                'upload goods details error'	=> $e->getMessage(),
                'trace'		=> $e->getTrace()
            ]);
            $success = false;
        }

        if($success){
            return $this->app_response(RESPONSE::SUCCESS,$this->say('!070'));
        }

        return $this->app_response(RESPONSE::WARNING,$this->say('!071'));

	}

	/**
	 * 更新商品详情图片
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function updateGoodsDetails(Request $request){

		$this->getUser();

		$goods_details_id = $request->input('id');
		$goods_code = $request->input('goods_code');
		$params = [];

		if($request->has('title_word')){
			$add_title_result = $this->AddWords($request->input('title_word'));
			$params['title_word_code'] = $add_title_result['word_code'];
		}

		if($request->has('image')){
			$params['image'] = $request->input('image');
		}

		if($request->has('info_word')){
			$add_info_result = $this->AddWords($request->input('info_word'));
			$params['information_word_code'] = $add_info_result['word_code'];
		}

		if(empty($params)){
			return $this->app_response(RESPONSE::WARNING,$this->say('!052'));
		}

		$isUpdate = GoodsDetailsInfoModel::where('id',$goods_details_id)->update($params);
		if($isUpdate){
			return $this->app_response(RESPONSE::SUCCESS,$this->say('!072'));
		}

		return $this->app_response(RESPONSE::WARNING,$this->say('!073'));
	}

	/**
	 * 移除商品
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function removeGoods(Request $request){

		$this->getUser();

		$goods_id = $request->input('id');
		$count = GoodsInfoModel::destroy($goods_id);

		if($count > 0){
			return $this->app_response(RESPONSE::SUCCESS,$this->say('!074'));
		}

		return $this->app_response(RESPONSE::WARNING,$this->say('!075'));

	}

	/**
	 * 获取图片重命名后的存储路径
	 *
	 * @param $store_code
	 * @param $mime_type
	 * @param null $type
	 * @return array
	 */
	private function rebuildFile($store_code,$mime_type,$type = null){

		if(!file_exists($this->root_dir)){
			mkdir($this->root_dir);
		}

		if(!file_exists($this->root_dir.$this->img_dir)){
			mkdir($this->root_dir.$this->img_dir);
		}

		if(!file_exists($this->root_dir.$this->img_dir.$store_code)){
			mkdir($this->root_dir.$this->img_dir.$store_code);
		}

		$rename = "store_";
		if($type != null){
			$rename .= $type."_";
		}
		$rename .= date('ymdHis').".".$mime_type;

		return [
			'path'	=> $this->root_dir.$this->img_dir.$store_code."/",
			'name'	=> $rename
		];
	}

}
