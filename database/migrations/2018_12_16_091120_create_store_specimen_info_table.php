<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreSpecimenInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_specimen_info', function (Blueprint $table) {
            $table->increments('id');
            $table->string('store_info_code',60)->comment('所属店铺编号');
            $table->string('name_word_code',60)->comment('样品名称文字编码');
            $table->string('title_word_code',60)->nullable()->comment('样品标语文字编码');
            $table->string('features_word_code',60)->nullable()->comment('样品特色文字编码');
            $table->string('photo',200)->comment('样品图片');
            $table->string('introduce_word_code',60)->nullable()->comment(':样品介绍文字编码');
            $table->integer('sort_number')->default(0)->comment('排序序号');
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
        Schema::dropIfExists('store_specimen_info');
    }
}
