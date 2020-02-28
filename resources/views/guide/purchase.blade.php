<?php
header("Content-type: text/html; charset=utf-8");

$guide_config = [
    '10034'     => [
        'login_address' => '/auth/login',
        'guide_address' => '/guide/1',
        'images'    => [
            '/assets/guide/purchase.jpg'
        ]
    ],
    '10043' => [
        'login_address' => '/auth/login',
        'guide_address' => '/guide/1',
        'images'    => [
            '/assets/guide/purchase.jpg'
        ]
    ],
    'default'   => [
        'login_address' => '/auth/login',
        'guide_address' => '/guide/1',
        'images'        => [
            '/assets/guide/purchase.jpg'
        ],
    ]
];

if(array_key_exists($merchant_code,$guide_config)){
    $merchant_info = $guide_config[$merchant_code];
}

else{
    $merchant_info = $guide_config['default'];
}

?>

<div style="width: 100%;height: auto;text-align: center;">
    <span style="display: inline-block;margin-bottom: 10px;font-size: 18px;font-weight: bold;">商城登陆地址：<a href="<?php echo $access_host.$merchant_info['login_address']; ?>" target="_blank"><?php echo $access_host.$merchant_info['login_address']; ?></a></span>
    <br>
    <span style="display: inline-block;margin-bottom: 10px;font-size: 18px;font-weight: bold;">购买流程查看地址：<a href="<?php echo $access_host.$merchant_info['guide_address']; ?>" target="_blank"><?php echo $access_host.$merchant_info['guide_address']; ?></a></span>
    <br>
</div>

<?php
foreach ($merchant_info['images'] as $image){
    ?>
    <div style="width: 100%;height: auto;text-align: center;">
        <img src="<?php echo $access_host.$image ?>" />
    </div>
    <?php
}
?>

