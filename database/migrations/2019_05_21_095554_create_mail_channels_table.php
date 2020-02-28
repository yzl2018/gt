<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_channels', function (Blueprint $table) {
            $table->increments('id');
			$table->string('code',60)->comment('通道编码');
            $table->string('name',100)->comment('通道简称');
            $table->string('driver',45)->comment('邮局驱动');
            $table->string('host',200)->comment('邮局地址');
            $table->string('port',45)->comment('邮局端口');
            $table->string('encryption',45)->comment('加密协议');
            $table->string('username',200)->comment('用户名');
            $table->string('password',200)->comment('密码');
            $table->string('stream',300)->nullable()->comment('协议认证方式(false表示忽略)');
            $table->integer('daily_send_limit')->comment('每日通道发送邮件数量上限');
            $table->integer('daily_send_times')->default(0)->comment('当日发送邮件数量');
            $table->integer('send_total')->default(0)->comment('发送邮件总数量');
            $table->integer('enabled')->default(0)->comment('该通道是否可用(0：待启用[只能自动启用]，1：启用成功[若发送失败，则自动设置为启用异常]，2：启用异常[自动检测，若能发送，则自动设置为启用成功]，-1：禁止启用[一旦被设置为禁止启用则不能用作发送，除非恢复启用，只能恢复为0])');
            $table->string('remarks',500)->nullable()->comment('状态变更备注');
			$table->string('queue_key',200)->comment('该邮局通道所使用的队列');
			$table->date('send_date')->default('2019-01-01')->comment('通道最近发送日期');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mail_channels');
    }
}
