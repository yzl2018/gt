<?php
namespace App\Http\Controllers\API\Normal;

use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\RESPONSE;
use App\Models\GoodsDetailsInfoModel;
use App\Models\GoodsInfoModel;
use App\Models\IndustryModel;
use App\Models\StoreInfoModel;
use App\Models\StoreSpecimenInfoModel;
use Illuminate\Http\Request;

class PushStoreGoodsController extends ApiController
{

    /**
     * 标签
     *
     * @var null
     */
    private $tag = null;

    /**
     * 行业编码
     *
     * @var null
     */
    private $industry_code = null;

    /**
     * 店铺编码
     *
     * @var null
     */
    private $store_code = null;

    /**
     * 获取数据的个数
     *
     * @var int
     */
    private $limit = 0;

	/**
	 * 设置推荐店铺
	 *
	 * @request array tag_list:[{id:tag_label}...]
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function setPopularStore(Request $request){

		$this->getUser();

		if(!$request->has('tag_list')){
			return $this->message('Missing parameter');
		}

		$tag_list = $request->input('tag_list');

		if(!is_array($tag_list)){
			return $this->message('Parameters type error');
		}

        $update_data = [];
        foreach ($tag_list as $tag){
            if(!is_array($tag)){
                return $this->message('Tag list format error');
            }
            foreach ($tag as $id => $label){
                $update_data[$id] = $label;
            }
        }

        $success = 0;$fail = 0;
        foreach ($update_data as $id => $tag_label){
            $is_update = StoreInfoModel::where('id',$id)->update(['tag_label'=>$tag_label,'updated_at'=>date('Y-m-d H:i:s')]);
            if($is_update > 0){
                $success++;
            }
            else{
                $fail++;
            }
        }

		$message = $this->say('!110').':'.$success.$this->say('!111').',';
		$message .= $this->say('!109').':'.$fail.$this->say('!111');

		return $this->app_response(RESPONSE::SUCCESS,$message);

	}

	/**
	 * 获取推荐店铺
	 *
	 * @request string tag
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function getPopularStore(Request $request){

		if(!$request->has('tag')){
			return $this->message('Missing parameter');
		}

		if($request->input('tag') === NULL || $request->input('tag') === ""){
			return $this->message('Empty parameter');
		}

        if($request->has('industry_code')){
            $this->industry_code = $request->input('industry_code');
            $industry_count = IndustryModel::where('code',$this->industry_code)->count();
            if($industry_count == 0){
                return $this->message('Error industry code');
            }
        }

        $this->tag = $request->input('tag');
        if($this->tag == 0){
            $this->limit = config('system.mall.store_newest_limit');
        }

		if($request->has('limit')){
			$this->limit = $request->input('limit');
			if(!is_int($this->limit)){
				return $this->message('Invalid parameter limit');
			}
		}

		$list = StoreInfoModel::
				with('name','introduce','address','evaluation','specimens')
                ->when($this->tag!=0,function($query){
                    return $query->where('tag_label','like','%'.$this->tag.'%');
                })
				->orderBy('updated_at','desc')
                ->when($this->industry_code != null,function($query){
                    return $query->where('industry_code',$this->industry_code);
                })
				->when($this->limit>0,function($query){
					return $query->limit($this->limit);
				})
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

            foreach ($list[$key]['specimens'] as $k => $v){
                $name = $this->transformWords($v->name);
                unset($list[$key]['specimens'][$k]['name']);
                $list[$key]['specimens'][$k]['name'] = $name;

                $title = $this->transformWords($v->title);
                unset($list[$key]['specimens'][$k]['title']);
                $list[$key]['specimens'][$k]['title'] = $title;

                $features = $this->transformWords($v->features);
                unset($list[$key]['specimens'][$k]['features']);
                $list[$key]['specimens'][$k]['features'] = $features;

                $introduce = $this->transformWords($v->introduce);
                unset($list[$key]['specimens'][$k]['introduce']);
                $list[$key]['specimens'][$k]['introduce'] = $introduce;
            }
        }

		return $this->app_response(RESPONSE::SUCCESS,'get popular stores success',$list);

	}

	/**
	 * 设置推荐商品
	 *
	 * @request array [{id:tag_label}...]
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function setPopularGoods(Request $request){

		$this->getUser();

		if(!$request->has('tag_list')){
			return $this->message('Missing parameter');
		}

		$tag_list = $request->input('tag_list');

		if(!is_array($tag_list)){
			return $this->message('Parameters type error');
		}

        $update_data = [];
        foreach ($tag_list as $tag){
            if(!is_array($tag)){
                return $this->message('Tag list format error');
            }
            foreach ($tag as $id => $label){
                $update_data[$id] = $label;
            }
        }

        $success = 0;$fail = 0;
        foreach ($update_data as $id => $tag_label){
            $is_update = GoodsInfoModel::where('id',$id)->update(['tag_label'=>$tag_label,'updated_at'=>date('Y-m-d H:i:s')]);
            if($is_update > 0){
                $success++;
            }
            else{
                $fail++;
            }
        }

		$message = $this->say('!110').':'.$success.$this->say('!111').',';
		$message .= $this->say('!109').':'.$fail.$this->say('!111');

		return $this->app_response(RESPONSE::SUCCESS,$message);

	}

	/**
	 * 获取推荐商品
	 *
	 * @request string tag
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function getPopularGoods(Request $request){

		if(!$request->has('tag')){
			return $this->message('Missing parameter');
		}

        if($request->input('tag') === NULL || $request->input('tag') === ""){
            return $this->message('Empty parameter');
        }

        if($request->has('store_code')){
            $this->store_code = $request->input('store_code');
            $store_count = StoreInfoModel::where('code',$this->store_code)->count();
            if($store_count == 0){
                return $this->message('Error store code');
            }
        }

        $this->tag = $request->input('tag');
        if($this->tag == 0){
            $this->limit = config('system.mall.goods_newest_limit');
        }

		if($request->has('limit')){
            $this->limit = $request->input('limit');
			if(!is_int($this->limit)){
				return $this->message('Invalid parameter limit');
			}
		}

        $list = GoodsInfoModel::
        with('name','features','introduce','details')
            ->when($this->tag!=0,function($query){
                return $query->where('tag_label','like','%'.$this->tag.'%');
            })
            ->orderBy('updated_at','desc')
            ->when($this->store_code != null,function($query){
                return $query->where('store_info_code',$this->store_code);
            })
            ->when($this->limit>0,function($query){
                return $query->limit($this->limit);
            })
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

            foreach ($list[$key]['details'] as $k => $v){
                $title = $this->transformWords($v->title);
                unset($list[$key]['details'][$k]['title']);
                $list[$key]['details'][$k]['title'] = $title;

                $information = $this->transformWords($v->information);
                unset($list[$key]['details'][$k]['information']);
                $list[$key]['details'][$k]['information'] = $information;
            }
        }

		return $this->app_response(RESPONSE::SUCCESS,'get goods success',$list);

	}

	/**
	 * 设置店铺样品排序规则
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function setStoreSpecimenSort(Request $request){

		if(!$request->has('sort_rule')){
			return $this->message('Missing parameter');
		}

		$sort_rules = $request->input('sort_rule');

		if(!is_array($sort_rules)){
			return $this->message('Error sort rule');
		}

		$success = 0;
		$fail = 0;
		foreach ($sort_rules as $id => $sort_number){
			$is_update = StoreSpecimenInfoModel::where('id',$id)->update(['sort_number'=>$sort_number,'updated_at'=>date('Y-m-d H:i:s')]);
			if($is_update > 0){
				$success ++;
			}
			else{
				$fail++;
			}
		}

		$message = $this->say('!110').':'.$success.$this->say('!111').',';
		$message .= $this->say('!109').':'.$fail.$this->say('!111');

		return $this->app_response(RESPONSE::SUCCESS,$message);

	}

	/**
	 * 设置商品详情的排序规则
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function setGoodsDetailSort(Request $request){

		if(!$request->has('sort_rule')){
			return $this->message('Missing parameter');
		}

		$sort_rules = $request->input('sort_rule');

		if(!is_array($sort_rules)){
			return $this->message('Error sort rule');
		}

		$success = 0;
		$fail = 0;
		foreach ($sort_rules as $id => $sort_number){
			$is_update = GoodsDetailsInfoModel::where('id',$id)->update(['sort_number'=>$sort_number,'updated_at'=>date('Y-m-d H:i:s')]);
			if($is_update > 0){
				$success++;
			}
			else{
				$fail++;
			}
		}

		$message = $this->say('!110').':'.$success.$this->say('!111').',';
		$message .= $this->say('!109').':'.$fail.$this->say('!111');

		return $this->app_response(RESPONSE::SUCCESS,$message);

	}

}
