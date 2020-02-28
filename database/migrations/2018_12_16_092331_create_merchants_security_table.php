<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMerchantsSecurityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('merchants_security', function (Blueprint $table) {
            $table->increments('id');
            $table->string('merchant_code',30)->comment('商户编号');
            $table->string('user_code',30)->comment('用户编号');
            $table->string('security_key',100)->comment('加密密钥');
            $table->string('security_salt',60)->comment('加密盐值');
            $table->integer('status')->default(1)->comment('可使用状态');
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
        Schema::dropIfExists('merchants_security');
    }
}
