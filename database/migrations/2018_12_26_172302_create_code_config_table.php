<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCodeConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('code_config', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',50)->comment('编码代称');
            $table->string('prefix',20)->comment('编码前缀');
            $table->integer('random_bits')->default(0)->comment('编码随机位数');
            $table->integer('code_bits')->default(4)->comment('编码位数');
            $table->integer('start_val')->default(100)->comment('编码起始值');
            $table->integer('latest_val')->comment('最新编码值');
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
        Schema::dropIfExists('code_config');
    }
}
