<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
			$table->string('code',60)->comment('用户编码');
            $table->string('name',100)->nullable()->comment('注册的昵称');
            $table->string('email',100)->comment('注册的邮箱');
            $table->string('phone',30)->comment('注册的手机');
            $table->string('password',200)->comment('登陆密码');
            $table->string('operate_password',200)->nullable()->comment('操作/支付密码');
            $table->string('user_type_code',20)->nullable()->comment('用户类型编码');
            $table->string('user_code',60)->nullable()->comment('B中用户唯一编码');
            $table->string('safety_code',60)->nullable()->comment('用户安全码');
            $table->integer('login_fail_times')->default(0)->comment('登录失败次数');
            $table->integer('active_status')->default(0)->comment('激活状态(默认为0,只有激活成功了才将此值设置为1并允许登陆)');
            $table->string('language_type_code',20)->default('CN')->comment('使用的语言编码(默认中文)');
            $table->string('latest_login_time',60)->comment('用户最近登陆时间戳');
            $table->string('site_code',60)->nullable()->comment('站点编码');
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
        Schema::dropIfExists('users');
    }
}
