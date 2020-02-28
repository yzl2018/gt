<?php
namespace App\Http\Controllers\API\Normal;

use App\Http\Controllers\API\ApiController;
use App\Models\CodeConfigModel;
use App\Models\IndustryModel;
use Illuminate\Http\Request;
use App\Models\StoreInfoModel;
use App\Models\GoodsInfoModel;
use App\Http\Toolkit\RESPONSE;

class IndustryController extends ApiController
{

	/**
	 * 获取所有行业信息
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function showAllIndustry(Request $request){

		$this->getUser();

		$list = IndustryModel::
			with('name')
			->orderBy('created_at','desc')
			->get();

        foreach ($list as $key => $industry){
            $name = $industry->name;
            $name_obj = [];
            foreach ($name as $index => $value){
                $name_obj[$value->language_type_code] = $value->word;
            }
            unset($list[$key]['name']);
            $list[$key]['name'] = $name_obj;
        }

		return $this->app_response(RESPONSE::SUCCESS,'get industry success',$list);

	}

	/**
	 * 创建新行业
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function newIndustry(Request $request){

        if($request->has('father_code')){
            $father_code = $request->input('father_code');
            $id = IndustryModel::where('code',$father_code)->value('id');
            if(empty($id)){
                return $this->message($this->say('!108'));
            }
        }

        $add_result = $this->AddWords($request->input('word'));
        if($add_result['exists']){
            $code = IndustryModel::where('name_word_code',$add_result['word_code'])->value('code');
            if($code){
                return $this->app_response(RESPONSE::WARNING,'Sorry,the industry already exists!',$code);
            }
        }

        $code = CodeConfigModel::getUniqueCode('industry');
        $time = date('Y-m-d H:i:s');
        $params = [
            'code'=>$code,
            'name_word_code'=>$add_result['word_code'],
            'created_at'=>$time,
            'updated_at'=>$time
        ];

        if($request->has('icon')){
            $params['icon']	= $request->input('icon');
        }

        if($request->has('father_code')){
            $params['father_code'] = $request->input('father_code');
        }
        $id = IndustryModel::insertGetId($params);
        if($id){
            return $this->app_response(RESPONSE::SUCCESS,$this->say('!043'),['code'=>$code]);
        }

        return $this->app_response(RESPONSE::WARNING,$this->say('!044'));
	}

	/**
	 * 更新行业信息
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function updateIndustry(Request $request){

		$params = [];

		if($request->has('word')){
			$add_result = $this->AddWords($request->input('word'));
			$params['name_word_code'] = $add_result['word_code'];
		}

		if($request->has('icon')){
			$params['icon']	= $request->input('icon');
		}

		if(empty($params)){
			return $this->app_response(RESPONSE::WARNING,$this->say('!052'));
		}

		$params['updated_at'] = date('Y-m-d H:i:s');

		$is_update = IndustryModel::where('id',$request->input('ind_id'))->update($params);
		if($is_update){
			return $this->app_response(RESPONSE::SUCCESS,$this->say('!045'));
		}
		
		return $this->app_response(RESPONSE::WARNING,$this->say('!046'));

	}

    /**
     * 获取某个分类下的所有店铺
     *
     * @request string industry_code
     *
     * @param Request $request
     * @return mixed
     */
    public function getAllStoresOfOneIndustry(Request $request){

        if(!$request->has('industry_code')){
            return $this->message('Missing parameter');
        }
        $industry_code = $request->input('industry_code');

        $list = StoreInfoModel::
        with('name','introduce','address','evaluation')
            ->where('industry_code',$industry_code)
            ->get();

        foreach ($list as $key => $store){
            $name = $store->name;
            $name_obj = [];
            foreach ($name as $index => $value){
                $name_obj[$value->language_type_code] = $value->word;
            }
            unset($list[$key]['name']);
            $list[$key]['name'] = $name_obj;

            $introduce = $store->introduce;
            $intro_obj = [];
            foreach ($introduce as $index => $value){
                $intro_obj[$value->language_type_code] = $value->word;
            }
            unset($list[$key]['introduce']);
            $list[$key]['introduce'] = $intro_obj;

            $address = $store->address;
            $address_obj = [];
            foreach ($address as $index => $value){
                $address_obj[$value->language_type_code] = $value->word;
            }
            unset($list[$key]['address']);
            $list[$key]['address'] = $address_obj;

            $evaluation = $store->evaluation;
            $evaluation_obj = [];
            foreach ($evaluation as $index => $value){
                $evaluation_obj[$value->language_type_code] = $value->word;
            }
            unset($list[$key]['evaluation']);
            $list[$key]['evaluation'] = $evaluation_obj;
        }

        return $this->app_response(RESPONSE::SUCCESS,'get all stores success',$list);
    }

    /**
     * 获取某个分类下的所有商品
     *
     * @request string industry_code
     *
     * @param Request $request
     * @return mixed
     */
    public function getAllGoodsOfOneIndustry(Request $request){

        if(!$request->has('industry_code')){
            return $this->message('Missing parameter');
        }
        $industry_code = $request->input('industry_code');

        $list = [];
        $stores = StoreInfoModel::where('industry_code',$industry_code)->get(['code']);
        if(count($stores) > 0){
            $store_codes = [];
            foreach ($stores as $key => $value){
                array_push($store_codes,$value->code);
            }

            $list = GoodsInfoModel::
            with('name','features','introduce')
                ->whereIn('store_info_code',$store_codes)
                ->get();
        }

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

        return $this->app_response(RESPONSE::SUCCESS,'get all goods success',$list);

    }
}
