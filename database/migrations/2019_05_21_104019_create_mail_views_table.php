<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_views', function (Blueprint $table) {
            $table->increments('id');
			$table->string('subject',200)->comment('邮件主题');
			$table->string('view',100)->comment('邮件视图');
			$table->string('parameters',500)->comment('邮件数据参数');
			$table->string('update_table',500)->nullable()->comment('补充更新对应表的邮件发送状态');
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
        Schema::dropIfExists('mail_views');
    }
}
