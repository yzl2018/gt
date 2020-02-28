<?php
/**
 * +------------------------------------------------------------------------------
 * Effect:
 * +------------------------------------------------------------------------------
 * @Desc
 * @Author    Administrator
 * @CreateDate  2019/1/29 17:46
 * +------------------------------------------------------------------------------
 */

namespace App\Http\Controllers\API\Debug;

use App\Models\LoginLogModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestDebugController
{

	private $log_tables = [
		'login_log'	=> LoginLogModel::class,
	];

	public function testSoftDelete(){
		$configs = DB::table('auto_clear_log_config')->get();

		//$count = LoginLogModel::where('id','25')->delete();
		return response()->json(['data'=>$configs])->setEncodingOptions(JSON_UNESCAPED_UNICODE);

	}

	public function testForceDelete(){

		$time = '2019-01-22 16:40:08';
		$deleted_logs = $this->log_tables['login_log']::where('login_at','>',$time)->get();
		$count = $this->log_tables['login_log']::where('login_at','>',$time)->forceDelete();

		if($count > 0){
			foreach ($deleted_logs as $log){
				Log::channel('login_log')->info('',['row'=>json_encode($log,JSON_UNESCAPED_UNICODE)]);
			}
		}
		return response()->json(['硬删除个数'=>$count,'data'=>$deleted_logs])->setEncodingOptions(JSON_UNESCAPED_UNICODE);

	}

	public function testStorageLink(){

		echo asset('storage/goods/15181697245390.jpg');

	}

    public function changeOrderStatus(){

        $sum = DB::table('payment_orders')->where('trade_status',2)->count();
        if($sum > 0){
            echo "<pre>";
            print_r("有".$sum."个数据需要更新");
            DB::table('payment_orders')->where('trade_status',2)->update(['trade_status'=>-1]);
        }
        else{
            print_r("暂时没有需要更新的数据");
        }

    }

    public function updateLoginPassword(){

//        $records_sum = "SELECT count(id) as sum FROM db_gsdpay_a.users WHERE length(password) = 32 limit 1000";
//        $count = DB::select($records_sum);
//        $sum = $count[0]->sum;
        $sum = 500;
        if($sum > 0){
            echo "<pre>";
            print_r("有".$sum."个数据需要更新");

            $sql = "SELECT id,password FROM db_gsdpay_a.users WHERE length(password) = 32 LIMIT 500";
            $users = DB::select($sql);
            $success = $fail =0;
            foreach ($users as $user){
                $update_data['password'] = bcrypt($user->password);
                $is_update = DB::table('users')->where('id',$user->id)->update($update_data);
                if($is_update){
                    $success++;
                }
                else{
                    $fail++;
                }
            }
            echo "<pre>";
            print_r("更新成功：".$success."条，更新失败：".$fail."条");
        }
        else{
            print_r("暂时没有需要更新的数据");
        }

    }

}
