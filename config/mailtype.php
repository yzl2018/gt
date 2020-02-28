<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment mail config
    |--------------------------------------------------------------------------
    |
    | 支付成功后的充值卡通知 邮件配置
    |
    */
    0xE001	=> [
        'channel'		=> 'fxpointcard',//邮件发送通道
        'view'			=> 'emails.shopping',//邮件视图
        'subject'		=> '号码 密码 邮件',//邮件主题
        'parameters'	=> ['Vouchers','Value','Currency'],//邮件数据参数
        'update_table'	=> [
            'table_name'	=> 'cash_cards',
            'key_name'		=> 'mail_redis_key',
            'value_name'	=> 'email_notice_status'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment mail config
    |--------------------------------------------------------------------------
    |
    | 支付成功后的充值卡通知 邮件配置
    |
    */
    0xE002	=> [
        'channel'		=> 'fxrefillcard',//邮件发送通道
        'view'			=> 'emails.shopping',//邮件视图
        'subject'		=> '号码 密码 邮件',//邮件主题
        'parameters'	=> ['Vouchers','Value','Currency'],//邮件数据参数
        'update_table'	=> [
            'table_name'	=> 'cash_cards',
            'key_name'		=> 'mail_redis_key',
            'value_name'	=> 'email_notice_status'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment mail config
    |--------------------------------------------------------------------------
    |
    | 支付成功后的充值卡通知 邮件配置
    |
    */
    0xE003	=> [
        'channel'		=> 'forexacard',//邮件发送通道
        'view'			=> 'emails.shopping',//邮件视图
        'subject'		=> '号码 密码 邮件',//邮件主题
        'parameters'	=> ['Vouchers','Value','Currency'],//邮件数据参数
        'update_table'	=> [
            'table_name'	=> 'cash_cards',
            'key_name'		=> 'mail_redis_key',
            'value_name'	=> 'email_notice_status'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment mail config
    |--------------------------------------------------------------------------
    |
    | 支付成功后的充值卡通知 邮件配置
    |
    */
    0xE004	=> [
        'channel'		=> 'forexbcard',//邮件发送通道
        'view'			=> 'emails.shopping',//邮件视图
        'subject'		=> '号码 密码 邮件',//邮件主题
        'parameters'	=> ['Vouchers','Value','Currency'],//邮件数据参数
        'update_table'	=> [
            'table_name'	=> 'cash_cards',
            'key_name'		=> 'mail_redis_key',
            'value_name'	=> 'email_notice_status'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment mail config
    |--------------------------------------------------------------------------
    |
    | 支付成功后的充值卡通知 邮件配置
    |
    */
    0xE005	=> [
        'channel'		=> 'fxcarda',//邮件发送通道
        'view'			=> 'emails.shopping',//邮件视图
        'subject'		=> '号码 密码 邮件',//邮件主题
        'parameters'	=> ['Vouchers','Value','Currency'],//邮件数据参数
        'update_table'	=> [
            'table_name'	=> 'cash_cards',
            'key_name'		=> 'mail_redis_key',
            'value_name'	=> 'email_notice_status'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Active mail config
    |--------------------------------------------------------------------------
    |
    | 客户消费兑换券 邮件配置
    |
    */
    0xE006	=> [
        'channel'		=> 'info_fxpointcard',//邮件发送通道
        'view'			=> 'emails.convert',//邮件视图
        'subject'		=> '客户消费兑换券邮件',//邮件主题
        'parameters'	=> ['VoucherNo','VoucherValue','Currency'],//邮件数据参数
        'update_table'	=> [
            'table_name'	=> 'cash_cards',
            'key_name'		=> 'sms_redis_key',
            'value_name'	=> 'sms_notice_status'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Active mail config
    |--------------------------------------------------------------------------
    |
    | 客户消费兑换券 邮件配置
    |
    */
    0xE007	=> [
        'channel'		=> 'system_fxpointcard',//邮件发送通道
        'view'			=> 'emails.convert',//邮件视图
        'subject'		=> '客户消费兑换券邮件',//邮件主题
        'parameters'	=> ['VoucherNo','VoucherValue','Currency'],//邮件数据参数
        'update_table'	=> [
            'table_name'	=> 'cash_cards',
            'key_name'		=> 'sms_redis_key',
            'value_name'	=> 'sms_notice_status'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Register mail config
    |--------------------------------------------------------------------------
    |
    | 注册激活和成功 邮件配置
    |
    */
    0xE010		=>[
        'channel'		=> 'fxcarda',//邮件发送通道
        'view'			=> 'emails.authcode',//邮件视图
        'subject'		=> '发送验证码邮件',//邮件主题
        'parameters'	=> ['Address','RegisterTime'],//邮件数据参数
        'update_table'	=> null
    ],
    0xE011		=> [
        'channel'		=> 'lighthouse',//邮件发送通道
        'view'			=> 'emails.success',//邮件视图
        'subject'		=> '激活成功邮件',//邮件主题
        'parameters'	=> ['Email','LoginPassword','OperatePassword'],//邮件数据参数
        'update_table'	=> null
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment mail config
    |--------------------------------------------------------------------------
    |
    | 支付成功后的充值卡通知 邮件配置
    |
    */
    0xE012	=> [
        'channel'		=> 'fxrefillcard',//邮件发送通道
        'view'			=> 'emails.shopping',//邮件视图
        'subject'		=> '号码 密码 邮件',//邮件主题
        'parameters'	=> ['Vouchers','Value','Currency'],//邮件数据参数
        'update_table'	=> [
            'table_name'	=> 'cash_cards',
            'key_name'		=> 'mail_redis_key',
            'value_name'	=> 'email_notice_status'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Active mail config
    |--------------------------------------------------------------------------
    |
    | 客户消费兑换券 邮件配置
    |
    */
    0xE013	=> [
        'channel'		=> 'crmcloud',//邮件发送通道
        'view'			=> 'emails.convert',//邮件视图
        'subject'		=> '客户消费兑换券邮件',//邮件主题
        'parameters'	=> ['VoucherNo','VoucherValue','Currency'],//邮件数据参数
        'update_table'	=> [
            'table_name'	=> 'cash_cards',
            'key_name'		=> 'sms_redis_key',
            'value_name'	=> 'sms_notice_status'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Active mail config
    |--------------------------------------------------------------------------
    |
    | 操作认证码 邮件配置
    |
    */
    0xE014	=> [
        'channel'		=> 'default',//邮件发送通道
        'view'			=> 'emails.authcode',//邮件视图
        'subject'		=> '操作认证码邮件',//邮件主题
        'parameters'	=> ['type_name','code','expires_time'],//邮件数据参数
        'update_table'	=> null
    ],

    /*
    |--------------------------------------------------------------------------
    | Active mail config
    |--------------------------------------------------------------------------
    |
    | 操作认证码 邮件配置
    |
    */
    0xE015	=> [
        'channel'		=> 'lighthouse',//邮件发送通道
        'view'			=> 'emails.authcode',//邮件视图
        'subject'		=> '操作认证码邮件',//邮件主题
        'parameters'	=> ['type_name','code','expires_time'],//邮件数据参数
        'update_table'	=> null
    ],

    /*
    |--------------------------------------------------------------------------
    | Active mail config
    |--------------------------------------------------------------------------
    |
    | 操作认证码 邮件配置
    |
    */
    0xE016	=> [
        'channel'		=> 'fxpointcard',//邮件发送通道
        'view'			=> 'emails.authcode',//邮件视图
        'subject'		=> '操作认证码邮件',//邮件主题
        'parameters'	=> ['type_name','code','expires_time'],//邮件数据参数
        'update_table'	=> null
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment mail config
    |--------------------------------------------------------------------------
    |
    | 支付成功后的充值卡通知 邮件配置
    |
    */
    0xE020	=> [
        'channel'		=> 'info_fxpointcard',//邮件发送通道
        'view'			=> 'emails.shopping',//邮件视图
        'subject'		=> '号码 密码 邮件',//邮件主题
        'parameters'	=> ['Vouchers','Value','Currency'],//邮件数据参数
        'update_table'	=> [
            'table_name'	=> 'cash_cards',
            'key_name'		=> 'mail_redis_key',
            'value_name'	=> 'email_notice_status'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment mail config
    |--------------------------------------------------------------------------
    |
    | 支付成功后的充值卡通知 邮件配置
    |
    */
    0xE021	=> [
        'channel'		=> 'info_fxrefillcard',//邮件发送通道
        'view'			=> 'emails.shopping',//邮件视图
        'subject'		=> '号码 密码 邮件',//邮件主题
        'parameters'	=> ['Vouchers','Value','Currency'],//邮件数据参数
        'update_table'	=> [
            'table_name'	=> 'cash_cards',
            'key_name'		=> 'mail_redis_key',
            'value_name'	=> 'email_notice_status'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment mail config
    |--------------------------------------------------------------------------
    |
    | 支付成功后的充值卡通知 邮件配置
    |
    */
    0xE022	=> [
        'channel'		=> 'info_forexacard',//邮件发送通道
        'view'			=> 'emails.shopping',//邮件视图
        'subject'		=> '号码 密码 邮件',//邮件主题
        'parameters'	=> ['Vouchers','Value','Currency'],//邮件数据参数
        'update_table'	=> [
            'table_name'	=> 'cash_cards',
            'key_name'		=> 'mail_redis_key',
            'value_name'	=> 'email_notice_status'
        ]
    ],

    /*
   |--------------------------------------------------------------------------
   | Payment mail config
   |--------------------------------------------------------------------------
   |
   | 支付成功后的充值卡通知 邮件配置
   |
   */
    0xE023	=> [
        'channel'		=> 'info_forexbcard',//邮件发送通道
        'view'			=> 'emails.shopping',//邮件视图
        'subject'		=> '号码 密码 邮件',//邮件主题
        'parameters'	=> ['Vouchers','Value','Currency'],//邮件数据参数
        'update_table'	=> [
            'table_name'	=> 'cash_cards',
            'key_name'		=> 'mail_redis_key',
            'value_name'	=> 'email_notice_status'
        ]
    ],

    /*
   |--------------------------------------------------------------------------
   | Payment mail config
   |--------------------------------------------------------------------------
   |
   | 支付成功后的充值卡通知 邮件配置
   |
   */
    0xE024	=> [
        'channel'		=> 'info_fxcarda',//邮件发送通道
        'view'			=> 'emails.shopping',//邮件视图
        'subject'		=> '号码 密码 邮件',//邮件主题
        'parameters'	=> ['Vouchers','Value','Currency'],//邮件数据参数
        'update_table'	=> [
            'table_name'	=> 'cash_cards',
            'key_name'		=> 'mail_redis_key',
            'value_name'	=> 'email_notice_status'
        ]
    ],

    /*
   |--------------------------------------------------------------------------
   | Payment mail config
   |--------------------------------------------------------------------------
   |
   | 支付成功后的充值卡通知 邮件配置
   |
   */
    0xE025	=> [
        'channel'		=> 'system_fxpointcard',//邮件发送通道
        'view'			=> 'emails.shopping',//邮件视图
        'subject'		=> '号码 密码 邮件',//邮件主题
        'parameters'	=> ['Vouchers','Value','Currency'],//邮件数据参数
        'update_table'	=> [
            'table_name'	=> 'cash_cards',
            'key_name'		=> 'mail_redis_key',
            'value_name'	=> 'email_notice_status'
        ]
    ],

    /*
   |--------------------------------------------------------------------------
   | Payment mail config
   |--------------------------------------------------------------------------
   |
   | 支付成功后的充值卡通知 邮件配置
   |
   */
    0xE026	=> [
        'channel'		=> 'system_fxrefillcard',//邮件发送通道
        'view'			=> 'emails.shopping',//邮件视图
        'subject'		=> '号码 密码 邮件',//邮件主题
        'parameters'	=> ['Vouchers','Value','Currency'],//邮件数据参数
        'update_table'	=> [
            'table_name'	=> 'cash_cards',
            'key_name'		=> 'mail_redis_key',
            'value_name'	=> 'email_notice_status'
        ]
    ],

    /*
  |--------------------------------------------------------------------------
  | Payment mail config
  |--------------------------------------------------------------------------
  |
  | 支付成功后的充值卡通知 邮件配置
  |
  */
    0xE027	=> [
        'channel'		=> 'system_forexacard',//邮件发送通道
        'view'			=> 'emails.shopping',//邮件视图
        'subject'		=> '号码 密码 邮件',//邮件主题
        'parameters'	=> ['Vouchers','Value','Currency'],//邮件数据参数
        'update_table'	=> [
            'table_name'	=> 'cash_cards',
            'key_name'		=> 'mail_redis_key',
            'value_name'	=> 'email_notice_status'
        ]
    ],

    /*
  |--------------------------------------------------------------------------
  | Payment mail config
  |--------------------------------------------------------------------------
  |
  | 支付成功后的充值卡通知 邮件配置
  |
  */
    0xE028	=> [
        'channel'		=> 'system_forexbcard',//邮件发送通道
        'view'			=> 'emails.shopping',//邮件视图
        'subject'		=> '号码 密码 邮件',//邮件主题
        'parameters'	=> ['Vouchers','Value','Currency'],//邮件数据参数
        'update_table'	=> [
            'table_name'	=> 'cash_cards',
            'key_name'		=> 'mail_redis_key',
            'value_name'	=> 'email_notice_status'
        ]
    ],

        /*
     |--------------------------------------------------------------------------
     | Payment mail config
     |--------------------------------------------------------------------------
     |
     | 支付成功后的充值卡通知 邮件配置
     |
     */
    0xE029	=> [
        'channel'		=> 'system_fxcarda',//邮件发送通道
        'view'			=> 'emails.shopping',//邮件视图
        'subject'		=> '号码 密码 邮件',//邮件主题
        'parameters'	=> ['Vouchers','Value','Currency'],//邮件数据参数
        'update_table'	=> [
            'table_name'	=> 'cash_cards',
            'key_name'		=> 'mail_redis_key',
            'value_name'	=> 'email_notice_status'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment mail config
    |--------------------------------------------------------------------------
    |
    | 支付成功后的充值卡通知 邮件配置
    |
    */
    0xE030	=> [
        'channel'		=> 'lighthouse',//邮件发送通道
        'view'			=> 'emails.shopping',//邮件视图
        'subject'		=> '号码 密码 邮件',//邮件主题
        'parameters'	=> ['Vouchers','Value','Currency'],//邮件数据参数
        'update_table'	=> [
            'table_name'	=> 'cash_cards',
            'key_name'		=> 'mail_redis_key',
            'value_name'	=> 'email_notice_status'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment mail config
    |--------------------------------------------------------------------------
    |
    | 支付成功后的充值卡通知 邮件配置
    |
    */
    0xE031	=> [
        'channel'		=> 'crmcloud',//邮件发送通道
        'view'			=> 'emails.shopping',//邮件视图
        'subject'		=> '号码 密码 邮件',//邮件主题
        'parameters'	=> ['Vouchers','Value','Currency'],//邮件数据参数
        'update_table'	=> [
            'table_name'	=> 'cash_cards',
            'key_name'		=> 'mail_redis_key',
            'value_name'	=> 'email_notice_status'
        ]
    ],
	
	/*
    |--------------------------------------------------------------------------
    | Payment mail config
    |--------------------------------------------------------------------------
    |
    | 支付成功后的充值卡通知 邮件配置
    |
    */
    0xE036	=> [
        'channel'		=> 'forexbcard',//邮件发送通道
        'view'			=> 'emails.invoice',//邮件视图
        'subject'		=> 'Invoice 邮件',//邮件主题
        'parameters'	=> ["OrderNo","Value","Currency","CustomerEmail","ProName","ProPrice","ProNumber"],//邮件数据参数
        'update_table'	=> null
    ],

];
