<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSysLanguageWordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_language_words', function (Blueprint $table) {
            $table->increments('id');
            $table->string('word_code',30)->comment('单词编码');
            $table->string('language_type_code',20)->comment('语言编码');
            $table->string('word',2000)->comment('单词');
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
        Schema::dropIfExists('sys_language_words');
    }
}
