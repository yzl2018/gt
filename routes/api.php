<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//获取客户端访问IP所在地信息的接口
Route::post('ip/location','API\Normal\IPLocationController@getIpAddress');

//用户操作接口组
Route::prefix('user')->group(function () {
    Route::post('send-auth-code','API\Normal\SendAuthCodeController@sendAuthCode');//发送验证码的路由
	Route::post('register','API\PassportController@register')->middleware('log.register');//用户注册的路由
	Route::post('login','API\PassportController@login')->middleware('log.login');//用户登陆的路由
	Route::post('password-reset','API\PassportController@resetPassword');//重置密码的路由
});

//管理员专用操作接口组
Route::group(['prefix' => 'admin','middleware' => ['auth.client','auth:api','auth.parameters']], function () {
	Route::get('create-authorized-code','API\Normal\AdminController@createAuthCode');
    Route::post('set-popular-store','API\Normal\PushStoreGoodsController@setPopularStore');
    Route::post('set-popular-goods','API\Normal\PushStoreGoodsController@setPopulargoods');
    Route::post('set-store-specimen-sort','API\Normal\PushStoreGoodsController@setStoreSpecimenSort');
    Route::post('set-goods-detail-sort','API\Normal\PushStoreGoodsController@setGoodsDetailSort');
});

//前端访问接口组
Route::prefix('front')->group(function () {
    Route::post('get-popular-store','API\Normal\PushStoreGoodsController@getPopularStore');
    Route::post('get-popular-goods','API\Normal\PushStoreGoodsController@getPopularGoods');
});
Route::prefix('industry')->group(function () {
	Route::get('all-list','API\Normal\IndustryController@showAllIndustry');//获取所有行业信息的路由
    Route::post('all-stores','API\Normal\IndustryController@getAllStoresOfOneIndustry');//获取某个分类下的所有店铺的路由
	Route::post('all-goods','API\Normal\IndustryController@getAllGoodsOfOneIndustry');//获取某个分类下的所有商品的路由
});
Route::prefix('store')->group(function () {
	Route::get('all-list','API\Normal\StoreController@showAllStores');//获取所有商家店铺信息的路由
    Route::post('all-goods','API\Normal\StoreController@getAllGoodsOfOneStore');//获取某个店铺下的所有商品的路由
	Route::post('details-info','API\Normal\StoreController@getStoreDetails');//获取商家店铺详情信息的路由
});
Route::prefix('goods')->group(function () {
	Route::post('all-list','API\Normal\GoodsController@showAllGoods');//获取所有商品信息的路由
	Route::post('details-info','API\Normal\GoodsController@getGoodsDetails');//获取商品详情信息的路由
});

//邮局通道操作接口组
Route::group(['prefix' => 'mail','middleware' => ['auth.client','auth:api','auth.parameters']],function(){
	Route::get('channels-list','API\Normal\MailChannelsConfigController@showAllMailChannels');//获取所有邮局通道信息的路由
	Route::get('channels-groups-list','API\Normal\MailChannelsConfigController@showAllMailChannelsGroups');//获取所有通道组信息的路由
	Route::get('views-list','API\Normal\MailChannelsConfigController@showAllMailViews');//获取所有邮件视图信息的路由
	Route::get('configs-list','API\Normal\MailChannelsConfigController@showAllMailTypesConfig');//获取所有邮件类型配置信息的路由
	Route::get('channels-code-name','API\Normal\MailChannelsConfigController@getChannelsCodeName');//获取所有通道的编码名称信息的路由
	Route::get('channels-of-group-code','API\Normal\MailChannelsConfigController@getChannelsOfGroupCode');//获取通道组编码所对应包含的通道信息的路由
	Route::get('channels-driver-info','API\Normal\MailChannelsConfigController@getBaseDriverInfo');//获取通道的驱动基本信息的路由

	Route::post('create-channel','API\Normal\MailChannelsConfigController@createMailChannel');//创建邮局通道的路由
	Route::post('update-channel','API\Normal\MailChannelsConfigController@updateMailChannel');//更新邮局通道的路由
	Route::post('toggle-channel-status','API\Normal\MailChannelsConfigController@toggleMailChannelStatus');//切换邮局通道使用状态的路由
	Route::post('new-channels-group','API\Normal\MailChannelsConfigController@newMailChannelsGroup');//创建邮局通道组的路由
	Route::post('update-channels-group','API\Normal\MailChannelsConfigController@updateMailChannelsGroup');//更新邮局通道组的路由
	Route::post('config-sent-type','API\Normal\MailChannelsConfigController@configMailTypes');//配置邮件视图和发送通道组的路由

	Route::post('test-channel','API\Normal\MailChannelsConfigController@testMailChannel');//测试邮局通道的路由
});

//客服专用操作接口组
Route::group(['prefix' => 'service','middleware' => ['auth.client','auth:api','auth.parameters']], function () {
	Route::post('upload-file','API\Normal\UploadFileController@uploadFile');
    Route::post('supply-card-mail','API\Normal\SupplyMailNoticeController@supplySuccessMail')->middleware('log.operate:supply_mail');//补发 购买充值卡成功邮件 和 激活充值卡成功邮件
});

Route::prefix('guide')->group(function(){
	Route::get('sent-view/{mer_code}','API\External\GuideHelpController@viewOperation');//发送引导邮件的视图
	Route::post('prepare-sent/{guide_type}','API\External\GuideHelpController@prepareSendMail');//预备发送邮件
	Route::post('sent-mail','API\External\SentGuideMailController@sendGuidanceMail');//发送引导邮件
});

//用户信息操作接口组
Route::group(['prefix' => 'user','middleware' => ['auth.client','auth:api','auth.parameters']], function () {
	Route::post('all-list','API\Normal\UserController@showAllUsers');//获取所有用户信息的路由
	Route::get('info','API\Normal\UserController@getUserInfo');//获取自己的用户信息的路由
	Route::post('update-info','API\Normal\UserController@updateUserInfo')->middleware('log.operate:update_user_info');//更新用户信息的路由
    Route::post('update-login-password','API\Normal\UserController@updateLoginPassword')->middleware('log.operate:modify_password');//更新用户登陆密码的路由
	Route::post('update-operate-password','API\Normal\UserController@updateOperatePassword')->middleware('log.operate:modify_password');//更新用户登陆密码的路由
	Route::post('change-status','API\Normal\UserController@changeUserStatus')->middleware('log.operate:frozen_user');//关闭或打开用户登陆的锁定状态的路由
	Route::post('unlock','API\Normal\UserController@unlockUser')->middleware('log.operate:unlock');//解锁用户
	Route::post('change-language','API\Normal\UserController@changeLanguage')->middleware('log.operate:switch_language');//用户切换语言

    Route::get('payment-token','API\Normal\UserController@getTestPaymentAccessPermission');//获取用户是否有访问支付测试页面的权限
});

//客户信息操作接口组
Route::group(['prefix' => 'customer','middleware' => ['auth.client','auth:api','auth.parameters']], function () {
	Route::post('list','API\Normal\CustomerController@showCustomersList');//获取所有客户记录的路由
	Route::get('info','API\Normal\CustomerController@getCustomerInfo');//获取客户信息的路由
	Route::post('update-info','API\Normal\CustomerController@updateCustomerInfo')->middleware('log.operate:update_cus_info');//更新客户信息的路由
});

//行业信息操作接口组
Route::group(['prefix' => 'industry','middleware' => ['auth.client','auth:api','auth.parameters']], function () {
	Route::post('create','API\Normal\IndustryController@newIndustry')->middleware('log.operate:create_industry');//创建新行业的路由
	Route::post('update','API\Normal\IndustryController@updateIndustry')->middleware('log.operate:update_industry_info');//更新行业信息的路由
});

//商家信息操作接口组
Route::group(['prefix' => 'store','middleware' => ['auth.client','auth:api','auth.parameters']], function () {
	Route::post('create','API\Normal\StoreController@newStore')->middleware('log.operate:create_store');//创建商家店铺信息的路由
	Route::post('update','API\Normal\StoreController@updateStore')->middleware('log.operate:update_store_info');//更新商家店铺信息的路由
	Route::post('upload-specimen','API\Normal\StoreController@uploadSpecimen')->middleware('log.operate:upload_specimen');//上传店铺样品图片的路由
	Route::post('update-specimen','API\Normal\StoreController@updateSpecimen')->middleware('log.operate:update_specimen_info');//更新店铺样品图片信息的路由
});

//商品信息操作接口组
Route::group(['prefix' => 'goods','middleware' => ['auth.client','auth:api','auth.parameters']], function () {
	Route::post('new','API\Normal\GoodsController@newGoods')->middleware('log.operate:new_goods');//新增商品信息的路由
	Route::post('update','API\Normal\GoodsController@updateGoods')->middleware('log.operate:update_goods_info');//更新商品信息的路由
	Route::post('upload-details','API\Normal\GoodsController@uploadGoodsDetails')->middleware('log.operate:upload_goods_img');//上传商品详情图片的路由
	Route::post('update-details','API\Normal\GoodsController@updateGoodsDetails')->middleware('log.operate:update_goods_details');//更新商品详情图片的路由
	Route::post('remove','API\Normal\GoodsController@removeGoods')->middleware('log.operate:remove_goods');//删除商品的路由
});

//虚拟充值卡操作接口组
Route::group(['prefix' => 'virtual-cards','middleware' => ['auth.client','auth:api','auth.parameters']], function() {
    Route::post('list','API\Normal\VirtualCardsController@showAllCards');//获取所有虚拟卡片信息
    Route::post('new','API\Normal\VirtualCardsController@newCards');//新增虚拟卡片信息
    Route::post('update','API\Normal\VirtualCardsController@updateCards');//更新虚拟卡片信息
});

//商户密钥接口组
Route::group(['prefix' => 'merchants','middleware' => ['auth.client','auth:api','auth.parameters']], function(){
	Route::get('security','API\Normal\MerchantsController@showMerchantsSecurity');//获取所有商户的密钥信息的路由
	Route::post('config-security','API\Normal\MerchantsController@configSecurity')->middleware('log.operate:config_security');//配置商户的密钥的路由
});

//客户购买接口组
Route::group(['prefix' => 'purchase','middleware' => ['auth.client','auth:api','auth.parameters']], function () {
    Route::post('list','API\Normal\PurchaseController@showPurchaseRecords');//获取所有客户购买记录的路由
    Route::post('a-record','API\Normal\PurchaseController@getOnePurchaseRecord');//获取单个购买记录的路由
    Route::post('goods','API\Normal\PurchaseController@purchaseGoods')->middleware('log.operate:buy_cards');//客户购买商品的路由
});

//订单交易接口组
Route::group(['prefix' => 'order','middleware' => ['auth.client','auth:api','auth.parameters']], function () {
	Route::post('list','API\Normal\OrderController@showOrdersList');//获取所有订单记录的路由
	Route::post('list-of-purchase','API\Normal\OrderController@ordersOfPurchase');//获取某个购买记录的所有支付订单记录
	Route::post('pay','API\Normal\OrderController@payment')->middleware('log.operate:payment');//订单支付请求的路由
});

//域名设置信息接口组
Route::group(['prefix' => 'domain'], function () {
    Route::get('trains-limit','API\Normal\DomainController@getAllDomainTrainsLimit');//获取域名交易金额门槛设置
});

//充值卡操作接口组
Route::group(['prefix' => 'cards','middleware' => ['auth.client','auth:api','auth.parameters']], function() {
	Route::post('list','API\Normal\CardsController@showCardsList');//获取所有充值卡的路由
	Route::post('password-view','API\Normal\CardsController@showPassword')->middleware('log.operate:view_voucher_key');//查看充值卡密码的路由

    Route::post('list-b','API\Normal\CardsController@showCardsListFromB');//获取所有充值卡的路由(from B)
    Route::post('password-view-b','API\Normal\CardsController@showPasswordFromB')->middleware('log.operate:view_voucher_key');//查看充值卡密码的路由(from B)
    Route::post('set-allow-view','API\Normal\CardsController@setAllowViewOrNot');//关闭或打开某张充值卡 是否允许客户在后台查看
});

//退款操作接口组
Route::group(['prefix' => 'refund','middleware' => ['auth.client','auth:api','auth.parameters']], function() {
	Route::post('list','API\Normal\RefundController@showRefundsList');//获取所有退款记录的路由
	Route::post('apply','API\Normal\RefundController@applyRefund')->middleware('log.operate:refund');//客户申请退款的路由
});

//日志操作接口组
Route::group(['prefix' => 'log','middleware' => ['auth.client','auth:api','auth.parameters']], function () {
	Route::post('register-list','API\Normal\LogController@showRegisterLogs');//查看所有注册日志的路由
	Route::post('login-list','API\Normal\LogController@showLoginLogs');//查看所有登陆日志(客户只查看自己的)的路由
	Route::post('b-notify-list','API\Normal\LogController@showBNotifyLogs');//查看所有B系统通知日志的路由
	Route::post('crm-request-list','API\Normal\LogController@showCrmRequestLogs');//查看所有CRM 激活请求日志的路由
	Route::post('user-operate-list','API\Normal\LogController@showUserOperateLogs');//查看所有用户操作日志的路由
    Route::post('send-mail-list','API\Normal\LogController@showSendMailLogs');//查看所有邮件发送日志的路由
    Route::post('notify-merchant-list','API\Normal\LogController@showNotifyMerchantLogs');//查看所有通知商户充值卡激活日志的路由
	Route::post('guide-mail-list','API\Normal\LogController@showGuideMailLogs');//查看所有商户引导邮件发送日志的路由
});

//搜索接口组
Route::group(['prefix' => 'search','middleware' => ['auth.client','auth:api','auth.parameters']], function () {
    Route::post('users','API\Normal\SearchController@searchUsers');//搜索客户的路由
    Route::post('purchase_records','API\Normal\SearchController@searchPurchaseRecords');//搜索购买记录的路由
    Route::post('payment_orders','API\Normal\SearchController@searchPaymentOrders');//搜索订单记录的路由
    Route::post('cash_cards','API\Normal\SearchController@searchCashCards');//搜索客户充值卡的路由
});

Route::group(['prefix' => 'gateway','middleware' => ['auth.client','auth:api','auth.parameters']],function(){
    Route::post('payment','API\Normal\OrderController@paymentForGoods')->middleware('log.operate:payment');//购买商品、生成订单、跳转支付三步一体的路由
});

//CRM 激活充值卡请求接口组
Route::group(['prefix' => 'gateway'],function(){
    Route::post('active-gcard','API\Payment\NewActiveCardController@activeCard')->middleware('log.request');//激活充值卡的路由
    Route::post('active-notify','API\Payment\ActiveNotifyMerchantController@receiveAndNotifyMerchant');//接收上游通知并转接下游通知的路由

	Route::post('callback','API\Payment\PaymentController@callback');//订单支付同步回调的路由
	Route::post('notify','API\Payment\PaymentController@notify')->middleware('log.notify');//订单支付异步通知的路由
	Route::post('active-b','API\Payment\ActiveCardController@activeCard')->middleware('log.request');//激活充值卡的路由
	Route::post('query','API\Payment\QueryCardController@queryVoucher')->middleware('log.request');//查询充值卡的路由
});
Route::group(['prefix' => 'v1/gateway'],function(){
	Route::post('active-b','API\Payment\OneActiveCardController@activeCard')->middleware('log.request');//激活充值卡的路由
});
//商户注册客户的接口
Route::post('register/customer','API\External\CRMRegisterCusController@run');
Route::post('register/a','API\External\CRMRegisterCusController@run');
//新商户注册客户接口
Route::post('register/new-customer','API\External\NewCRMRegisterCusController@run');
Route::post('register/b','API\External\NewCRMRegisterCusController@run');

//D系统请求接口组
Route::post('system/register','API\External\RegisterController@register');
Route::group(['prefix'=>'mall','middleware'=>'auth.system'],function(){
	Route::post('customers-list','API\External\MallInterfaceController@getCustomers');//获取客户信息的接口
	Route::post('goods-list','API\External\MallInterfaceController@getGoods');//获取商品信息的接口
	Route::post('purchase-list','API\External\MallInterfaceController@getPurchaseRecords');//获取购买记录的接口
    Route::post('order-list','API\External\MallInterfaceController@getPaymentOrders');//获取支付订单记录的接口
	Route::post('cards-list','API\External\MallInterfaceController@getCashCards');//获取充值卡记录的接口
	Route::post('refund-list','API\External\MallInterfaceController@getRefundRecords');//获取退款记录的接口

    Route::post('register-logs','API\External\MallInterfaceController@getRegisterLogs');//获取客户注册日志的接口
    Route::post('login-logs','API\External\MallInterfaceController@getLoginLogs');//获取客户登陆日志的接口
    Route::post('user-operate-logs','API\External\MallInterfaceController@getOperateLogs');//获取用户操作日志的接口
    Route::post('crm-activation-logs','API\External\MallInterfaceController@getActivationLogs');//获取CRM激活请求日志的接口
    Route::post('upstream-notify-logs','API\External\MallInterfaceController@getUpstreamNotifyLogs');//获取上游通知日志的接口
    Route::post('merchant-guide-mail-logs','API\External\MallInterfaceController@getMerchantGuideMailLogs');//获取商户引导邮件日志的接口
    Route::post('send-mail-logs','API\External\MallInterfaceController@getSendMailLogs');//获取邮件发送日志的接口

});

//激活测试接口组
Route::group(['prefix' => 'demo'],function(){
	Route::get('test','API\Demo\ActiveTestController@postActive');//外部激活请求接口
	Route::post('active','API\Demo\ActiveTestController@doActive');//外部激活测试接口

	Route::get('customers-list','API\Demo\ExternalRequestTestController@getCustomersList');//外部获取客户信息的测试接口
	Route::get('goods-list','API\Demo\ExternalRequestTestController@getGoodsList');//外部获取商品信息的测试接口
	Route::get('purchase-records','API\Demo\ExternalRequestTestController@getPurchaseRecords');//外部获取购买记录的测试接口
	Route::get('cards-list','API\Demo\ExternalRequestTestController@getCashCards');//外部获取充值卡记录的测试接口
	Route::get('refund-list','API\Demo\ExternalRequestTestController@getRefundRecords');//外部获取退款记录的测试接口
});

//调试接口组
Route::group(['prefix' => 'debug'],function(){
	Route::get('soft-delete','API\Debug\TestDebugController@testSoftDelete');//测试软删除
	Route::get('force-delete','API\Debug\TestDebugController@testForceDelete');//测试硬删除
	Route::get('get-contents','API\Normal\UploadFileController@getFileContents');
	Route::get('download-file','API\Normal\UploadFileController@downloadFile');

    Route::get('test-callback','API\Payment\PaymentController@testCallBack');//测试同步通知
	Route::get('supply-mail','API\Debug\MessageController@testQueueMail');//测试队列邮件
    Route::get('view-mail/{type}','API\Debug\MessageController@showMailView');
    Route::get('update-status','API\Debug\TestDebugController@changeOrderStatus');//更新订单状态
    Route::get('update-password','API\Debug\TestDebugController@updateLoginPassword');//更新用户登陆密码
});

Route::group(['prefix' => 'auth'],function(){
    Route::post('user','API\PassportController@authUser');
});
