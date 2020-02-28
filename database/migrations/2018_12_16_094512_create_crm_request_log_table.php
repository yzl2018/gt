<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCrmRequestLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crm_request_log', function (Blueprint $table) {
            $table->increments('id');
            $table->ipAddress('ip')->comment('请求IP');
            $table->string('ip_address',200)->nullable()->comment('ip所在地');
            $table->string('api',100)->comment('请求接口');
            $table->string('method',20)->comment('请求方式');
            $table->string('parameters',1000)->comment('请求参数');
            $table->dateTime('request_time')->comment('请求时间');
            $table->string('merchant_code',50)->nullable()->comment('请求的商户号');
            $table->string('crm_order_no',100)->nullable()->comment('激活单号(允许为空)');
            $table->string('content_type',100)->nullable()->comment('请求数据类型');
            $table->string('data_from',60)->nullable()->comment('从哪获取到数据');
            $table->string('sign_data',500)->nullable()->comment('加密数据');
            $table->dateTime('response_time')->nullable()->comment('响应时间');
            $table->string('result',500)->nullable()->comment('响应结果');
            $table->string('response',2000)->nullable()->comment('加密后的响应信息');
            $table->dateTime('updated_at')->nullable()->comment('更新时间');
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
        Schema::dropIfExists('crm_request_log');
    }
}
