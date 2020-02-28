<?php
/**
 * +------------------------------------------------------------------------------
 * Effect:
 * +------------------------------------------------------------------------------
 * @Desc
 * @Author    Administrator
 * @CreateDate  2019/1/2 10:41
 * +------------------------------------------------------------------------------
 */

namespace App\Http\Toolkit;


use Illuminate\Support\Facades\File;

trait FileStore
{
	
	/**
	 * 上传的根目录
	 *
	 * @var string
	 */
	private $root_dir = "/public/upload";
	
	/**
	 * 上传文件的目录
	 *
	 * @var string
	 */
	private $file_dir = "/files";
	
	/**
	 * 上传图片的目录
	 *
	 * @var string
	 */
	private $img_dir = "/images";
	
	public function __construct()
	{
		if(!file_exists($this->root_dir)){
			mkdir($this->root_dir);
		}
		
		if(!file_exists($this->root_dir.$this->file_dir)){
			mkdir($this->root_dir.$this->file_dir);
		}
		
		if(!file_exists($this->root_dir.$this->img_dir)){
			mkdir($this->root_dir.$this->img_dir);
		}
	}
	
	protected function upload_file(File $file,string $sub_dir = null){
	
	}
	
	protected function upload_img(File $img_file,string $sub_dir = null){
		$url_path = 'uploads/cover';
		$rule = ['jpg', 'png', 'gif'];
		if ($img_file->isValid()) {
			$clientName = $img_file->getClientOriginalName();
			$tmpName = $img_file->getFileName();
			$realPath = $img_file->getRealPath();
			$entension = $img_file->getClientOriginalExtension();
			if (!in_array($entension, $rule)) {
				return '图片格式为jpg,png,gif';
			}
			$newName = md5(date("Y-m-d H:i:s") . $clientName) . "." . $entension;
			$path = $img_file->move($url_path, $newName);
			$namePath = $url_path . '/' . $newName;
			return $path;
		}
		
	}

}