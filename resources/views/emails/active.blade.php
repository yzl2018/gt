<style type="text/css">
    /*标签重置*/
    body,h1,p,h2,ul,li{
        margin:0;
        padding:0;
    }
    body{
        background-color: #e0e0e0;
        color:#343434;
        font-family: '微软雅黑','黑体','隶书','宋体',serif;
    }
    img{
        vertical-align: middle;
    }
    /* 公用类 */
    /* 圆角 */
    .radius-5{
        border-radius: 5px;
        -ms-border-radius: 5px;
        -webkit-border-radius: 5px;
        -o-border-radius: 5px;
        -moz-border-radius: 5px;
        behavior:url(http://id1.gsdtechs.com/backend_storage/assets/images/ptc/pie.htc);/*兼容IE8 */
    }
    .bd-box{
        box-sizing:border-box;
        -ms-box-sizing:border-box;
    }
    /* 颜色 */
    .text-orange{
        color:#F66E19;
    }
    .text-light-orange{
        color:#FAA21E
    }
    .font-bold{
        font-weight: bold;
    }
    /* 盒模型 */
    /* 垂直居中盒子 */

    .v-box,
    .v-box-box:after,
    .v-box-box:before{
        display: inline-block;
        vertical-align: middle;
    }
    .v-box-box:after,
    .v-box-box:before{
        content:'';
        height:100%;
    }
    /* 对齐 */
    .text-center{
        text-align: center;
    }
    .indent{
        text-indent:2em;
    }
    /* 页面 */
    /* 注册邮件 */
    .main{
        width:650px;
        height:776px;
        margin:100px auto 0;
        background-color: #F7F6F6;
        overflow:hidden;
    }
    /* 头部 */
    .header{
        background: url(http://id1.gsdtechs.com/backend_storage/assets/images/bg-head.png) no-repeat;
        height:100px;
        width:100%;
    }
    .header img{
        margin-left: 50px;
        margin-top:29px;
    }
    /* 内容 */
    .content {padding:32px 30px 0;}
    .content-title{
        text-align:center;
        padding-bottom:27px;
        border-bottom:1px #ddd dashed;
    }
    .content-main{
        margin:30px 0;
        border:1px solid #FFDC24;
        height:227px;

    }
    .content-main h2{
        padding-top:30px;
        text-align:center;
        font-size:12px;
    }
    .content-main>a{
        padding:30px 50px 0;
        display:block;
        word-break: break-all;
        color:#5699FA;
    }
    .gray-area{
        font-size:12px;
        line-height:2em;
        margin-top:120px;
        background-color: #F7F6F6;
        width:100%;
        height: 80px;
        padding:0 50px;
    }
    .gray-area p{
        padding-left:2em;
    }
    .bg{
        background-repeat: no-repeat;
        background-position: left center;
    }
    .bg-home{
        background-image:url(http://id1.gsdtechs.com/backend_storage/assets/images/bg-home.png);
    }
    .bg-email{
        background-image:url(http://id1.gsdtechs.com/backend_storage/assets/images/bg-email.png);
    }
    .footer>p{
        height: 100px;
        text-align:center;
    }


    /* 注册成功页面 */
    .content-main  table{
        width:75%;
        margin:auto;
        border:1px solid #ddd;
        border-collapse: collapse;
        text-align:center;
        margin-bottom: 30px;
    }
    .content-main table td,
    .content-main table th{
        border:1px solid #ddd;
        padding:5px 0;
    }
    .line{
        color:#FAA21E;

    }
    .line-box{
        position: relative;
    }
    .line-box:before,
    .line-box:after{
        position: absolute;
        width:100px;
        height:2px ;
        display:inline-block;
        background-color: #FAA21E;
        content:'';
        top:0.75em;
    }
    .line-box:before{
        left:-130px;
    }
    .line-box:after{
        right:-150%;
    }
    .txt {
        margin-left: 3em;
        color:white;
        font-size:20px;
    }

    /* 消费兑换券 */
    .content-main table.use-table{
        margin-top:70px;
        width:312px;
    }

    /* 英文邮件 */
    .content-title-en h1{
        font-size:20px;
    }
    .content-title-en p{
        font-size:12px;
        text-align:right;
        margin-top: 34px;
        border-bottom:1px dashed #d0d0d0;
    }

    .content-main-en h2{
        text-align: right;
        font-size:16px;
        font-weight: bold;
    }
    .content-main-en ul{
        list-style: none;
        line-height: 1.8em;
        font-size:14px;
    }

    .content-main-en>table{
        width:100%;
        text-align: center;
        line-height: 3em;
    }
    .content-main-en>table th{
        border-bottom:1px #d0d0d0 dashed;
        font-size:16px;
    }
    .content-main-en>table td{
        font-size:12px;
    }
    .content-main-en>table th:first-child,
    .content-main-en>table td:first-child{
        text-align: left;
    }
    .content-main-en>table th:last-child,
    .content-main-en>table td:last-child{
        text-align: right;
    }

    .content-main-en>p{
        text-align:right;
        font-size:12px;
        font-weight: bold;
    }
    .content-main-en>p>strong{
        font-size:20px;
        color:#F66E19;
        line-height:2.5em;
    }
    .footer-en table{
        font-size:12px;
    }
    .footer-en table caption{
        font-size:20px;
        font-weight: bold;
        line-height: 5em;
    }
    .footer-en table td{vertical-align: top;}
    .footer-en table td:first-child{
        width:289px;
    }
    .footer-en table td>p:first-child{
        margin-bottom: 1em;
    }
    .footer-en table td>strong{
        font-size:16px;
        color:#F66E19;
        line-height: 2em;
    }
</style>


<div class="main radius-5">
    <!-- s 头部 -->
    <div class="header">
        <img src="http://id1.gsdtechs.com/backend_storage/assets/images/logo.png" />
    </div>
    <!-- e 头部 -->

    <!-- s 内容 -->
    <div class="content">
        <!-- 内容头部 -->
        <div class="content-title text-orange">
            <h1>用户注册激活成功</h1>
        </div>
        <!-- 内容主体 -->
        <div class="content-main">
            <h2>您好，您的邮箱已激活成功！</h2>
            <table>
                <thead>
                <tr>
                    <th>注册邮箱</th>
                    <th>登录密码</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>{{$Email}}</td>
                    <td class="text-light-orange font-bold">{{$LoginPassword}}</td>
                </tr>
                </tbody>
            </table>
            <div class="text-center"><b>请保管好您的信息，登陆后可修改自己的登陆密码！</b></div>
        </div>
    </div>
    <!-- e 内容 -->
    <!-- 尾部 -->
    <div class="footer">
        <div class="gray-area bd-box text-center v-box-box">
            <div class="v-box ">
                领导流行，展现品位，诚信至上，值得信赖，服务品质，顾客至上。
            </div>
        </div>
        <p class="v-box-box ">
                        <span class="v-box line-box">
                            <b class="line">Thank you</b>
                        </span>
        </p>
    </div>
</div>
