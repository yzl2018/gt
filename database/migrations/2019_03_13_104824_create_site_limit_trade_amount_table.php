<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSiteLimitTradeAmountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('site_limit_trade_amount', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code',30)->comment('站点编码');
            $table->string('domain_name',150)->comment('域名');
            $table->string('web_site',150)->comment('网址');
            $table->string('currency_type',30)->default('CNY')->comment('可交易的商铺货币类型 enum(ALL,USD,CNY) [ALL代表所有货币类型]');
            $table->integer('cny_buy_limit')->default(3000)->comment('人民币最低限价交易金额');
            $table->integer('cny_buy_stop')->default(499000)->comment('人民币最高限价交易金额');
            $table->integer('usd_buy_limit')->default(500)->comment('美元最低限价交易金额');
            $table->integer('usd_buy_stop')->default(71285)->comment('美元最高限价交易金额');
            $table->integer('enable_status')->default(1)->comment('站点可用状态 (1：可用，-1：禁用)');
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
        Schema::dropIfExists('site_limit_trade_amount');
    }
}
