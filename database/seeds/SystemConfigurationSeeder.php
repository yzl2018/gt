<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemConfigurationSeeder extends Seeder
{

    /**
     *
     *
     * @var string
     */
    private static $table_name = 'system_configuration';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $configuration = config('configuration');
        foreach ($configuration as $key => $config){
            $count = DB::table(self::$table_name)->where('key_code',$config['key_code'])->count();
            if($count == 0){
                $time = date('Y-m-d H:i:s');
                $config['created_at'] = $config['updated_at'] = $time;
                if($config['data_options'] == null){
                    unset($config['data_options']);
                }
                else if(is_array($config['data_options'])){
                    $config['data_options'] = json_encode($config['data_options']);
                }
                DB::table(self::$table_name)->insert($config);
            }
        }

    }
}
