<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemConfigurationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_configuration', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key_code',60)->comment('参数键编码:编码方式 类型名+参数名+编号');
            $table->string('key_name',300)->comment('参数键名称');
            $table->string('default_value',500)->comment('参数键值');
            $table->string('data_type',45)->comment('参数数据类型');
            $table->string('data_options',1000)->nullable()->comment('可选参数值');
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
        Schema::dropIfExists('system_configuration');
    }
}
