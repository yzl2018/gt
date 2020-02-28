<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBNotifyLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('b_notify_log', function (Blueprint $table) {
            $table->increments('id');
            $table->ipAddress('sys_ip')->comment('B系统ip');
            $table->string('ip_address',200)->nullable()->comment('ip所在地');
            $table->string('api',100)->comment('访问的接口');
            $table->string('method',20)->comment('通知方式');
            $table->text('parameters')->comment('通知参数');
            $table->dateTime('time')->comment('通知时间');
            $table->string('response_result',256)->nullable()->comment('通知响应结果');
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
        Schema::dropIfExists('b_notify_log');
    }
}
