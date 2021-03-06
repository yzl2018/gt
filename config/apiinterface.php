<?php

return [//商城系统接口访问权限配置

    'api/user/send-auth-code'       => [0xB010,0xB011,0xB012],//发送验证码

	/*
    |--------------------------------------------------------------------------
    | Admin api interface
    |--------------------------------------------------------------------------
    |
    | 配置	管理员专用操作接口组
    |
    */
	'/api/admin/create-authorized-code'		=> [0xB010],//生成授权码
    '/api/admin/set-popular-store'			=> [0xB010,0xB011],//设置推荐店铺
    '/api/admin/set-popular-goods'			=> [0xB010,0xB011],//设置推荐商品
    '/api/admin/set-store-specimen-sort'	=> [0xB010,0xB011],//设置店铺样品排序规则
    '/api/admin/set-goods-detail-sort'		=> [0xB010,0xB011],//设置商品详情排序规则

	/*
    |--------------------------------------------------------------------------
    | Service api interface
    |--------------------------------------------------------------------------
    |
    | 配置	客服专用操作接口组
    |
    */
	'/api/service/upload-file'				=> [0xB010,0xB011,0xB012],//上传文件
    '/api/service/supply-card-mail'		    => [0xB010,0xB011,0xB012],//补发购买成功邮件和充值成功邮件

    /*
    |--------------------------------------------------------------------------
    | Service api interface
    |--------------------------------------------------------------------------
    |
    | 配置	虚拟卡片信息操作接口组
    |
    */
    '/api/virtual-cards/new'				=> [0xB010,0xB011],//新增虚拟卡片
    '/api/virtual-cards/update'				=> [0xB010,0xB011],//更新虚拟卡片
    '/api/virtual-cards/list'				=> [0xB010,0xB011,0xB012],//获取虚拟充值卡

	/*
    |--------------------------------------------------------------------------
    | User api interface
    |--------------------------------------------------------------------------
    |
    | 配置	用户信息操作接口组
    |
    */
	'/api/user/all-list'				=> [0xB010,0xB011],//获取所有用户信息
	'/api/user/info'					=> [0xB010,0xB011,0xB012],//获取某个用户信息
	'/api/user/update-info'				=> [0xB010,0xB011,0xB012],//更新用户信息
	'/api/user/update-login-password'	=> [0xB010,0xB011,0xB012],//更新用户登陆密码
	'/api/user/update-operate-password'	=> [0xB010,0xB011,0xB012],//更新用户操作密码
	'/api/user/change-status'			=> [0xB010],//关闭或打开用户登陆的状态
	'/api/user/unlock'					=> [0xB010,0xB011],//关闭或打开用户登陆的锁定状态
	'/api/user/change-language'			=> [0xB010,0xB011,0xB012],//用户切换语言

	/*
    |--------------------------------------------------------------------------
    | Customer api interface
    |--------------------------------------------------------------------------
    |
    | 配置	客户信息操作接口组
    |
    */
	'/api/customer/list'				=> [0xB010,0xB011],//所有客户记录
	'/api/customer/info'				=> [0xB010,0xB011,0xB012],//获取客户信息
	'/api/customer/update-info'			=> [0xB010,0xB011,0xB012],//更新客户信息

	/*
    |--------------------------------------------------------------------------
    | Industry api interface
    |--------------------------------------------------------------------------
    |
    | 配置	行业信息操作接口组
    |
    */
	'/api/industry/all-list'	=> [0xB010,0xB011,0xB012],//所有行业信息
	'/api/industry/create'		=> [0xB010,0xB011],//创建行业信息
	'/api/industry/update'		=> [0xB010,0xB011],//更新行业信息

	/*
    |--------------------------------------------------------------------------
    | Store api interface
    |--------------------------------------------------------------------------
    |
    | 配置	商家信息操作接口组
    |
    */
	'/api/store/all-list'			=> [0xB010,0xB011,0xB012],//所有商家记录
	'/api/store/create'				=> [0xB010,0xB011],//创建商家
	'/api/store/update'				=> [0xB010,0xB011],//更新商家信息
	'/api/store/upload-specimen'	=> [0xB010,0xB011],//上传店铺样品图片
    '/api/store/update-specimen'	=> [0xB010,0xB011],//更新店铺样品图片

	/*
    |--------------------------------------------------------------------------
    | Goods api interface
    |--------------------------------------------------------------------------
    |
    | 配置	商品信息操作接口组
    |
    */
	'/api/goods/all-list'		=> [0xB010,0xB011,0xB012],//所有商品记录
	'/api/goods/new'			=> [0xB010,0xB011],//新增商品
	'/api/goods/update'			=> [0xB010,0xB011],//更新商品信息
	'/api/goods/upload-details'	=> [0xB010,0xB011],//上传商品详情图片
    '/api/goods/update-details'	=> [0xB010,0xB011],//更新商品详情图片
	'/api/goods/remove'			=> [0xB010,0xB011],//删除商品

	/*
    |--------------------------------------------------------------------------
    | Merchant api interface
    |--------------------------------------------------------------------------
    |
    | 配置	商户密钥接口组
    |
    */
	'/api/merchants/security'		=> [0xB010,0xB011],//获取商户密钥信息
	'/api/merchants/config-security'	=> [0xB010],//配置商户密钥

	/*
    |--------------------------------------------------------------------------
    | Purchase api interface
    |--------------------------------------------------------------------------
    |
    | 配置	客户购买接口组
    |
    */
	'/api/purchase/list'		=> [0xB010,0xB011,0xB012],//所有购买记录(客户只查看自己的)
    '/api/purchase/a-record'	=> [0xB010,0xB011,0xB012],//查看单个购买记录
	'/api/purchase/goods'		=> [0xB012],//客户购买

	/*
    |--------------------------------------------------------------------------
    | Order api interface
    |--------------------------------------------------------------------------
    |
    | 配置	订单交易接口组
    |
    */
	'/api/order/list'		=> [0xB010,0xB011],//所有订单记录
	'/api/order/list-of-purchase'	=> [0xB010,0xB011],//获取某个购买记录的所有支付订单记录
	'/api/order/pay'		=> [0xB012],//订单支付

	/*
    |--------------------------------------------------------------------------
    | Cards api interface
    |--------------------------------------------------------------------------
    |
    | 配置	充值卡操作接口组
    |
    */
	'/api/cards/list'				=> [0xB010,0xB011,0xB012],//所有充值卡记录(客户只查看自己的)
	'/api/cards/password-view'		=> [0xB012],//查看充值卡密码
    '/api/cards/list-b'				=> [0xB010,0xB011,0xB012],//所有充值卡记录(客户只查看自己的)
    '/api/cards/password-view-b'	=> [0xB012],//查看充值卡密码
    '/api/cards/set-allow-view'		=> [0xB010,0xB011],//设置是否允许客户查看这张充值卡

	/*
    |--------------------------------------------------------------------------
    | Refund api interface
    |--------------------------------------------------------------------------
    |
    | 配置	退款操作接口组
    |
    */
	'/api/refund/list'			=> [0xB010,0xB011,0xB012],//所有退款记录(客户只查看自己的)
	'/api/refund/apply'			=> [0xB012],//客户申请退款

	/*
    |--------------------------------------------------------------------------
    | Log api interface
    |--------------------------------------------------------------------------
    |
    | 配置	日志操作接口组
    |
    */
	'/api/log/register-list'		=> [0xB010,0xB011],//查看所有注册日志
	'/api/log/login-list'			=> [0xB010,0xB011,0xB012],//查看所有登陆日志(客户只查看自己的)
	'/api/log/b-notify-list'		=> [0xB010,0xB011],//查看所有B系统通知日志
	'/api/log/crm-request-list'		=> [0xB010,0xB011],//查看所有CRM 激活请求日志
	'/api/log/user-operate-list'	=> [0xB010,0xB011],//查看所有用户操作日志
    '/api/log/send-mail-list'		=> [0xB010,0xB011],//查看所邮件发送日志
    '/api/log/notify-merchant-list'	=> [0xB010,0xB011],//查看所有通知商户充值卡激活日志
	'/api/log/guide-mail-list'		=> [0xB010,0xB011],//查看所有商户引导邮件发送日志

	/*
    |--------------------------------------------------------------------------
    | mail channels api interface
    |--------------------------------------------------------------------------
    |
    | 配置	邮局通道接口组
    |
    */
	'/api/mail/channels-list'                    => [0xB010],//获取所有邮局通道信息的路由
	'/api/mail/channels-groups-list'                  => [0xB010],//获取所有通道组信息的路由
	'/api/mail/views-list'                       => [0xB010],//获取所有邮件视图信息的路由
	'/api/mail/configs-list'                     => [0xB010],//获取所有邮件类型配置信息的路由
	'/api/mail/channels-code-name'               => [0xB010],//获取所有通道的编码名称信息的路由
	'/api/mail/channels-of-group-code'           => [0xB010],//获取通道组编码所对应包含的通道信息的路由
	'/api/mail/channels-driver-info'             => [0xB010],//获取通道的驱动基本信息的路由
	'/api/mail/create-channel'                   => [0xB010],//创建邮局通道的路由
	'/api/mail/update-channel'                   => [0xB010],//更新邮局通道的路由
	'/api/mail/toggle-channel-status'            => [0xB010],//切换邮局通道使用状态的路由
	'/api/mail/new-channels-group'               => [0xB010],//创建邮局通道组的路由
	'/api/mail/update-channels-group'            => [0xB010],//更新邮局通道组的路由
	'/api/mail/config-sent-type'                 => [0xB010],//配置邮件视图和发送通道组的路由
	'/api/mail/test-channel'                 	 => [0xB010],//测试邮局通道的路由

    /*
    |--------------------------------------------------------------------------
    | search api interface
    |--------------------------------------------------------------------------
    |
    | 配置	搜索接口组
    |
    */
    '/api/search/users'                      => [0xB010,0xB011],//搜索用户
    '/api/search/purchase_records'           => [0xB010,0xB011],//搜索购买记录
    '/api/search/payment_orders'             => [0xB010,0xB011],//搜索订单记录
    '/api/search/cash_cards'                 => [0xB010,0xB011],//搜索充值卡

    '/api/user/payment-token'				 => [0xB012],//获取单页支付测试Token
    '/api/gateway/payment'					 => [0xB012],//购买商品、生成订单、跳转支付三步一体的支付接口

    '/api/domain/trains-limit'				 => [0xB010,0xB011,0xB012],//获取域名交易金额门槛设置

];
