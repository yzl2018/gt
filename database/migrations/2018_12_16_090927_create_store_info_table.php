<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_info', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code',60)->comment('商家店铺编码');
            $table->string('industry_code',60)->comment('所属行业编号');
            $table->string('name_word_code',60)->comment('店铺名称文字编码');
            $table->string('logo',200)->nullable()->comment('店铺logo');
            $table->string('litpic',200)->nullable()->comment('店铺门面照');
            $table->string('introduce_word_code',60)->nullable()->comment('店铺简介文字编码');
            $table->string('address_word_code',60)->nullable()->comment('店铺地址文字编码');
            $table->decimal('grade',15,1)->default(4.9)->comment('评分');
            $table->string('evaluation_word_code',60)->nullable()->comment('评价');
            $table->string('tag_label',200)->nullable()->comment('标记标签');
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
        Schema::dropIfExists('store_info');
    }
}
