<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoginLogTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('login_log', function (Blueprint $table) {
            $table->increments('id');
            $table->ipAddress('client_ip')->comment('客户端IP');
            $table->string('ip_address',256)->nullable()->comment('ip归属地');
            $table->string('login_user',60)->comment('登陆用户(邮箱/手机)');
            $table->dateTime('login_at')->comment('登陆时间');
			$table->integer('response_code')->default(0)->comment('登陆响应状态码');
			$table->string('response_message',512)->nullable()->comment('登陆响应信息');
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
        Schema::dropIfExists('login_log');
    }
}
