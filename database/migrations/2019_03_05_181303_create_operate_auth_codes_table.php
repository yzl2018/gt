<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOperateAuthCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operate_auth_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_name',100)->comment('用户主体');
            $table->string('auth_code',45)->comment('操作认证码');
            $table->integer('operate_type')->comment('操作类型');
            $table->integer('status')->default(0)->comment('使用状态，0：未使用，1：已使用');
            $table->dateTime('expires_time')->comment('认证码失效时间');
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
        Schema::dropIfExists('operate_auth_codes');
    }
}
