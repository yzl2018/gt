<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SetExpriesOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check overdue orders';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		$earlier_time = date('Y-m-d H:i:s',time()-config('system.mall.overdue_delay_minutes')*60);
    	try{
			DB::table('purchase_records')
				->where('created_at','<',$earlier_time)
				->where('payment_status','=',0)
				->update(['payment_status'=>-1]);
		}
		catch(\Exception $e){
			Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
				'Update purchase records status' => $e->getMessage(),
				'trace' => $e->getTrace()
			]);
		}

		try{
			DB::table('payment_orders')
				->where('order_time','<',$earlier_time)
				->where('trade_status','=',0)
				->update(['trade_status'=>-1]);
		}
		catch(\Exception $e){
			Log::error('===' . __FILE__ . '(line:' . __LINE__ . ')===', [
				'Update payment orders status' => $e->getMessage(),
				'trace' => $e->getTrace()
			]);
		}
    }
}
