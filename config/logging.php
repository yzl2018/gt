<?php

use Monolog\Handler\StreamHandler;

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['daily'],
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
        ],

		'sweeper' => [//清道夫清理日志记录
			'driver' => 'single',
			'path' => storage_path('logs/sweeper.log'),
			'level' => 'debug',
		],

		'register_activation_log' => [//已被删除的注册激活日志
			'driver' => 'single',
			'path' => storage_path('logs/deleted_register.log'),
			'level' => 'debug',
		],

		'login_log' => [//已被删除的登陆日志
			'driver' => 'single',
			'path' => storage_path('logs/deleted_login.log'),
			'level' => 'debug',
		],

		'b_notify_log' => [//已被删除的B系统通知日志
			'driver' => 'single',
			'path' => storage_path('logs/deleted_notify.log'),
			'level' => 'debug',
		],

		'crm_request_log' => [//已被删除的CRM请求日志
			'driver' => 'single',
			'path' => storage_path('logs/deleted_crm_request.log'),
			'level' => 'debug',
		],

		'user_operate_log' => [//已被删除的用户操作日志
			'driver' => 'single',
			'path' => storage_path('logs/deleted_user_operate.log'),
			'level' => 'debug',
		],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/daily.log'),
            'level' => 'debug',
            'days' => 7,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => 'critical',
        ],

        'stderr' => [
            'driver' => 'monolog',
            'handler' => StreamHandler::class,
            'with' => [
                'stream' => 'php://stderr',
            ],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => 'debug',
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => 'debug',
        ],
    ],

];
