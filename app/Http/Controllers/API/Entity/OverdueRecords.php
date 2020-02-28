<?php
namespace App\Http\Controllers\API\Entity;

class OverdueRecords
{

    /**
     * 表名
     *
     * @var null|string
     */
    public $table_name = null;

    /**
     * 时间字段
     *
     * @var null|string
     */
    public $time_key = "order_time_out";

    /**
     * 列名
     *
     * @var null
     */
    public $column_key = null;

    /**
     * 列值
     *
     * @var null
     */
    public $column_value = null;

    /**
     * 状态字段
     *
     * @var null
     */
    public $status_key = null;

    /**
     * PurchaseRecords constructor.
     * @param string $table_name
     * @param string $column_key
     * @param $column_value
     * @param $status_key
     */
    public function __construct(string $table_name,string $column_key,$column_value,$status_key)
    {

        $this->table_name = $table_name;
        $this->column_key = $column_key;
        $this->column_value = $column_value;
        $this->status_key = $status_key;

    }

}
