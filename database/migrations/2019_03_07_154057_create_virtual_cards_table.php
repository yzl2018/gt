<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCardsInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('virtual_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code',60)->comment('充值卡编码');
            $table->string('name_word_code',60)->comment('充值卡名称编码');
            $table->decimal('price',15,2)->comment('单价');
            $table->string('currency_type_code',30)->comment('货币类型编号');
            $table->string('litpic',150)->nullable()->comment('缩略图');
            $table->integer('buy_limit')->default(1)->comment('购买数量下限');
            $table->integer('buy_stop')->default(100)->comment('购买数量上限');
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
        Schema::dropIfExists('cards_info');
    }
}
