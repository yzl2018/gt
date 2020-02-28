<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePhoneCodeRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('phone_code_records', function (Blueprint $table) {
            $table->increments('id');
            $table->string('phone',50)->comment('手机号码');
            $table->ipAddress('client_ip')->comment('验证的客户端IP');
            $table->string('verify_code',30)->comment('验证码');
            $table->string('input_code',30)->nullable()->comment('客户输入的验证码');
            $table->integer('verify_status')->default(0)->comment('验证状态(0:未验证,1:验证成功,-1验证失败)');
            $table->integer('fail_times')->default(0)->comment('验证失败次数');
            $table->string('fail_reason')->nullable()->comment('验证失败的原因');
            $table->dateTime('created_at')->comment('生成时间');
            $table->dateTime('expired_at')->comment('过期时间');
            $table->dateTime('verified_at')->nullable()->comment('验证时间');
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
        Schema::dropIfExists('phone_code_records');
    }
}
