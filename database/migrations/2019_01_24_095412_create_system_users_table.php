<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_users', function (Blueprint $table) {
			$table->increments('id');
			$table->ipAddress('sys_ip')->comment('外部访问系统IP');
			$table->string('header_token_name',100)->comment('访问头名称');
			$table->string('ip_address',256)->nullable()->comment('系统ip归属地');
			$table->string('authorization_code',100)->comment('授权码');
			$table->string('communication_key',150)->comment('通信密钥');
			$table->integer('status')->default(1)->comment('通信连接状态:0 禁止连接,1 允许连接');
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
        Schema::dropIfExists('system_users');
    }
}
