<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOperateTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operate_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code',20)->comment('类型代码');
            $table->string('name',60)->comment('类型名称(如 注册账号、重置密码、登陆系统、购买商品、支付订单、激活充值卡、查看重置卡密码、修改登陆密码、更新信息、补充资料、申请退款)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('operate_type');
    }
}
