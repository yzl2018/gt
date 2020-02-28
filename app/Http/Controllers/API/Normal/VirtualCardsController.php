<?php
namespace App\Http\Controllers\API\Normal;

use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\RESPONSE;
use App\Models\CodeConfigModel;
use App\Models\VirtualCardsModel;
use Illuminate\Http\Request;

class VirtualCardsController extends ApiController
{

	/**
	 * 获取所有虚拟卡片信息
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function showAllCards(Request $request){

        $this->getSearchParams($request);

        $list = VirtualCardsModel::with('name')
			->orderBy('created_at','desc')
            ->when($this->is_paginate,function($query){
                return $query->paginate($this->page_items);
            },function($query){
                return $query->get();
            });

        foreach ($list as $key => $goods) {
            $name = $goods->name;
            $name_obj = [];
            foreach ($name as $index => $value) {
                $name_obj[$value->language_type_code] = $value->word;
            }
            unset($list[$key]['name']);
            $list[$key]['name'] = $name_obj;
        }

		return $this->app_response(RESPONSE::SUCCESS,'get cards success',$list);

	}

	/**
	 * 新增虚拟卡片信息
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function newCards(Request $request){

		$this->getUser();

		$date_time = date('Y-m-d H:i:s');

        if(!$request->has('name')){
            return $this->message('Please enter the name');
        }

		$add_name_result = $this->AddWords($request->input('name'));

		$params = [
			'name_word_code'	=> $add_name_result['word_code'],
			'price'			=> $request->input('price'),
			'currency_type_code'	=> $request->input('currency_type_code'),
			'created_at'	=> $date_time,
			'updated_at'	=> $date_time
		];

		if($request->has('litpic')){
			$params['litpic'] = $request->input('litpic');
		}

		$params['code'] = CodeConfigModel::getUniqueCode('virtual_cards');

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

		$id = VirtualCardsModel::insertGetId($params);
		if($id > 0){
			return $this->app_response(RESPONSE::SUCCESS,'new cards success');
		}

		return $this->app_response(RESPONSE::WARNING,'new cards fail');

	}

	/**
	 * 更新虚拟卡片信息
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function updateCards(Request $request){

		$this->getUser();

		$cards_code = $request->input('cards_code');
		$params = [];

		$cards_info = VirtualCardsModel::where('code',$cards_code)->first();

		if($request->has('name')){
			$add_name_result = $this->AddWords($request->input('name'));
			$params['name_word_code'] = $add_name_result['word_code'];
		}

		if($request->has('litpic')){
			$params['litpic'] = $request->input('litpic');
		}

		if($request->has('price')){
			$params['price'] = $request->input('price');
		}

		if($request->has('currency_code')){
			if(in_array($request->input('currency_type_code'),['USD','CNY'])){
				return $this->message('Error currency type code');
			}
			$params['currency_type_code'] = $request->input('currency_type_code');
		}

		if(isset($params['price'])){
			$price = $params['price'];
		}
		else{
			$price = $cards_info->price;
		}

		if(isset($params['currency_type_code'])){
			$currency_code = $params['currency_type_code'];
		}
		else{
			$currency_code = $cards_info->currency_type_code;
		}

		$limit_amount = config('system.goods.'.$currency_code.'.buy_limit');
		$stop_amount = config('system.goods.'.$currency_code.'.buy_stop');

		if($request->has('buy_limit')){
			$buy_limit = $price * $request->input('buy_limit');
			if($buy_limit >= $limit_amount && $buy_limit <= $stop_amount){
				$params['buy_limit'] = $request->input('buy_limit');
			}
		}

		if($request->has('buy_stop')){
			if($request->input('buy_stop') <= $params['buy_limit']){
				return $this->message($this->say('!063'));
			}
			$buy_stop = $price * $request->input('buy_stop');
			if($buy_stop > $price * $params['buy_limit'] && $buy_stop <= $stop_amount){
				$params['buy_stop'] = $request->input('buy_stop');
			}
		}

		if(empty($params)){
			return $this->app_response(RESPONSE::WARNING,$this->say('!052'));
		}

		$isUpdate = VirtualCardsModel::where('code',$cards_code)->update($params);
		if($isUpdate){
			return $this->app_response(RESPONSE::SUCCESS,$this->say('!112'));
		}

		return $this->app_response(RESPONSE::WARNING,$this->say('!113'));

	}

}
