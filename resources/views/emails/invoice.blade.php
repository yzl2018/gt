<?php
header("Content-type: text/html; charset=utf-8");
$curr_type = ['USD'=>'美元','CNY'=>'人民币'];
$curr_name = $curr_type[$Currency];
$tax_date = date('d/m/Y');
$today = date('YmdHis');
list($t1, $t2) = explode(' ', microtime());
$micro_time = (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
$invoice_no = 'SE-19/'.$today.$micro_time;
//vps 数组
$vpsinfo = [
    ['ip' => '103.105.57.157:18845',
    'user' => 'administrator',
    'pass' => 'JGDO350WbGjEw9'
    ],
    ['ip' => '103.117.132.212:10288',
        'user' => 'administrator',
        'pass' => 'FPX8Gg4dQ0bZfAjI75o'
    ],
    ['ip' => '103.105.57.189:15778',
        'user' => 'administrator',
        'pass' => '1lx2z7O2MVT'
    ],
    ['ip' => '103.105.57.186:10276',
        'user' => 'administrator',
        'pass' => 'o1cX2vXlZ9'
    ],
    ['ip' => '103.117.132.194:13733',
        'user' => 'administrator',
        'pass' => '6M7dzsE7C'
    ],
    ['ip' => '103.105.57.137:12475',
        'user' => 'administrator',
        'pass' => 'Bn7mLAmR2ZLsh7zgKG'
    ],
    ['ip' => '103.105.57.99:10730',
        'user' => 'administrator',
        'pass' => 'XzIi3xH94'
    ],
    ['ip' => '103.105.59.40:15645',
        'user' => 'administrator',
        'pass' => 'O7H83cHbLf'
    ],
    ['ip' => '103.105.57.83:10579',
        'user' => 'administrator',
        'pass' => 'D4bJ0Sz2WZ4FCg'
    ],
    ['ip' => '113.10.167.168:14915',
        'user' => 'administrator',
        'pass' => 'N9wXFqaDzyZt92k'
    ],
    ['ip' => '103.112.210.151:12991',
        'user' => 'administrator',
        'pass' => 'xP8Ei3fo4Pu9d5n'
    ],
    ['ip' => '103.117.133.236:19659',
        'user' => 'administrator',
        'pass' => 'UfI0B82rxO'
    ],
    ['ip' => '103.105.59.74:18035',
        'user' => 'administrator',
        'pass' => 'm7wZ4bZ9LgPXashKP6'
    ],
    ['ip' => '103.112.210.47:13216',
        'user' => 'administrator',
        'pass' => '0R8Kqf34vQ6'
    ],
    ['ip' => '103.105.57.107:19890',
        'user' => 'administrator',
        'pass' => 'RPlMYm52f3jG5aoTQkl'
    ]
];

//$num0 = array_fill(0, 1, 0);
// 定义一个数组，里面有5个1
//$num1 = array_fill(1, 5, 1);
// 定义一个数组，里面有10个2
//$num2 = array_fill(2, 10, 2);
// 总数组，里面总共100个元素
//$allNum = array_merge($num0);
$useVpsInfo =  $vpsinfo[array_rand($vpsinfo,1)];
//print_r($num1);
?>

<style type="text/css">
    /*标签重置*/
    body,
    h1,
    p,
    h2,
    ul,
    li {
        margin: 0;
        padding: 0;
    }

    body {
        background-color: #e0e0e0;
        color: #343434;
        font-family: '微软雅黑', '黑体', '隶书', '宋体', serif;
    }

    img {
        vertical-align: middle;
    }

    /* 公用类 */
    /* 圆角 */
    .radius-5 {
        border-radius: 5px;
        -ms-border-radius: 5px;
        -webkit-border-radius: 5px;
        -o-border-radius: 5px;
        -moz-border-radius: 5px;
    }



    /* 颜色 */


    .text-light-orange {
        color: #F9CE00
    }

    .font-bold {
        font-weight: bold;
    }

    /* 盒模型 */
    /* 垂直居中盒子 */

    .v-box,
    .v-box-box:after,
    .v-box-box:before {
        display: inline-block;
        vertical-align: middle;
    }

    .v-box-box:after,
    .v-box-box:before {
        content: '';
        height: 100%;
    }

    /* 对齐 */
    .text-center {
        text-align: center;
    }

    .indent {
        text-indent: 2em;
    }

    /* 页面 */
    /* 注册邮件 */
    .main {
        width: 650px;
        height: 976px;
        margin: 100px auto 0;
        background-color: #fff;
        overflow: hidden;
    }

    /* 头部 */
    .header {
        height: 100px;
    }

    .bt-msg{
        margin-bottom: 55px;
        margin-top:44px;
    }

    /* 内容 */
    .content {
        padding: 32px 30px 0;
    }


    .content-title {
        text-align: center;
        margin-top: 104px;
        padding-bottom: 27px;
    }

    .border {
        border: 1px solid #74AAF7;
    }

    .reg-main {
        margin: 0 35px;
    }

    .content-main h2 {
        padding-top: 55px;
        margin-bottom: 32px;
        font-weight: bold;
        text-align: center;
        font-size: 12px;
    }

    /* 注册成功页面 */
    .content-main table {
        width: 75%;
        margin: auto;
        margin-top: 30px;
        border: 1px solid #ddd;
        border-collapse: collapse;
        text-align: center;
        margin-bottom: 30px;
    }

    .content-main table td,
    .content-main table th {
        border: 1px solid #ddd;
        padding: 5px 0;
    }

    .ml{
        margin-left:164px;
        margin-top:48px;
    }

    .txt {

        font-size: 16px;
        line-height:32px;
    }

    /* 卡号卡密 */
    table.my-table {
        margin-top: 200px;
    }

    table.my-table td {
        font-weight: bold;
    }

    .bg {
        background-color:#fff;
    }


    .mail_logo{
        text-align: center;
        padding-bottom: 27px;
    }
    .mail_title{
        text-align: center;
        line-height:26px;
        font-size: 16px;
        font-weight: bold;
    }
    .mail_price{
        text-align: center;
        line-height:66px;
        font-size: 38px;
        font-weight: bold;
        color:#58b3f1;
        margin-bottom:50px;
    }
    .mail_row{
        line-height:30px;
    }
    .mail_row b{
        width:40%;
        text-align:left;
        height:auto;
    }
    .mail_row p{
        width:40%;
        text-align:left;
        height:auto;
    }
    .mail_row span{
        width:60%;
        text-align:right;
        float:right;
        height:auto;
        overflow: hidden;
    }
    .mail_row_title{
        text-align:right;
        font-size:17px;
        font-weight: 300;
    }
    .mail_line{
        height:5px;
        border-bottom:1px dashed #dedddd;
        margin-bottom:30px;
    }
    .mail_footer_title{
        margin-top:70px;
        margin-bottom:40px;
        text-align:center;
        font-size:19px;
        color:#58b3f1;
    }
    .mail_footer_row{
        line-height:20px;
        font-size:13px;
    }
    .mail_footer_row .left_con{
        width:70%;
        float:left;
        text-align:left;
    }
    .mail_footer_row .right_con{
        width:29%;
        text-align:right;
        float:left;
        border-left:1px dashed #dddddd;
    }
</style>
<div class="main radius-5 bg">

    <!-- s 内容 -->
    <div class="content">
        <div class="mail_logo">
            <img src="http://id1.gsdtechs.com/assets/images/logo.png" />
        </div>
        <div class="mail_title">
            GSD-TECH
        </div>
        <div class="mail_price">
            <?php echo $Value; ?>  <?php echo $Currency; ?>
        </div>
        <div class="mail_row">
            <b style="font-weight: 300">Order No:</b><span><?php echo $OrderNo; ?></span>
        </div>
        <div class="mail_line" style="margin-bottom: 0;"></div>
        <div class="mail_row_title" style="margin-bottom: 50px;">invoice</div>
        <div class="mail_row">
            <b>Date:</b><span><?php echo $tax_date; ?></span>
        </div>
        <div class="mail_row">
            <b>Invoice No:</b><span><?php echo $invoice_no; ?></span>
        </div>
        <div class="mail_row">
            <b>Invoice To:</b><span><?php echo $CustomerEmail; ?></span>
        </div>
        <div class="mail_row">
            <b>Address:</b><span><?php echo $CustomerEmail; ?></span>
        </div>
        <div class="mail_line"></div>
        <div class="mail_row">
            <b>名称</b><span style="height: 30px;">VPS</span>
        </div>
        <div class="mail_row">
            <b>单价</b><span><?php echo $ProPrice; ?></span>
        </div>
        <div class="mail_row">
            <b>数量</b><span><?php echo $ProNumber; ?></span>
        </div>
        <div class="mail_row">
            <b>总价</b><span><?php echo $Value;?> <?php echo $curr_name;?></span>
        </div>
        <div class="mail_line" style="margin-top: 20px;"></div>
        <div class="mail_row">
            <b>支付的数额:</b><span style="font-size:18px;font-weight:bold;"><?php echo $Value;?> <?php echo $curr_name;?></span>
        </div>

        <div class="mail_row">
            <h2>Your server is online</h2>
        </div>

        <div class="mail_row">IP:<b><?php echo $useVpsInfo['ip']?></b></div>
        <div class="mail_row">User:<b><?php echo $useVpsInfo['user']?></b></div>
        <div class="mail_row">Pass:<b><?php echo $useVpsInfo['pass']?></b></div>

        <div class="mail_footer_title" style="font-weight: 300;">
            感谢您按时支付!
        </div>

        <div class="mail_footer_row" style="font-weight: 300;">
            <div class="left_con">
                支票付款需划线<br>
                并以支票付款
            </div>
            <div class="right_con">
                For & On Behalf of <br>
                <span style="font-size:16px;">GSD-TECH</span>
            </div>
        </div>

    </div>
</div>
