<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserOperateLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_operate_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('users_id')->comment('用户id');
			$table->ipAddress('client_ip')->comment('客户ip');
            $table->string('ip_address',200)->nullable()->comment('ip所在地');
            $table->string('operate_type_code',30)->comment('操作类型代码');
            $table->string('api',150)->comment('操作接口');
            $table->string('method',20)->comment('请求方式');
            $table->string('parameters',2000)->nullable()->comment('请求参数');
            $table->dateTime('time')->comment('操作时间');
			$table->string('response',2000)->nullable()->comment('用户操作响应');
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
        Schema::dropIfExists('user_operate_log');
    }
}
