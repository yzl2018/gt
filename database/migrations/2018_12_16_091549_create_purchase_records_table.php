<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_records', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code',60)->comment('下单编码');
            $table->integer('users_id')->comment('所属客户id');
            $table->string('users_code',60)->comment('要购买的客户编号');
			$table->string('store_info_code',60)->comment('所在店铺编号');
			$table->string('goods_info_code',60)->comment('购买商品编号');
			$table->decimal('price',15,2)->comment('商品单价');
            $table->integer('buy_number')->comment('购买数量');
            $table->decimal('total_amount',15,2)->comment('总购买金额');
            $table->string('currency_type_code',30)->default('CNY')->comment('货币类型编号');
            $table->integer('payment_times')->default(0)->comment('支付次数');
            $table->integer('payment_status')->default(0)->comment('支付状态(0:未支付,1:已支付,-1:支付失败)');
            $table->dateTime('order_time_out')->nullable()->comment('订单过期时间');
            $table->dateTime('success_time')->nullable()->comment('支付成功时间');
            $table->string('order_no',60)->nullable()->comment('支付成功订单编号');
            $table->string('card_no',100)->nullable()->comment('充值卡卡号');
            $table->integer('use_status')->default(0)->comment('充值卡使用状态(0:未使用,1:激活成功,-1:已退款)');
            $table->string('attach_info',1000)->nullable()->comment('附加信息');
            $table->timestamps();
			$table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_records');
    }
}
