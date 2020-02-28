<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAutoClearLogConfigTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auto_clear_log_config', function (Blueprint $table) {
            $table->increments('id');
            $table->string('log_table_name',100)->comment('日志表名');
            $table->string('date_key',50)->default('created_at')->comment('日期键名');
            $table->integer('delete_moment_limit_days')->default(15)->comment('软删除期限(1,2,3... 以天为单位，默认15天)/硬删除期限必须大于或等于软删除期限');
            $table->integer('delete_forever_limit_days')->default(60)->comment('硬删除期限(1,2,3... 以天为单位，默认60天)/硬删除期限必须大于或等于软删除期限');
            $table->integer('delete_type')->default(0)->comment('使用的清理类型(-1:只执行硬删除,0:软删除和硬删除均执行,1:只执行软删除)');
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
        Schema::dropIfExists('auto_clear_log_config');
    }
}
