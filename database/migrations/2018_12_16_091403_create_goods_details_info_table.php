<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsDetailsInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_details_info', function (Blueprint $table) {
            $table->increments('id');
            $table->string('goods_info_code',60)->comment('所属商品编码');
            $table->string('title_word_code',60)->comment('详情标语文字编码');
            $table->string('image',200)->comment('详情图片');
            $table->string('information_word_code',60)->nullable()->comment('详情信息文字编码');
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
        Schema::dropIfExists('goods_details_info');
    }
}
