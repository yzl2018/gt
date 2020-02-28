<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('users_id')->comment('所属客户id');
            $table->string('users_email',100)->nullable()->comment('客户邮箱');
            $table->integer('purchase_records_id')->comment('所属购买记录id');
            $table->string('purchase_code',60)->comment('下单编码');
            $table->string('order_no',100)->comment('所属支付订单编号');
            $table->string('trade_no',100)->comment('系统流水号');
            $table->string('card_no',100)->comment('充值卡编码');
            $table->string('card_key',50)->nullable()->comment('充值卡密码');
            $table->decimal('card_value',15,2)->comment('充值卡面额');
            $table->string('currency_type_code',20)->default('CNY')->comment('货币类型编号');
            $table->integer('use_status')->default(0)->comment('充值卡使用状态(0:未使用,1:激活成功,-1:已退款)');
            $table->integer('email_notice_status')->default(0)->comment('购买成功的充值卡邮件通知客户状态(0:未通知,1:通知成功,-1:通知失败)');
            $table->integer('supply_email_times')->default(0)->comment('补发购买成功通知邮件次数');
            $table->integer('sms_notice_status')->default(0)->comment('充值卡激活成功邮件通知客户状态(0:未通知,1:通知成功,-1:通知失败)');
            $table->integer('supply_sms_times')->default(0)->comment('补发激活成功通知邮件次数');
            $table->string('merchant_code',30)->nullable()->comment('客户要充值的商户');
            $table->string('crm_order_no',60)->nullable()->comment('CRM激活请求单号');
            $table->integer('attempt_times')->default(0)->comment('尝试激活次数');
            $table->string('activation_message',200)->nullable()->comment('充值卡激活响应信息');
            $table->dateTime('success_time')->nullable()->comment('成功使用时间');
            $table->string('mail_redis_key',150)->nullable()->comment('购买成功邮件发送队列序列码');
            $table->string('sms_redis_key',150)->nullable()->comment('激活成功邮件发送队列序列码');
            $table->string('merchant_notify_url',300)->nullable()->comment('商户接收异步通知的地址');
            $table->integer('merchant_notify_status')->default(0)->comment('主动通知商户的状态(0:未通知,1:通知成功,-1:通知失败)');
            $table->integer('merchant_notify_times')->default(0)->comment('主动通知商户的次数');
            $table->string('merchant_notify_response',1000)->nullable()->comment('主动通知商户后最近返回的响应信息');
            $table->integer('allow_view')->default(0)->comment('是否允许客户在后台查看这张充值卡的密码');
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
        Schema::dropIfExists('cash_cards');
    }
}
