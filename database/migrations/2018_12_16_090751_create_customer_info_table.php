<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_info', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('users_id')->comment('用户ID');
            $table->string('profile_photo',150)->nullable()->comment('用户头像');
            $table->string('bank_name',30)->nullable()->comment('银行名称');
            $table->string('bank_account',50)->nullable()->comment('银行账号');
            $table->string('card_holder',20)->nullable()->comment('银行卡持有者');
            $table->string('id_card_number',50)->nullable()->comment('身份证号码');
            $table->string('card_photo',100)->nullable()->comment('银行卡图片');
            $table->string('id_card_front',100)->nullable()->comment('身份证正面');
            $table->string('id_card_behind',100)->nullable()->comment('身份证反面');
            $table->float('complete_percent',8,2)->default(0.3)->comment('资料完整程度百分比(该值在0到1之间)');
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
        Schema::dropIfExists('customer_info');
    }
}
