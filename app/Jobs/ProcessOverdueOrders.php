<?php

namespace App\Jobs;

use App\Http\Controllers\API\Entity\OverdueRecords;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;

class ProcessOverdueOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $records;

    /**
     * Create a new job instance.
     *
     * @param OverdueRecords $records
     * @return void
     */
    public function __construct(OverdueRecords $records)
    {
        $this->records = $records;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //每5秒处理一次Redis队列任务
        Redis::throttle('ProcessOverdueOrders')->allow(1)->every(5)->then(function(){

            if(Schema::hasTable($this->records->table_name)){
                if(Schema::hasColumns($this->records->table_name,[$this->records->column_key,$this->records->status_key])){
                    $status = DB::table($this->records->table_name)->where($this->records->column_key,$this->records->column_value)->value($this->records->status_key);

                    //只有当过了过期时间之后，状态仍为0时，才将其设置为过期失效的订单
                    if($status == 0){
                        try{
                            DB::table($this->records->table_name)->where($this->records->column_key,$this->records->column_value)->update([$this->records->status_key=>-1]);
                        }
                        catch(\Exception $e){
                            Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
                                'Failed to update the status:' => [$this->records->table_name,$this->records->status_key]
                            ]);
                        }
                    }
                }
                else{
                    Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
                        'Missing Columns:' => [$this->records->column_key,$this->records->status_key]
                    ]);
                }
            }
            else{
                Log::error('===' . __FILE__ . ' (line:' . __LINE__ . ')===', [
                    'The table is not exists:' => $this->records->table_name
                ]);
            }

        },function(){
            $this->release(3);
        });
    }
}
