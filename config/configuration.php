<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Config Customer recharge card number card secret acquisition method
    |--------------------------------------------------------------------------
    |
    | 配置	客户充值卡卡号卡密获取方式
    |
    */
    'fetch_card_method'	=> [
        'key_code'	=> 'CG001',
        'key_name'		=> '切换卡号卡密获取方式的参数配置',
        'data_type'		=> 'enum',
        'default_value'	=> 0xCF001,
        'data_options'	=> [
            'both'	=> [
                'value'	=> 0xCF001,
                'name'	=> '两者都行'
            ],
            'mail_notice'	=> [
                'value'	=> 0xCF002,
                'name'	=> '邮件通知'
            ],
            'backend_view'	=> [
                'value'	=> 0xCF003,
                'name'	=> '后台查看'
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Config Delayed queue time for sending mail
    |--------------------------------------------------------------------------
    |
    | 配置	发送邮件的延迟加入队列的时间(单位:秒)
    |
    */
    'send_mail_delay_seconds'	=> [
        'key_code'	=> 'CG002',
        'key_name'		=> '发送邮件的延迟加入队列的时间(单位:秒)',
        'data_type'		=> 'int',
        'default_value'	=> 5,
        'data_options'	=> NULL
    ]

];
