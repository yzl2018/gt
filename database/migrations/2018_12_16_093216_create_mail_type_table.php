<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mail_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code',20)->comment('类型编码');
            $table->string('name',60)->comment('邮件类型名称');
            $table->string('config',100)->comment('邮件类型配置');
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
        Schema::dropIfExists('mail_type');
    }
}
