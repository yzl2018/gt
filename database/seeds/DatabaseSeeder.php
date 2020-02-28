<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
    	$this->call(LanguageTypeTableSeeder::class);
    	$this->call(SysLanguageWordsTableSeeder::class);
		$this->call(CodeConfigTableSeeder::class);
		$this->call(UserTypeTableSeeder::class);
		$this->call(MailTypeSeeder::class);
		$this->call(CurrencyTypeTableSeeder::class);
		$this->call(OperateTypeTableSeeder::class);
		$this->call(IndutryTableSeeder::class);
		$this->call(UsersTableSeeder::class);
		$this->call(AutoClearLogTableSeeder::class);
        $this->call(SiteLimitTradeAmountSeeder::class);
    }
}
