<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIndustryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('industry', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code',30)->comment('行业编码');
            $table->string('name_word_code',60)->nullable()->comment('名称文字编码');
            $table->string('father_code',30)->default(0)->comment('所属父类行业编码');
            $table->string('icon',200)->nullable()->comment('上传的图标地址');
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
        Schema::dropIfExists('industry');
    }
}
