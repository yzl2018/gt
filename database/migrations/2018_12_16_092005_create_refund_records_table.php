<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefundRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refund_records', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('users_id')->comment('充值卡所属用户id');
			$table->string('order_no',100)->comment('申请退款的订单编号');
			$table->string('card_no',100)->comment('申请退款的充值卡编号');
			$table->decimal('card_value',15,2)->comment('申请退款的充值卡面额');
			$table->string('currency_type_code',20)->comment('申请退款的货币类型编号');
			$table->string('reason',500)->nullable()->comment('退款理由');
            $table->string('dispose_person',50)->nullable()->comment('退款处理人');
            $table->string('mark',200)->nullable()->comment('退款处理意见');
            $table->dateTime('finish_time')->nullable()->comment('退款完成时间');
            $table->integer('status')->default(0)->comment('退款状态(0:未处理,1:同意退款,2:拒绝退款,3退款完成)');
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
        Schema::dropIfExists('refund_records');
    }
}
