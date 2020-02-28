<?php

return [

    '/api/admin/set-popular-store'	=> [
        'tag_list'	=> 'is_array'
    ],

    '/api/admin/set-popular-goods'	=> [
        'tag_list'	=> 'is_array'
    ],

    '/api/user/send-auth-code'	=> [
        'auth_type'		=> 'is_int',
        'operate_type'  => 'is_int',
        'email'			=> 'is_string',
        'phone'			=> NULL
    ],

	'/api/user/register'	=> [
		'name'			=> 'is_string',
		'email'			=> 'is_string',
		'phone'			=> 'is_string',
		'password'		=> 'is_string',
		'c_password'	=> 'is_string'
	],

	'/api/user/login'		=> [
		'language'		=> NULL,//CN,EN
		'username'		=> 'is_string|email,phone',
		'password'		=> 'is_string'
	],

	'/api/service/upload-file'	=> [
		'file_type'		=> NULL,
		'file_source'	=> NULL,
		'file_name'		=> NULL,
		'visibility'	=> NULL
	],

	/*
	 * --------------------------------------------------------------------------
	 * Configuration: get all users
	 * --------------------------------------------------------------------------
	 * @route api/user/all-list
	 * @method GET
	 * @params Request data[json]:null
	 * @return Illuminate\Http\Response such as:{code:'int',message:'string',data:[]|{}|null}
	 * Response data [array] [{
	 *  id：int											//用户id
	 *  code：string										//用户编码
	 * 	name：string										//用户昵称
	 *  email: string									//用户邮箱
	 *  phone：string									//用户手机号码
	 *  user_type_code：int enum(0xB010,0xB011,0xB012)	//用户类型 / 0xB010：管理员 / 0xB011：客服 / 0xB012：客户
	 *  user_code：string								//B系统中用户唯一编码
	 *  login_fail_times：int							//登录失败次数
	 *  active_status：int enum(0,1)						//激活状态 / 0：未激活 / 1：已激活
	 *  language_type_code：string enum('CN','EN')		//使用的语言编码 / 'CN'：中文 / 'EN'：English
	 *  created_at：date('Y-m-d H:i:s')					//生成时间
	 * },...]
	 *
	 */
	'/api/user/all-list'	=> [
        'is_paginate'		=> NULL,
        'page_items'		=> NULL
    ],

	/*
    |--------------------------------------------------------------------------
    | Configuration parameters
    |--------------------------------------------------------------------------
    |
    | 获取指定id的用户信息
    |
    */
	'/api/user/info'	=> [
		'uid'			=> 'is_int',//用户id
	],

	/*
	 * --------------------------------------------------------------------------
	 * Configuration: update the user info by uid
	 * --------------------------------------------------------------------------
	 * @route api/user/update-info
	 * @method POST
	 * @params data[json]:as follow
	 * @return Illuminate\Http\Response such as:{code:'int',message:'string'}
	 * success：{ code:0xFFF, message:'Update the user info success' }
	 * failure：{ code:0xF03, message:'Update the user info fail' }
	 *
	 */
	'/api/user/update-info'	=> [
		'name'				=> 'is_string',//昵称
		'email'				=> 'is_string'//邮箱
	],

	'/api/user/update-login-password'	=> [
		'old_password'		=> 'is_string',//旧登陆密码
		'new_password'		=> 'is_string',//新登陆密码
	],

	'/api/user/update-operate-password'	=> [
		'old_password'		=> 'is_string',//旧操作密码
		'new_password'		=> 'is_string',//新操作密码
	],

    '/api/user/change-status'	=>[
        'uid'				=> 'is_int',
        'status'			=> 'is_int|0,1,-1',
        'operate_password'	=> 'is_string'
    ],

    '/api/user/unlock'	=> [
        'uid'				=> 'is_int',
        'email'				=> 'is_string',
        'phone'				=> NULL,
        'operate_password'	=> 'is_string'
    ],

	'/api/user/change-language'	=> [
		'language_code'	=> 'is_string|CN,EN'
	],

	'/api/customer/list'	=> [
		'begin_date'		=> NULL,
		'end_date'			=> NULL,
		'is_paginate'		=> NULL,
		'page_items'		=> NULL
	],

	'/api/customer/update-info'	=> [
		'name'				=> NULL,
        'profile_photo'		=> NULL,
		'bank_name'			=> NULL,
		'bank_account'		=> NULL,
		'card_holder'		=> NULL,
		'id_card_number'	=> NULL,
		'card_photo'		=> NULL,
		'id_card_front'		=> NULL,
		'id_card_behind'	=> NULL
	],

    '/api/industry/create'  => [
        'father_code'		=> NULL,
        'word'  			=> [
            'cn'    		=> 'is_string',
            'en'    		=> 'is_string'
        ],
        'icon'				=> NULL
    ],

    '/api/industry/update'  => [
        'ind_id'    		=> 'is_int',
        'word'      		=> NULL,
		'icon'				=> NULL
    ],

	'/api/store/create'		=> [
		'ind_code'			=> 'is_string',
		'name'      	=> [
			'cn'			=> 'is_string',
			'en'			=> 'is_string'
		],
		'logo'				=> NULL,
		'litpic'			=> NULL,
		'introduce'	=> [
			'cn'			=> 'is_string',
			'en'			=> 'is_string'
		],
		'address'		=> [
			'cn'			=> 'is_string',
			'en'			=> 'is_string'
		],
        'grade'			=> 'is_numeric',
        'evaluation'	=> [
            'cn'			=> 'is_string',
            'en'			=> 'is_string'
        ],
        'tag_label'			=> NULL
	],

	'/api/store/update'		=> [
		'code'				=> 'is_string',
		'ind_code'			=> NULL,
		'name'      	=> NULL,
		'logo'				=> NULL,
		'litpic'			=> NULL,
		'introduce'	=> NULL,
		'address'		=> NULL,
        'grade'			=> NULL,
        'evaluation'	=> NULL,
        'tag_label'			=> NULL
	],

    '/api/store/upload-specimen'	=>[
        [
            'store_code'		=> 'is_string',
            'name_word'      	=> [
                'cn'			=> 'is_string',
                'en'			=> 'is_string'
            ],
            'photo'				=> 'is_string',
            'title_word'		=> [
                'cn'			=> 'is_string',
                'en'			=> 'is_string'
            ],
            'features_word'		=> [
                'cn'			=> 'is_string',
                'en'			=> 'is_string'
            ],
            'introduce_word'	=> [
                'cn'			=> 'is_string',
                'en'			=> 'is_string'
            ]
        ]
    ],

	'/api/store/update-specimen'	=>[
		'id'				=> 'is_int',
		'store_code'		=> 'is_string',
		'name_word'      	=> NULL,
		'photo'				=> NULL,
		'title_word'		=> NULL,
		'features_word'		=> NULL,
		'introduce_word'	=> NULL
	],

	'/api/goods/all-list'	=> [
		'is_paginate'		=> NULL,
		'page_items'		=> NULL
	],

	'/api/goods/new'			=> [
		'store_info_code'	=> 'is_string',
		'name_word'      	=> [
			'cn'			=> 'is_string',
			'en'			=> 'is_string'
		],
        'original_price'	=> 'is_numeric',
		'price'				=> 'is_numeric',
		'currency_type_code'		=> 'is_string|USD,CNY',
		'litpic'			=> NULL,
		'buy_limit'			=> NULL,
		'buy_stop'			=> NULL,
		'features_word'		=> [
			'cn'			=> 'is_string',
			'en'			=> 'is_string'
		],
		'introduce_word'	=> [
			'cn'			=> 'is_string',
			'en'			=> 'is_string'
		]
	],

	'/api/goods/update'			=> [
		'goods_code'			=> 'is_string',
		'name_word'      		=> NULL,
        'original_price'	    => NULL,
        'price'					=> NULL,
        'currency_code'			=> NULL,
		'litpic'				=> NULL,
		'buy_limit'				=> NULL,
		'buy_stop'				=> NULL,
		'features_word'			=> NULL,
		'introduce_word'		=> NULL
	],

	'/api/goods/upload-details'	=> [
        [
            'goods_code'		=> 'is_string',
            'title_word'		=> [
                'cn'			=> 'is_string',
                'en'			=> 'is_string'
            ],
            'image'				=> 'is_string',
            'info_word'			=> [
                'cn'			=> 'is_string',
                'en'			=> 'is_string'
            ],
        ]
	],

	'/api/goods/update-details'	=> [
		'id'				=> 'is_int',
		'goods_code'		=> 'is_string',
		'title_word'		=> NULL,
		'image'				=> NULL,
		'info_word'			=> NULL
	],

	'/api/goods/remove'		=> [
		'id'				=> 'is_int'
	],

	'/api/merchants/config-security'	=> [
		'merchant_code'			=> 'is_string',
		'user_code'			=> 'is_string'
	],

	'/api/purchase/list'	=> [
		'begin_date'		=> NULL,
		'end_date'			=> NULL,
		'is_paginate'		=> NULL,
		'page_items'		=> NULL,
        'payment_status'	=> NULL
	],

    '/api/purchase/a-record' => [
        'purchase_id'	=> 'is_int'
    ],

	'/api/purchase/goods'	=> [
        'goods_type'		=> 'is_int|0,1',
		'goods_info_code'	=> 'is_string',
		'buy_number'		=> 'is_int',
        'payment_token'		=> NULL,
	],

	'/api/order/list'		=> [
		'begin_date'		=> NULL,
		'end_date'			=> NULL,
		'is_paginate'		=> NULL,
		'page_items'		=> NULL
	],

	'/api/order/list-of-purchase' => [
		'purchase_id'	=> NULL
	],

	'/api/order/pay'		=> [
		'purchase_id'	=> NULL
	],

	'/api/cards/list'		=> [
		'begin_date'		=> NULL,
		'end_date'			=> NULL,
		'is_paginate'		=> NULL,
		'page_items'		=> NULL,
        'use_status'		=> NULL
	],

	'/api/cards/password-view'	=> [
		'card_no'		=> 'is_string'
	],

    '/api/cards/list-b'		=> [
        'begin_date'		=> NULL,
        'end_date'			=> NULL
    ],

    '/api/cards/password-view-b'	=> [
        'card_no'		=> 'is_string'
    ],

    '/api/cards/set-allow-view'	=> [//设置是否允许客户在后台查看这张充值卡
        'cards_id'			=> 'is_int',
        'is_allow'			=> 'is_int|0,1',
        'operate_password'	=> 'is_string'
    ],

	'/api/refund/list'			=> [
		'begin_date'		=> NULL,
		'end_date'			=> NULL,
		'is_paginate'		=> NULL,
		'page_items'		=> NULL
	],

	'/api/refund/apply'			=> [
		'card_no'		=> 'is_string',
		'reason'		=> NULL
	],

	'/api/log/register-list'	=> [
		'begin_date'		=> NULL,
		'end_date'			=> NULL,
		'is_paginate'		=> NULL,
		'page_items'		=> NULL
	],

	'/api/log/login-list'		=> [
		'begin_date'		=> NULL,
		'end_date'			=> NULL,
		'is_paginate'		=> NULL,
		'page_items'		=> NULL
	],

	'/api/log/b-notify-list'	=> [
		'begin_date'		=> NULL,
		'end_date'			=> NULL,
		'is_paginate'		=> NULL,
		'page_items'		=> NULL
	],

    '/api/log/notify-merchant-list'	=> [
        'begin_date'		=> NULL,
        'end_date'			=> NULL,
        'is_paginate'		=> NULL,
        'page_items'		=> NULL
    ],

	'/api/log/crm-request-list'	=> [
		'begin_date'		=> NULL,
		'end_date'			=> NULL,
		'is_paginate'		=> NULL,
		'page_items'		=> NULL
	],

	'/api/log/user-operate-list'	=> [
		'begin_date'		=> NULL,
		'end_date'			=> NULL,
		'is_paginate'		=> NULL,
		'page_items'		=> NULL
	],

    '/api/log/send-mail-list'	=> [
        'begin_date'		=> NULL,
        'end_date'			=> NULL,
        'is_paginate'		=> NULL,
        'page_items'		=> NULL
    ],
	
	'/api/log/guide-mail-list'	=> [
		'begin_date'		=> NULL,
		'end_date'			=> NULL,
		'is_paginate'		=> NULL,
		'page_items'		=> NULL
	],

    '/api/search/users'         => [
        'vague'     => NULL,
        'fields'     => NULL,
        'value'     => NULL,
        'begin_date'	=> NULL,
        'end_date'		=> NULL
    ],

    '/api/search/purchase_records'         => [
        'vague'     => NULL,
        'field'     => NULL,
        'value'     => NULL,
        'begin_date'	=> NULL,
        'end_date'		=> NULL
    ],

    '/api/search/payment_orders'         => [
        'vague'     => NULL,
        'field'     => NULL,
        'value'     => NULL,
        'begin_date'	=> NULL,
        'end_date'		=> NULL
    ],

    '/api/search/cash_cards'         => [
        'vague'     => NULL,
        'field'     => NULL,
        'value'     => NULL,
        'begin_date'	=> NULL,
        'end_date'		=> NULL
    ],

    '/api/virtual-cards/list'	=> [
        'begin_date'		=> NULL,
        'end_date'			=> NULL,
        'is_paginate'		=> NULL,
        'page_items'		=> NULL
    ],

    '/api/virtual-cards/new'	=> [
        'name'				=> [
            'cn'			=> 'is_string',
            'en'			=> 'is_string'
        ],
        'price'				=> 'is_numeric',
        'litpic'				=> NULL,
        'currency_type_code'		=> 'is_string|USD,CNY',
        'buy_limit'			=> NULL,
        'buy_stop'			=> NULL,
    ],

    '/api/virtual-cards/update'	=> [
        'cards_code'		=> 'is_string',
        'litpic'				=> NULL,
        'name'				=> NULL,
        'price'				=> NULL,
        'currency_type_code'		=> NULL,
        'buy_limit'			=> NULL,
        'buy_stop'			=> NULL,
    ],

    '/api/service/supply-card-mail'	=> [
        'mail_type'	=> 'is_int|1,2',
        'card_id'	=> 'is_int'
    ],

    '/api/admin/set-store-specimen-sort'	=> [
        'sort_rule'	=> []
    ],

    '/api/admin/set-goods-detail-sort'	=> [
        'sort_rule'	=> []
    ],

    '/api/gateway/payment'	=> [
        'goods_type'		=> NULL,
        'goods_info_code'	=> 'is_string',
        'buy_number'		=> 'is_numeric',
        'payment_token'		=> NULL,
    ],
	
	'/api/mail/create-channel'	=> [
		'code'			=> 'is_string',
		'name'			=> 'is_string',
		'driver'		=> 'is_string',
		'host'			=> 'is_string',
		'port'			=> 'is_string',
		'encryption'	=> 'is_string|ssl,tls,',
		'username'		=> 'is_string',
		'password'		=> 'is_string',
		'is_ignore'		=> 'is_string|0,1',
		'daily_send_limit'	=> 'is_int',
		'queue_key'		=> 'is_string'
	],

	'/api/mail/update-channel'	=> [
		'code'			=> 'is_string',
		'name'			=> NULL,
		'driver'		=> NULL,
		'host'			=> NULL,
		'port'			=> NULL,
		'encryption'	=> NULL,
		'username'		=> NULL,
		'password'		=> NULL,
		'is_ignore'		=> NULL,
		'daily_send_limit'	=> NULL,
		'queue_key'		=> NULL
	],

	'/api/mail/toggle-channel-status' => [
		'code'			=> 'is_string',
		'status'		=> 'is_int|0,-1'
	],

	'/api/mail/new-channels-group' => [
		'code'			=> 'is_string',
		'name'			=> 'is_string',
		'include_channels'	=> 'is_array',
		'repeat_times'		=> 'is_int',
		'using_channel'		=> NULL
	],

	'/api/mail/update-channels-group' => [
		'code'			=> 'is_string',
		'name'			=> NULL,
		'include_channels'	=> NULL,
		'repeat_times'		=> NULL,
		'using_channel'		=> NULL
	],

	'/api/mail/config-sent-type'	=> [
		'code'		=> 'is_string',
		'name'		=> NULL,
		'current_channels_group'	=> NULL,
		'prepare_channels_groups'	=> NULL,
		'emergency_channel'			=> NULL,
		'delay_send_seconds'		=> NULL
	],
	
	'/api/mail/test-channel'	=> [
		'channel_code'	=> 'is_string',
		'receive_email'	=> 'is_string'
	],

];
