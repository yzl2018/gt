<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCurrencyTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currency_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code',20)->comment('货币代码');
            $table->string('name_word_code',60)->comment('货币名称文字编码');
            $table->string('symbol',20)->comment('货币符号(如 ￥)');
            $table->integer('b_number')->comment('B系统对应编号');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('currency_type');
    }
}
