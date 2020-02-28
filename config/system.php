<?php

return [

	/*
    |--------------------------------------------------------------------------
    | Mall config
    |--------------------------------------------------------------------------
    |
    | 配置	商城名称、商城编码、商城真实地址、商城测试地址
    |
    */
	'mall'	=>	[
        'server_root'	=> 'http://id1.gsdtechs.com',
		'name'		=> 'GTECH 商城',
        'short'		=> 'GTECH',//商城简称
		'code'		=> 0xA07,//2567
		'access_safety_code'	=> '$dsgb3e7r9gh4385jsv98*93j4ygt98d9fb8uoi&&@HUrve0rt',//外部系统访问安全码
		'language'	=> 'CN',//商城系统默认使用的语言
		'phone_code_expire_time'	=> 5 * 60,//手机验证码过期时间 5 分钟
		'phone_code_fail_times'		=> 3,//手机验证码 最大验证失败次数
		'live_url'	=> 'http://id1.gsdtechs.com',
		'demo_url'	=> 'http://id1.gsdtechs.com',
		'word_pre'	=> ':',//文字代号  4位编号数字
		'user_pre'	=> 'U',//用户代号	4位商城编号+6位用户编号数字
		'industry_pre'	=> 'I',//行业代号	3位编号数字
		'store_pre'	=> 'S',//商家代号	3位编号数字
		'goods_pre'	=> 'G',//商品代号	4位编号数字
        'purchase_pre'	=> 'P',//下单代号	7位编号数字
		'order_pre'	=> 'O',//订单代号	2位随机数+7位编号数字

		'date_range'	=> 20,//获取数据的天数范围 单位/天 表示默认获取最近多少天以内的数据

		//订单号生成规则 9位用户编号-3位商家编号-4位商品编号-11位订单编号
		'order_no'	=> 'users.code+store_info.code+goods_info.code',
		'front_url'	=> '/api/gateway/callback',
		'back_url'	=> '/api/gateway/notify',
		'do_pay_uri' => '/payment/dopay',
		'do_active'	=> '/api/v1/gateway/active-b',
		'success_page'	=> 'http://id1.gsdtechs.com/user_index.html#!/myVoucher',
		'fail_page'		=> 'https://www.baidu.com',
		'test_active_uri'	=> '/api/demo/active',
		'md5_sec_key'	=> 'dfghrthy35ge3yh465756gbhrth56u23567gd',

        'cookie_expires_time'	=> 3600*6,//前端浏览器Cookie过期时间
		'overdue_delay_minutes'	=> 40,//延迟执行过期任务，以分钟计

        'store_newest_limit'	=> 4,//获取最新店铺的个数
        'goods_newest_limit'	=> 8,//获取最新商品的个数
        'email_notice_limit'	=> 2,//补发购买成功通知邮件次数上限
        'sms_notice_limit'		=> 2,//补发激活成功通知邮件次数上限
        'send_mail_delay_seconds'		=> 2,//发送邮件的延迟加入队列的时间(单位:秒)
		'send_card_key_mail_delay_seconds'	=> 120,//发送卡号卡密的邮件延迟加入队列的时间(单位:秒)
        'receive_url'	=> '/api/gateway/active-notify',//商城接收上游异步激活成功通知的接口

		'allow_currency_types'	=> ['CNY'],//该商城允许交易的货币类型
	],

    /*
    |--------------------------------------------------------------------------
    | Domain trade amount config
    |--------------------------------------------------------------------------
    |
    | 配置	域名交易门槛
    |
    */
    'domain'	=> [
		'site_to_code'	=> [
			'default'       => 'S004',
			'my.lu-mo.com'  => 'S001',
			'my.lumopr.com'	=> 'S002',
			'my.lumooo.com'	=> 'S003',
			'id1.gsdtechs.com'	=> 'S004',
			'id1.gsdtechz.com'	=> 'S005',
			'id1.gsdtechx.com'	=> 'S006',
			'id1.gsdtechc.com'	=> 'S007'
		],
		'code_to_site'	=> [
			'default'   => 'http://id1.gsdtechs.com',
			'S001'  => 'http://my.lu-mo.com',
			'S002'	=> 'http://my.lumopr.com',
			'S003'	=> 'http://my.lumooo.com',
			'S004'	=> 'http://id1.gsdtechs.com',
			'S005'	=> 'http://id1.gsdtechz.com',
			'S006'	=> 'http://id1.gsdtechx.com',
			'S007'	=> 'http://id1.gsdtechc.com',
		],
		'limit_trade_amount'	=> [
			'default'   => 3000,
			'S001'      => 3000,
			'S002'		=> 5000,
			'S003'		=> 4000,
			'S004'		=> 3000,
			'S005'		=> 5000,
			'S006'		=> 4000,
			'S007'		=> 100,
		],
		'secondary_to_main'	=> [
			'my.lu-mo.com'	=> 'http://www.lu-mo.com',
			'my.lumopr.com'	=> 'http://www.lumopr.com',
			'my.lumooo.com'	=> 'http://www.lumooo.com',
			'id1.gsdtechs.com'	=> 'http://www.gsdtechs.com',
			'id1.gsdtechz.com'	=> 'http://www.gsdtechz.com',
			'id1.gsdtechx.com'	=> 'http://www.gsdtechx.com',
			'id1.gsdtechc.com'	=> 'http://www.gsdtechc.com',
		]
	],

	/*
    |--------------------------------------------------------------------------
    | Login uri config
    |--------------------------------------------------------------------------
    |
    | 配置	商户客户登陆地址
    |
    */
	'login_uri'	=> [
		'10037'	=> 'http://id1.gsdtechx.com/auth/login',
		'10043'	=> 'http://id1.gsdtechx.com/auth/login',
		'10045'	=> 'http://id1.gsdtechz.com/auth/login',
		'10069'	=> 'http://id1.gsdtechc.com//auth/login',
		'10075'	=> 'http://id1.gsdtechc.com//auth/login',
		'default'	=> 'http://id1.gsdtechs.com/auth/login'
	],

	'merchant_host'	=> [
		'10037'	=> 'http://id1.gsdtechx.com',
		'10043'	=> 'http://id1.gsdtechx.com',
		'10045'	=> 'http://id1.gsdtechz.com',
		'10069'	=> 'http://id1.gsdtechc.com',
		'10075'	=> 'http://id1.gsdtechc.com',
		'default'	=> 'http://id1.gsdtechs.com'
	],

	'sent_activation_guide'	=> [
		'10034','10043'
	],

	/*
    |--------------------------------------------------------------------------
    | Upload config
    |--------------------------------------------------------------------------
    |
    | 配置	上传文件大小限制
    |
    */
	'upload'	=> [
        'file_host'	=> 'http://id1.gsdtechs.com',//文件访问地址
		'admin_file_host' => 'http://id1.gsdtechs.com',  // 管理员访问静态文件地址
        'file_root'	=> 'D:/Websites/bp-prod/assets/',//文件上传根目录
		'maxsize'	=> 300000,	//最大上传文件大小（单位：Bytes）
		'minsize'	=> 1000		//最小上传文件大小（单位：Bytes）
	],

	/*
    |--------------------------------------------------------------------------
    | User config
    |--------------------------------------------------------------------------
    |
    | 配置	用户类型
    |
    */
	'user'	=> [
		'admin'	=> [
			'code'	=> 0xB010,//45072
			'name'	=> '管理员'
		],
		'service'	=> [
			'code'	=> 0xB011,//45073
			'name'	=> '客服'
		],
		'customer'	=> [
			'code'	=> 0xB012,//45074
			'name'	=> '客户'
		]
	],

    /*
    |--------------------------------------------------------------------------
    | Payment test config
    |--------------------------------------------------------------------------
    |
    | 配置	允许购买测试的用户
    |
    */
    'payment_test'	=> [
        'allow_users'	=> //'*'
			[
			'm15287654321@163.com',
			'tony29tony29@qq.com',
			'ivan.wong@henyep.com',
			'td194672513@163.com',
			'zhanghao914@gmail.com',
			'kringe@gmail.com',
			'b.soul@avatrade.com',
			'client@test.com',
			'morgan@gsdpay.com',
            'auguoziqian@trademax.com.au',
            'auguoziqian@gmail.com',
            '1296122733@qq.com',
            'hzaccp@qq.com',
			'17182112867@163.com',
			'andy13923@hotmail.com',
			'417117634@qq.com',
			'15952258410@qq.com',
			'1207784529@qq.com'
		]
    ],

	/*
    |--------------------------------------------------------------------------
    | mall unused cards config
    |--------------------------------------------------------------------------
    |
    | 配置	允许购买测试的用户
    |
    */
    'unused_cards'	=> [
        'CNON1907V29EIHWSBG',
        'CNON1907HAS3SKFVJQ',
        'CNON1907HLHR0QVFGK',
        'CNON1907HCYZVSH19C',
        'CNON1907CZ8QZKH7GE',
        'CNON1907WQABLNVZXG',
        'CNON1907R5EG93V3XI',
        'CNON1906TG34VOMZ88',
        'CNON1906RPSW5OKWI3',
        'CNON1906HTO1SCACMY',
        'CNON1907VCZUJRVFDB',
        'CNON1907KTKJKDCTNS',
        'CNON1907VWEN2SUT6U',
        'CNON1907QZFI20PBSA',
        'CNON1906AZGE2N73M5',
        'CNON19069NZLSULGCR',
        'CNON1906DTWMPZC6PQ',
        'CNON190675D2QWRZKV',
        'CNON1906XKS1WTA0VF',
        'CNON1906FAAYQZ45B4',
        'CNON1906L4CWCAMO96',
        'CNON1906RQZMC8YDEJ',
        'CNON1906AXGM35VL6E',
        'CNON19060WPDNCH7LG',
        'CNON19055SSEGTDH2K',
        'CNON1905Z6ZNA8BSVE',
        'CNON1905RUYDECYYVG',
        'CNON1905O1IJRPJLTG',
    ],

	/*
    |--------------------------------------------------------------------------
    | Operate type config
    |--------------------------------------------------------------------------
    |
    | 配置	用户操作类型
    |
    */
	'operate_type'	=> [
		'register'				=> 0xD11,//3345
		'reset_password'		=> 0xD12,//3346
		'login'					=> 0xD13,//3347
		'buy_cards'				=> 0xD14,//3348
		'payment'				=> 0xD15,//3349
		'view_voucher_key'		=> 0xD16,//3350
		'active_cards'			=> 0xD17,//3351
		'modify_password'		=> 0xD18,//3352
		'update_user_info'		=> 0xD19,//3353
		'additional_info'		=> 0xD20,//3360
		'refund'				=> 0xD21,//3361
        'frozen_user'           => 0xD22,//3362
		'unlock'				=> 0xD23,//3363
		'switch_language'		=> 0xD24,//3364
        'create'                => 0xD25,//3365
        'upload'                => 0xD26,//3366
        'update_cus_info'       => 0xD27,//3367
        'create_industry'       => 0xD28,//3368
        'update_industry_info'  => 0xD29,//3369
        'create_store'          => 0xD30,//3376
        'update_store_info'     => 0xD31,//3377
        'upload_specimen'       => 0xD32,//3378
        'update_specimen_info'  => 0xD33,//3379
        'new_goods'             => 0xD34,//3380
        'update_goods_info'     => 0xD35,//3381
        'upload_goods_img'      => 0xD36,//3382
        'update_goods_details'  => 0xD37,//3383
        'remove_goods'          => 0xD38,//3384
        'config_security'       => 0xD39,//3385

	],

	/*
    |--------------------------------------------------------------------------
    | Goods config
    |--------------------------------------------------------------------------
    |
    | 配置	商品信息
    |
    */
	'goods'	=> [
		'USD'	=> [
			'buy_limit'	=> 500,//美元卡最少购买金额
			'buy_stop'	=> 71285,//美元卡最多购买金额
		],
		'CNY'	=> [
			'buy_limit'	=> 1,//人民币卡最少购买金额
			'buy_stop'	=> 499000,//人民币卡最多购买金额
		]
	],

	/*
    |--------------------------------------------------------------------------
    | mail channel parameters config
    |--------------------------------------------------------------------------
    |
    | 配置	邮局通道参数信息
    |
    */
	'mail'	=> [
		'channel'	=> [
			'driver'	=> [
				'smtp'=>[25,465,587],
				'pop3'=>[110,995],
				'imap'=>[143,993]
			],
			'encryption'	=> ['ssl','tls',''],
			'stream'	=> [
				'ssl'	=> [//设置ssl证书协议认证方式  false表示忽略
					'verify_peer'		=> false,
					'verify_peer_name'	=> false
				]
			],
		],
		'max_delay_seconds'	=> 600, //邮件发送最大延时秒数
		'auth_operate_users'	=> [
			'bp001@qq.com',
			'bp002@qq.com',
			'bp003@qq.com'
		]
	],

	/*
    |--------------------------------------------------------------------------
    | System security config
    |--------------------------------------------------------------------------
    |
    | 配置	系统安全加密通信信息
    |
    */
	'security'	=> [
		'sys_type'				=> 'cu', //系统类型编码
		'token_prefix'			=> 'cu_' ,//认证信息前缀
		'register_token_key'	=> 'X-API-STKN', //注册认证请求头键名
		'user_token_key'		=> 'X-API-TOKEN', //用户认证请求头键名
		'notify_token_key'		=> 'X-API-TOKEN', //回调通知认证请求头键名
		'b_notify_header_info'	=> [
			'type'	=> 'sys',
			'ucode'	=> '000000'
		], //B系统回调通知的请求头参数信息
	],

	/*
    |--------------------------------------------------------------------------
    | B system web site config
    |--------------------------------------------------------------------------
    |
    | 配置	B系统网址
    |
    */
	'b_web_site' => [
		'live'	=> 'http://113.10.167.40:32951',
		'demo'	=> 'http://103.112.209.82:32951'
	],

	/*
    |--------------------------------------------------------------------------
    | Security code config
    |--------------------------------------------------------------------------
    |
    | 配置	系统安全码
    |
    */
	'security_code'	=> [
        'live'	=> 'GsdTech#RVNUe9rhu8*A&GV9e3toi309dfsh(*TR4390sdfg83roj0_)AEWGF',//真实环境
		'demo'	=> 'GsdTech#RVnj8w9ert8nV*234tji9)GVu4='//测试环境
	],

];
