<?php
header("Content-type: text/html; charset=utf-8");

$guide_config = [
	'10034'	=> [
		'host'	=> 'http://voucher321.com',
		'page'	=> '/guide1.html',
		'images'	=> [
			'/images/10034-active/01.png',
			'/images/10034-active/02.png',
			'/images/10034-active/03.png',
			'/images/10034-active/04.png',
			'/images/10034-active/05.png',
			'/images/10034-active/06.png',
			'/images/10034-active/07.png',
		]
	],
	'10043'	=> [
		'host'	=> 'http://voucher321.com',
		'page'	=> '/guide2.html',
		'images'	=> [
			'/images/10043-active2/01.png',
			'/images/10043-active2/02.png',
		]
	],
	'default'	=> [
		'host'	=> 'http://id1.gsdtechs.com',
		'page'	=> '',
		'images'	=> [
			'/backend_storage/assets/images/20190816144856.png'
		]
	]
];

$merchant_info = $guide_config['default'];
if(array_key_exists($merchant_code,$guide_config)){
	$merchant_info = $guide_config[$merchant_code];
}

$access_host = $merchant_info['host'];
$view_page = $access_host.$merchant_info['page'];
$images = $merchant_info['images'];

?>
<?php

		if(array_key_exists($merchant_code,$guide_config)){
			?>
<div style="width: 100%;height: auto;text-align: center;">
	<span style="display: inline-block;margin-bottom: 10px;font-size: 18px;font-weight: bold;">激活流程查看地址：<a href="<?php echo $view_page; ?>" target="_blank"><?php echo $view_page; ?></a></span>
	<br>
</div>
<?php
		}
		else{
			?>
<div style="width: 100%;height: auto;text-align: center;">
	<span style="display: inline-block;margin-bottom: 10px;font-size: 18px;font-weight: bold;">
		付款成功后，在邮箱查收兑换码和兑换密码，<br>
		连同付款金额(RMB)，到商户充值页面输入并充值。
	</span>
	<br>
</div>
<?php
		}

?>

<?php
        foreach ($images as $image){
        	?>
            <div style="width: 100%;height: auto;text-align: center;">
				<img src="<?php echo $access_host.$image; ?>" />
            </div>
            <?php
        }
?>
