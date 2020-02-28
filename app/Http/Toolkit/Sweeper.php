<?php
namespace App\Http\Toolkit;

use App\Models\BNotifyLogModel;
use App\Models\CrmRequestLogModel;
use App\Models\LoginLogModel;
use App\Models\NotifyMerchantCardLogModel;
use App\Models\RegisterActivationLogModel;
use App\Models\SendMailLogModel;
use App\Models\UserOperateLogModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait Sweeper
{

	/**
	 * 日志表对应数据模型的数组
	 *
	 * @var array
	 */
	private $log_tables = [
		'register_activation_log'		=> RegisterActivationLogModel::class,
		'login_log'						=> LoginLogModel::class,
		'b_notify_log'					=> BNotifyLogModel::class,
		'crm_request_log'				=> CrmRequestLogModel::class,
		'user_operate_log'				=> UserOperateLogModel::class,
		'send_mail_log'             	=> SendMailLogModel::class,
		'notify_merchant_card_log'		=> NotifyMerchantCardLogModel::class,
		'request_guide_mail_records'	=> RequestGuideMailRecordsModel::class,
	];

	/**
	 * 清道夫自动清理日志
	 */
	protected function autoClearLog(){

		$configs = DB::table('auto_clear_log_config')->get();

		foreach ($configs as $key => $log){

			if($log->delete_type == 0){//软删除和硬删除均执行
				$this->forceDeletes($log->log_table_name,$log->date_key,$log->delete_forever_limit_days);
				$this->softDeletes($log->log_table_name,$log->date_key,$log->delete_moment_limit_days);
			}

			else if($log->delete_type == 1){//只执行软删除
				$this->softDeletes($log->log_table_name,$log->date_key,$log->delete_moment_limit_days);
			}

			else if($log->delete_type == -1){//只执行硬删除
				$this->forceDeletes($log->log_table_name,$log->date_key,$log->delete_forever_limit_days);
			}

		}

	}

	/**
	 * 软删除
	 *
	 * @param string $table_name
	 * @param string $date_key
	 * @param int $limit_days
	 */
	private function softDeletes(string $table_name,string $date_key,int $limit_days){

		$date_time = date('Y-m-d H:i:s');
		$limit_time = date('Y-m-d H:i:s',strtotime($date_time." -".$limit_days." day"));

		$deleted_num = $this->log_tables[$table_name]::where($date_key,'<',$limit_time)->delete();
        Log::channel('sweeper')->info(__FUNCTION__,[$table_name => $deleted_num." rows"]);

	}

	/**
	 * 硬删除
	 *
	 * @param string $table_name
	 * @param string $date_key
	 * @param int $limit_days
	 */
	private function forceDeletes(string $table_name,string $date_key,int $limit_days){

		$date_time = date('Y-m-d H:i:s');
		$limit_time = date('Y-m-d H:i:s',strtotime($date_time." -".$limit_days." day"));

		$deleted_rows = $this->log_tables[$table_name]::where($date_key,'<',$limit_time)->get();
		$deleted_num = $this->log_tables[$table_name]::where($date_key,'<',$limit_time)->forceDelete();
        Log::channel('sweeper')->info(__FUNCTION__,[$table_name => $deleted_num." rows"]);
		if($deleted_num > 0){
			foreach ($deleted_rows as $row){
				//将硬删除的行数据记录到 已删除的日志文件
				Log::channel($table_name)->info('',['row'=>json_encode($row)]);
			}
		}

	}

}
