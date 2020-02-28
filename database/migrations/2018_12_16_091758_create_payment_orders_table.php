<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('purchase_code',60)->comment('下单编码');
            $table->string('order_code',60)->comment('订单编码');
            $table->integer('users_id')->comment('要购买的客户id');
            $table->integer('purchase_records_id')->comment('所属购买记录id');
            $table->string('order_no',60)->comment('生成的订单编号');
			$table->decimal('order_amount',15,2)->comment('订单金额');
            $table->dateTime('order_time')->comment('订单生成时间');
            $table->dateTime('order_time_out')->comment('订单过期时间');
            $table->string('currency_type_code',20)->default('CNY')->comment('货币类型编号');
            $table->string('trade_no',60)->nullable()->comment('系统流水号');
            $table->integer('trade_status')->default(0)->comment('交易状态(0:未支付,1:支付成功,-1:已过期)');
            $table->dateTime('success_time')->nullable()->comment('交易成功时间');
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
        Schema::dropIfExists('payment_orders');
    }
}
