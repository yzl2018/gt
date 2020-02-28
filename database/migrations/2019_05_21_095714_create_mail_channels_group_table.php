<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailChannelsGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_channels_group', function (Blueprint $table) {
            $table->increments('id');
			$table->string('code',60)->comment('通道组编码');
			$table->string('name',100)->comment('通道组简称');
			$table->string('include_channels',1000)->comment('所含通道');
			$table->integer('repeat_times')->default(1)->comment('单个通道一个轮询重复使用次数');
			$table->string('using_channel',60)->comment('正在使用的通道');
			$table->integer('status')->default(0)->comment('通道组状态(0：未满，1：已满，-1：无可用的通道)');
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
        Schema::dropIfExists('mail_channels_group');
    }
}
