<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegisterAuthorizationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('register_authorization', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code',100)->comment('注册授权码');
            $table->integer('status')->default(0)->comment('使用状态，0：未使用，1：已使用');
            $table->dateTime('expires_time')->comment('授权码失效时间');
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
        Schema::dropIfExists('register_authorization');
    }
}
