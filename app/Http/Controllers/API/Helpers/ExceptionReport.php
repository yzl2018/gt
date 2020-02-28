<?php
/**
 * +------------------------------------------------------------------------------
 * Effect:
 * +------------------------------------------------------------------------------
 * @Desc
 * @Author    Administrator
 * @CreateDate  2018/12/11 17:22
 * +------------------------------------------------------------------------------
 */

namespace App\Http\Controllers\API\Helpers;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ExceptionReport
{
	use ApiResponse;
	
	/**
	 * @var Exception
	 */
	public $exception;
	/**
	 * @var Request
	 */
	public $request;
	
	/**
	 * @var
	 */
	protected $report;
	
	/**
	 * ExceptionReport constructor.
	 * @param Request $request
	 * @param Exception $exception
	 */
	function __construct(Request $request, Exception $exception)
	{
		$this->request = $request;
		$this->exception = $exception;
	}
	
	/**
	 * @var array
	 */
	public $doReport = [
		AuthenticationException::class => ['Unauthorized',401],
		ModelNotFoundException::class => ['The model was not found',404]
	];
	
	/**
	 * @return bool
	 */
	public function shouldReturn(){
		
		if (! ($this->request->wantsJson() || $this->request->ajax())){
			return false;
		}
		
		foreach (array_keys($this->doReport) as $report){
			
			if ($this->exception instanceof $report){
				
				$this->report = $report;
				return true;
			}
		}
		
		return false;
		
	}
	
	/**
	 * @param Exception $e
	 * @return static
	 */
	public static function make(Exception $e){
		
		return new static(\request(),$e);
	}
	
	/**
	 * @return mixed
	 */
	public function report(){
		
		$message = $this->doReport[$this->report];
		
		return $this->failed($message[0],$message[1]);
		
	}
	
}