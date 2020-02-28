<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegisterActivationLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('register_activation_log', function (Blueprint $table) {
            $table->increments('id');
            $table->ipAddress('client_ip')->comment('客户端IP');
            $table->string('ip_address',200)->comment('ip所在地');
            $table->string('parameters',500)->nullable()->comment('注册请求参数');
            $table->integer('response_code')->default(0)->comment('注册响应状态码');
            $table->string('response_message',256)->nullable()->comment('注册响应信息');
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
        Schema::dropIfExists('register_activation_log');
    }
}
