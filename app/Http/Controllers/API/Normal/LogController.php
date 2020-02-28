<?php

namespace App\Http\Controllers\API\Normal;

use App\Http\Controllers\API\ApiController;
use App\Http\Toolkit\RESPONSE;
use App\Models\BNotifyLogModel;
use App\Models\CrmRequestLogModel;
use App\Models\LoginLogModel;
use App\Models\NotifyMerchantCardLogModel;
use App\Models\RegisterActivationLogModel;
use App\Models\RequestGuideMailRecordsModel;
use App\Models\ResponseCrmLogModel;
use App\Models\SendMailLogModel;
use App\Models\UserOperateLogModel;
use Illuminate\Http\Request;

class LogController extends ApiController
{

	/**
	 * 查看所有注册日志
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function showRegisterLogs(Request $request){

		$this->getUser();
		$this->getSearchParams($request);

		$list = RegisterActivationLogModel::where('created_at','>=',$this->begin_date)
			->where('created_at','<=',$this->end_date)
            ->orderBy('created_at','desc')
			->when($this->is_paginate,function($query){
				return $query->paginate($this->page_items);
			},function($query){
				return $query->get();
			});

		return $this->app_response(RESPONSE::SUCCESS,'get register log success',$list);

	}

	/**
	 * 查看所有登陆日志
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function showLoginLogs(Request $request){

		$this->getUser();
		$this->getSearchParams($request);

		$list = LoginLogModel::when($this->isCustomer(),function($query){
				return $query->where('login_user',$this->user['phone'])->orwhere('login_user',$this->user['email']);
				})->where('login_at','>=',$this->begin_date)
				->where('login_at','<=',$this->end_date)
                ->orderBy('login_at','desc')
				->when($this->is_paginate,function($query){
					return $query->paginate($this->page_items);
				},function($query){
					return $query->get();
				});

		return $this->app_response(RESPONSE::SUCCESS,'get login log success',$list);

	}

	/**
	 * 查看所有B系统通知日志
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function showBNotifyLogs(Request $request){

		$this->getUser();
		$this->getSearchParams($request);

		$list = BNotifyLogModel::where('time','>=',$this->begin_date)
			->where('time','<=',$this->end_date)
            ->orderBy('time','desc')
			->when($this->is_paginate,function($query){
				return $query->paginate($this->page_items);
			},function($query){
				return $query->get();
			});

		return $this->app_response(RESPONSE::SUCCESS,'get b notify log success',$list);

	}

    /**
     * 查看所有通知商户充值卡激活日志
     *
     * @param Request $request
     * @return mixed
     */
    public function showNotifyMerchantLogs(Request $request){

        $this->getUser();
        $this->getSearchParams($request);

        $list = NotifyMerchantCardLogModel::where('created_at','>=',$this->begin_date)
            ->where('created_at','<=',$this->end_date)
            ->orderBy('created_at','desc')
            ->when($this->is_paginate,function($query){
                return $query->paginate($this->page_items);
            },function($query){
                return $query->get();
            });

        return $this->app_response(RESPONSE::SUCCESS,'get notify merchant logs success',$list);

    }

	/**
	 * 查看所有CRM 激活请求日志
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function showCrmRequestLogs(Request $request){

		$this->getUser();
		$this->getSearchParams($request);

		$list = CrmRequestLogModel::where('request_time','>=',$this->begin_date)
			->where('request_time','<=',$this->end_date)
            ->orderBy('request_time','desc')
			->when($this->is_paginate,function($query){
				return $query->paginate($this->page_items);
			},function($query){
				return $query->get();
			});

		return $this->app_response(RESPONSE::SUCCESS,'get crm request log success',$list);

	}

	/**
	 * 查看所有用户操作日志
	 *
	 * @param Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function showUserOperateLogs(Request $request){

		$this->getUser();
		$this->getSearchParams($request);

        $list = UserOperateLogModel::with('operate_type')
            ->where('user_operate_log.time','>=',$this->begin_date)
            ->where('user_operate_log.time','<=',$this->end_date)
            ->orderBy('user_operate_log.time','desc')
            ->leftJoin('users','user_operate_log.users_id','=','users.id')
            ->select(['user_operate_log.*','users.email'])
            ->when($this->is_paginate,function($query){
                return $query->paginate($this->page_items);
            },function($query){
                return $query->get();
            });

		return $this->app_response(RESPONSE::SUCCESS,'get operate log success',$list);

	}

    /**
     * 查看所有邮件发送日志
     *
     * @param Request $request
     * @return mixed
     */
    public function showSendMailLogs(Request $request){

        $this->getUser();
        $this->getSearchParams($request);

        $list = SendMailLogModel::with('mail_type')
            ->where('created_at','>=',$this->begin_date)
            ->where('created_at','<=',$this->end_date)
			//->where('mail_to','=','zsx9696@126.com')
			//->where('mail_type_code','=','58118')
            ->orderBy('created_at','desc')
            ->when($this->is_paginate,function($query){
                return $query->paginate($this->page_items);
            },function($query){
                return $query->get();
            });

        return $this->app_response(RESPONSE::SUCCESS,'get send mail log success',$list);

    }
	
	/**
	 * 查看所有商户引导邮件发送日志
	 *
	 * @param Request $request
	 * @return mixed
	 */
	public function showGuideMailLogs(Request $request)
	{

		$this->getUser();
		$this->getSearchParams($request);

		$list = RequestGuideMailRecordsModel::with('mail_type')
			->where('request_time','>=',$this->begin_date)
			->where('request_time','<=',$this->end_date)
			->orderBy('request_time','desc')
			->when($this->is_paginate,function($query){
				return $query->paginate($this->page_items);
			},function($query){
				return $query->get();
			});

		return $this->app_response(RESPONSE::SUCCESS,'get guide mail log success',$list);

	}

}
