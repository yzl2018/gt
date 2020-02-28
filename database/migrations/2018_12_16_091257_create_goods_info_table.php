<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_info', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code',60)->comment('商品编码');
            $table->string('store_info_code',60)->comment('所属商家店铺编号');
            $table->string('name_word_code',60)->comment('商品名称文字编码');
            $table->decimal('original_price',15,2)->default(0)->comment('原价');
            $table->decimal('price',15,2)->comment('单价');
            $table->string('currency_type_code',30)->comment('货币类型编号');
            $table->string('litpic',200)->nullable()->comment('缩略图');
            $table->integer('buy_limit')->default(1)->comment('购买数量下限');
            $table->integer('buy_stop')->default(100)->comment('购买数量上限');
            $table->string('features_word_code',60)->nullable()->comment('商品特点文字编码');
            $table->string('introduce_word_code',60)->nullable()->comment('商品介绍文字编码');
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
        Schema::dropIfExists('goods_info');
    }
}
