
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
        color: #000000;
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
        height: 776px;
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
        margin-top: 184px;
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
        background: url(/backend_storage/assets/images/view-bg.png) center center no-repeat;
    }

</style>
<div class="main radius-5">

    <!-- s 内容 -->
    <div class="content">
        <!-- 内容头部 -->
        <div class="content-title ">
            <h1><?php echo $type_name; ?></h1>
        </div>
        <!-- 内容主体 -->
        <div class="content-main border radius-5 reg-main">
            <h2>您好，您正在我们商城进行<?php echo $type_name; ?>的操作。</h2>
            <table class="">
                <thead>
                <tr>
                    <th>验证码</th>
                    <td class="text-light-orange font-bold"><?php echo $code; ?></td>
                </tr>

                <tr>
                    <th>过期时间</th>
                    <td><?php echo $expires_time; ?></td>
                </tr>
                </tbody>
            </table>
            <div class="text-center bt-msg"><b>请尽快填写完成验证，为了您的账户安全，请勿外泄！</b></div>

        </div>
    </div>
    <!-- e 内容 -->
    <!-- 尾部 -->
    <div class="header v-box-box ml">
        <div class="txt v-box" style="border: none;background: none;">
            <p>领导流行，展现品位，诚信至上，</p>
            <p class="indent">值得信赖，服务品质，顾客至上。</p>
        </div>
    </div>

</div>
