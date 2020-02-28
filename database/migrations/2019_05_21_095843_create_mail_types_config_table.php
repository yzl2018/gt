<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailTypesConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_types_config', function (Blueprint $table) {
            $table->increments('id');
			$table->string('code',60)->comment('邮件类型编码');
			$table->string('name',100)->comment('邮件类型名称');
			$table->integer('view_id')->comment('邮件视图ID');
            $table->string('current_channels_group',100)->comment('当前使用的通道组');
            $table->string('prepare_channels_groups',1000)->comment('备用通道组');
			$table->string('emergency_channel',100)->comment('邮局应急通道（最好确保这个通道一定能正常发送）');
            $table->integer('can_be_send')->default(1)->comment('此类邮件发送开关(0：禁止发送，1：允许发送)');
            $table->integer('delay_send_seconds')->default(0)->comment('延迟发送时间 单位/秒 (默认为0)');
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
        Schema::dropIfExists('mail_types_config');
    }
}
