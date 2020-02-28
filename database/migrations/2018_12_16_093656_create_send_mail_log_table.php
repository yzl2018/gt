<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSendMailLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('send_mail_log', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('mail_type_code')->comment('邮件类型code');
            $table->string('mail_data',500)->comment('邮寄数据参数');
            $table->string('mail_to',100)->comment('收件人');
            $table->string('mail_channel',100)->nullable()->comment('使用的邮局通道');
            $table->string('attach_path',150)->nullable()->comment('附件地址(绝对地址)');
            $table->integer('dispatch_status')->default(0)->comment('0:尚未派发，1：派发成功，-1：派发失败');
            $table->string('redis_key',150)->nullable()->comment('邮件发送队列序列码');
            $table->integer('send_status')->default(0)->comment('发送状态(0:尚未发送，1：发送成功，-1：发送失败)');
            $table->dateTime('send_time')->nullable()->comment('发送时间');
			$table->string('fail_reason',500)->nullable()->comment('发送失败的原因(默认为null)');
			$table->integer('resend_times')->default(0)->comment('补发次数');
			$table->integer('resend_result')->default(0)->comment('重发送结果(-1:发送失败,0:未发送,1:发送成功)');
			$table->string('resend_response',500)->nullable()->comment('补发邮件响应信息(默认为null)');
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
        Schema::dropIfExists('send_mail_log');
    }
}
