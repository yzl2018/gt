<?php
namespace App\Http\Controllers\API\Normal;


use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\RESPONSE;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadFileController extends ApiController
{

	/**
	 * 文件存储根目录
	 *
	 * @var string
	 */
	private $file_root;

    /**
     * 相对根目录
     *
     * @var string
     */
	private $root_base = '/assets/';

	/**
	 * 文件存储子目录
	 *
	 * @var string
	 */
	private $store_directory = 'default';

	/**
	 * 文件名
	 *
	 * @var
	 */
	private $file_name;

	/**
	 * 文件对外是否可见
	 *
	 * @var bool
	 */
	private $visibility = true;

	/**
	 * 文件存储磁盘
	 *
	 * @var string
	 */
	private $store_disk = 'public';

	/**
	 * 上传文件
	 *
	 * @param Request $request
	 * @return mixed
	 *
	 * @request
	 $("#uploadSubmit").click(function(){
		//获取文件对象，files是文件选取控件的属性，存储的是文件选取控件选取的文件对象，类型是一个数组
		var fileObj = fileM.files[0];
		//创建formdata对象，formData用来存储表单的数据，表单数据时以键值对形式存储的。
		var formData = new FormData();
		if(typeof fileObj !== 'undefined'){
			formData.append('file_source', fileObj);//文件源数据 必填
			formData.append('file_type','goods');//文件分类存放 可选
			formData.append('file_name','test123');//指定文件名称 可选
			formData.append('visibility','false');//文件是否对外可见 可选
			$.ajax({
				url:'http://www.newmall.com/api/service/upload-file',
				type: "post",
				dataType: "json",
				data: formData,
				async: false,
				cache: false,
				contentType: false,
				processData: false,
				success: function (resp) {
					if(resp.code == 0xFFF){//响应成功
						var file_path = resp.data.file_path;
						swal({ title: resp.message, type: "success", allowOutsideClick: true, timer: 1300 });
					}
	 				else{//响应失败
						swal({ title: resp.message, type: "warning", allowOutsideClick: true, timer: 1300 });
					}
				},
				error:function(resp){
					swal({ title: '响应异常', type: "error", allowOutsideClick: true, timer: 1200 });
				}
			});
		}else{
			swal({ title: "请选择图片", type: "warning", allowOutsideClick: true, timer: 1200 });
		}
	});
	 *
	 */
	public function uploadFile(Request $request){

        $this->file_root = config('system.upload.file_root');
		$file = $request->file('file_source');

		if(empty($file)){
			return $this->message($this->say('!092'));
		}

		$file_size = $file->getSize();
		$max_size = config('system.upload.maxsize');
		$min_size = config('system.upload.minsize');

		if($file_size < $min_size){
			return $this->error($this->say('!089').strval($min_size/1000).' KB');
		}

		if($file_size > $max_size){
			return $this->error($this->say('!090').strval($max_size/1000).' KB');
		}

		if(!$file->isValid()){
			return $this->error($this->say('!093'));
		}

		if($request->has('file_type')){
			$this->store_directory = $request->input('file_type');
		}

        if(!file_exists($this->file_root.$this->store_directory)){
            mkdir($this->file_root.$this->store_directory);
        }

		//获取文件的扩展名
		$ext = $file->getClientOriginalExtension();

		if($request->has('file_name')){
			$this->file_name = $request->input('file_name').'.'.$ext;
			if(file_exists($this->file_root.$this->store_directory.'/'.$this->file_name)){
				return $this->app_response(RESPONSE::WARNING,$this->say('!091'),$this->file_name);
			}
		}
		else{
			$this->file_name = date('YmdHis').'.'.$ext;
		}

		$filePath = $this->file_root.$this->store_directory.'/'.$this->file_name;

		$success = move_uploaded_file($_FILES["file_source"]["tmp_name"], $filePath);
		$return_data = ['file_path'=>$this->root_base.$this->store_directory.'/'.$this->file_name];

		if($success){
			return $this->app_response(RESPONSE::SUCCESS,$this->say('!094'),$return_data);
		}

		return $this->app_response(RESPONSE::WARNING,$this->say('!093'));

	}

	public function upload(Request $request){

        $this->file_root = config('system.upload.file_root');
		$file = $request->file('file_source');

		if(empty($file)){
			return $this->message($this->say('!092'));
		}

		$file_size = $file->getSize();
		$max_size = config('system.upload.maxsize');
		$min_size = config('system.upload.minsize');

		if($file_size < $min_size){
			return $this->error($this->say('!089').strval($min_size/1000).' KB');
		}

		if($file_size > $max_size){
			return $this->error($this->say('!090').strval($max_size/1000).' KB');
		}

		if(!$file->isValid()){
			return $this->error($this->say('!093'));
		}

		if($request->has('file_type')){
			$this->store_directory = $request->input('file_type');
		}

		if($request->has('visibility')){
			$visibility = $request->input('visibility');
			if($visibility == false || $visibility == 0){
				$this->visibility = false;
				$this->store_disk = 'local';
			}
		}

		//获取文件的扩展名
		$ext = $file->getClientOriginalExtension();

		if($request->has('file_name')){
			$this->file_name = $request->input('file_name').'.'.$ext;
			$exists = Storage::disk($this->store_disk)->exists($this->store_directory.'/'.$this->file_name);
			if($exists){
				return $this->app_response(RESPONSE::WARNING,$this->say('!091'),$this->file_name);
			}
		}
		else{
			$this->file_name = date('YmdHis').'.'.$ext;
		}

		$path = $file->storeAs($this->store_directory,$this->file_name,$this->store_disk);

		if($this->visibility){
			$path = $this->file_root.$path;
		}

		$return_data = ['file_path'=>$path];

		return $this->app_response(RESPONSE::SUCCESS,$this->say('!094'),$return_data);

	}

	/**
	 * 下载文件
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function downloadFile(Request $request){

		//$file_path = $request->input('file_path');
		$file_path = 'goods/20190211173839.jpg';

		$download_name = null;
		if($request->has('file_name')){
			$arr = explode('.',strval($file_path));
			$ext = $arr[count($arr)-1];
			$download_name = $request->input('file_name').'.'.$ext;
		}

		if(!Storage::disk('public')->exists($file_path) && !Storage::disk('local')->exists($file_path)){
			return $this->error($this->say('!095'));
		}

		if(empty($download_name)){
			return Storage::download($file_path);
		}

		return Storage::download($file_path,$download_name);

	}

	/**
	 * 获取文件内容
	 *
	 * @return string
	 */
	public function getFileContents(){

		try{
			$contents = Storage::disk('local')->get('goods/20190211172429.jpg');
		}
		catch (\Exception $e){
			return $e->getMessage();
		}

		return $contents;

	}

}
