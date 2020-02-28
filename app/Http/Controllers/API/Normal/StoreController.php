<?php
namespace App\Http\Controllers\API\Normal;

use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\RESPONSE;
use App\Models\CodeConfigModel;
use App\Models\GoodsInfoModel;
use App\Models\IndustryModel;
use App\Models\StoreInfoModel;
use App\Models\StoreSpecimenInfoModel;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StoreController extends ApiController
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
	private $img_dir = "/store/";

	/**
	 * 获取所有商家店铺信息
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function showAllStores(Request $request){

		$list = StoreInfoModel::
			with('name','introduce','address')
			->orderBy('created_at','desc')
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

		return $this->app_response(RESPONSE::SUCCESS,'get stores success',$list);

	}

    /**
     * 获取商家店铺详情信息
     *
     * @param Request $request
     * @return mixed
     */
    public function getStoreDetails(Request $request){

        if(!$request->has('store_code')){
            return $this->message('Missing parameter');
        }
        $store_code = $request->input('store_code');

        $store_info = StoreInfoModel::
        with('name','introduce','address','evaluation')
            ->where('code',$store_code)
            ->first();

        if(!empty($store_info)){
            $name = $this->transformWords($store_info->name);
            unset($store_info->name);$store_info->name = $name;

            $introduce = $store_info->introduce;
            $intro_obj = [];
            foreach ($introduce as $index => $value){
                $intro_obj[$value->language_type_code] = $value->word;
            }
            unset($store_info->introduce);
            $store_info->introduce = $intro_obj;

            $address = $store_info->address;
            $address_obj = [];
            foreach ($address as $index => $value){
                $address_obj[$value->language_type_code] = $value->word;
            }
            unset($store_info->address);
            $store_info->address = $address_obj;

            $evaluation = $store_info->evaluation;
            $evaluation_obj = [];
            foreach ($evaluation as $index => $value){
                $evaluation_obj[$value->language_type_code] = $value->word;
            }
            unset($store_info->evaluation);
            $store_info->evaluation = $evaluation_obj;
        }

        if(!empty($store_info)){
            $store_info->specimens = StoreSpecimenInfoModel::
            with('name','title','features','introduce')
                ->where('store_info_code',$store_code)
                ->get();

            foreach ($store_info->specimens as $key => $specimen){
                $name = $this->transformWords($specimen->name);
                unset($store_info->specimens[$key]['name']);
                $store_info->specimens[$key]['name'] = $name;

                $title = $this->transformWords($specimen->title);
                unset($store_info->specimens[$key]['title']);
                $store_info->specimens[$key]['title'] = $title;

                $features = $this->transformWords($specimen->features);
                unset($store_info->specimens[$key]['features']);
                $store_info->specimens[$key]['features'] = $features;

                $introduce = $this->transformWords($specimen->introduce);
                unset($store_info->specimens[$key]['introduce']);
                $store_info->specimens[$key]['introduce'] = $introduce;
            }

        }

        return $this->app_response(RESPONSE::SUCCESS,'get store details success',$store_info);

    }

	/**
	 * 创建商家店铺
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function newStore(Request $request){

        if(!$request->has('name')){
            return $this->app_response(RESPONSE::WARNING,'Missing store name');
        }

        $industry_code = $request->input('ind_code');
        $count = IndustryModel::where('code',$industry_code)->count();
        if($count == 0){
            return $this->message('The industry of this code is not exists.');
        }

        $add_name_result = $this->AddWords($request->input('name'));
        if($add_name_result['exists']){
            $code = StoreInfoModel::where('name_word_code',$add_name_result['word_code'])->value('code');
            if($code){
                return $this->app_response(RESPONSE::WARNING,$this->say('!047'),$code);
            }
        }

        $store_code = CodeConfigModel::getUniqueCode('store');

        $grade = sprintf('%1.f',$request->input('grade'));

        $params = [
            'code'				=> $store_code,
            'industry_code'		=> $industry_code,
            'name_word_code'	=> $add_name_result['word_code'],
            'grade'				=> $grade
        ];

        if($request->has('logo')){
            $params['logo'] = $request->input('logo');
        }

        if($request->has('litpic')){
            $params['litpic'] = $request->input('litpic');
        }

        if($request->has('introduce')){
            $add_introduce_result = $this->AddWords($request->input('introduce'));
            $params['introduce_word_code']	= $add_introduce_result['word_code'];
        }

        if($request->has('address')){
            $add_address_result = $this->AddWords($request->input('address'));
            $params['address_word_code'] = $add_address_result['word_code'];
        }

        if($request->has('evaluation')){
            $add_evaluation_result = $this->AddWords($request->input('evaluation'));
            $params['evaluation_word_code'] = $add_evaluation_result['word_code'];
        }

        if($request->has('tag_label')){
            $params['tag_label'] = strval($request->input('tag_label'));
        }

        $params['created_at'] = $params['updated_at'] = date('Y-m-d H:i:s');

        $id = StoreInfoModel::insertGetId($params);
        if($id){
            return $this->app_response(RESPONSE::SUCCESS,$this->say('!050'));
        }

        return $this->app_response(RESPONSE::WARNING,$this->say('!051'));

	}

	/**
	 * 更新商家店铺信息
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function updateStore(Request $request){

		$store_code = $request->input('code');
		$params = [];

		if($request->has('ind_code')){
			$params['industry_code'] = $request->input('ind_code');
		}

		if($request->has('name') && !empty($request->input('name'))){
			$add_name_result = $this->AddWords($request->input('name'));
			$params['name_word_code'] = $add_name_result['word_code'];
		}
		
		if($request->has('logo')){
			$params['logo'] = $request->input('logo');
		}
		
		if($request->has('litpic')){
			$params['litpic'] = $request->input('litpic');
		}

		if($request->has('grade')){
			$grade = $request->input('grade');
			if(!is_numeric($grade)){
				return $this->message('Error grade value');
			}
			$params['grade'] = sprintf('%1.f',$grade);
		}
		
		if($request->has('introduce') && !empty($request->input('introduce'))){
			$add_introduce_result = $this->AddWords($request->input('introduce'));
			$params['introduce_word_code'] = $add_introduce_result['word_code'];
		}
		
		if($request->has('address') && !empty($request->input('address'))){
			$add_address_result = $this->AddWords($request->input('address'));
			$params['address_word_code'] = $add_address_result['word_code'];
		}

		if($request->has('evaluation') && !empty($request->input('evaluation'))){
			$add_evaluation_result = $this->AddWords($request->input('evaluation'));
			$params['evaluation_word_code'] = $add_evaluation_result['word_code'];
		}

        if($request->has('tag_label')){
            $params['tag_label'] = strval($request->input('tag_label'));
        }

		if(empty($params)){
			return $this->app_response(RESPONSE::WARNING,$this->say('!052'));
		}

		$params['updated_at'] = date('Y-m-d H:i:s');

		$isUpdate = StoreInfoModel::where('code',$store_code)->update($params);
		if($isUpdate){
			return $this->app_response(RESPONSE::SUCCESS,$this->say('!053'));
		}

		return $this->app_response(RESPONSE::WARNING,$this->say('!054'));

	}

	/**
	 * 上传店铺样品信息
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function uploadSpecimen(Request $request){

        $specimens = $request->input();

        if(!is_array($specimens)){
            return $this->message('data format error');
        }

        $new_data = [];
        foreach ($specimens as $specimen){

            if(!isset($specimen['store_code'])){
                return $this->message('missing parameter:store_code');
            }
            if(!isset($specimen['name_word'])){
                return $this->app_response(RESPONSE::WARNING,$this->say('!055'));
            }
            if(!isset($specimen['photo'])){
                return $this->message('missing parameter:photo');
            }
            $params = [];
            $params['store_info_code'] = $specimen['store_code'];
            if(!is_array($specimen['name_word'])){
                return $this->message('name word format error');
            }
            $add_name_result = $this->AddWords($specimen['name_word']);
            $params['name_word_code'] = $add_name_result['word_code'];
            $params['photo'] = $specimen['photo'];
            if(isset($specimen['title_word'])){
                if(!is_array($specimen['title_word'])){
                    return $this->message('title word format error');
                }
                $add_title_result = $this->AddWords($specimen['title_word']);
                $params['title_word_code'] = $add_title_result['word_code'];
            }
            if(isset($specimen['features_word'])){
                if(!is_array($specimen['features_word'])){
                    return $this->message('features word format error');
                }
                $add_features_result = $this->AddWords($specimen['features_word']);
                $params['features_word_code'] = $add_features_result['word_code'];
            }
            if(isset($specimen['introduce_word'])){
                if(!is_array($specimen['introduce_word'])){
                    return $this->message('introduce word format error');
                }
                $add_introduce_result = $this->AddWords($specimen['introduce_word']);
                $params['introduce_word_code'] = $add_introduce_result['introduce_word'];
            }
            $params['created_at'] = $params['updated_at'] = date('Y-m-d H:i:s');
            array_push($new_data,$params);

        }

        $success = true;
        try{
            DB::beginTransaction();
            StoreSpecimenInfoModel::insert($new_data);
            DB::commit();
        }
        catch(\Exception $e){
            Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===',[
                'upload specimen error'	=> $e->getMessage(),
                'trace'		=> $e->getTrace()
            ]);
            $success = false;
        }

        if($success){
            return $this->app_response(RESPONSE::SUCCESS,$this->say('!057'));
        }

        return $this->app_response(RESPONSE::WARNING,$this->say('!058'));

	}

	/**
	 * 更新店铺样品信息
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function updateSpecimen(Request $request){

		$store_specimen_id = $request->input('id');
		$store_code = $request->input('store_code');
		$params = [];

		if($request->has('name_word') && !empty($request->input('name_word'))){
			$add_name_result = $this->AddWords($request->input('name_word'));
			$params['name_word_code'] = $add_name_result['word_code'];
		}
		
		if($request->has('photo')){
			$params['photo'] = $request->input('photo');
		}
		
		if($request->has('title_word') && !empty($request->input('title_word'))){
			$add_title_result = $this->AddWords($request->input('title_word'));
			$params['title_word_code'] = $add_title_result['word_code'];
		}
		
		if($request->has('features_word') && !empty($request->input('features_word'))){
			$add_features_result = $this->AddWords($request->input('features_word'));
			$params['features_word_code'] = $add_features_result['word_code'];
		}
		
		if($request->has('introduce_word') && !empty($request->input('introduce_word'))){
			$add_introduce_result = $this->AddWords($request->input('introduce_word'));
			$params['introduce_word_code'] = $add_introduce_result['introduce_word'];
		}

		if(empty($params)){
			return $this->app_response(RESPONSE::WARNING,$this->say('!052'));
		}

		$params['updated_at'] = date('Y-m-d H:i:s');

		$isUpdate = StoreSpecimenInfoModel::where('id',$store_specimen_id)->update($params);
		if($isUpdate){
			return $this->app_response(RESPONSE::SUCCESS,$this->say('!059'));
		}

		return $this->app_response(RESPONSE::WARNING,$this->say('!060'));

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

    /**
     * 获取某个店铺下的所有商品
     *
     * @request string store_code
     *
     * @param Request $request
     * @return mixed
     */
    public function getAllGoodsOfOneStore(Request $request){

        if(!$request->has('store_code')){
            return $this->message('Missing parameter');
        }
        $store_code = $request->input('store_code');

        $list = GoodsInfoModel::
        with('name','features','introduce')
            ->where('store_info_code',$store_code)
            ->get();

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
