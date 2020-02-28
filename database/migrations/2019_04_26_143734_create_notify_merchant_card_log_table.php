<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotifyMerchantCardLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notify_merchant_card_log', function (Blueprint $table) {
            $table->increments('id');
            $table->ipAddress('sys_ip')->comment('B系统ip');
            $table->string('ip_address',200)->nullable()->comment('ip所在地');
            $table->string('api',100)->comment('访问的接口');
            $table->string('method',20)->comment('通知方式');
            $table->text('receive_params')->comment('上游通知参数');
            $table->dateTime('receive_time')->comment('上游通知时间');
            $table->string('response_b',300)->nullable()->comment('响应给上游的信息');
            $table->string('remarks',500)->nullable()->comment('响应成功或失败的备注信息');

            $table->string('notify_url',300)->nullable()->comment('通知下游商户的地址');
            $table->string('notify_parameters',500)->nullable()->comment('通知下游商户的参数');
            $table->text('sign_data')->nullable()->comment('通知参数的签名加密数据');
            $table->dateTime('notify_time')->nullable()->comment('通知下游的时间');
            $table->string('crm_response',1000)->nullable()->comment('下游商户的响应信息');
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
        Schema::dropIfExists('notify_merchant_card_log');
    }
}
