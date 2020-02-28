<?php
header("Content-type: text/html; charset=utf-8");
?>
<h3>{{$subject}}</h3>
<br>
通道名称：{{$channel_name}}
<br>
驱动：{{$driver}}
<br>
邮局地址：{{$host}}
<br>
端口：{{$port}}
<br>
恭喜您在{{$send_time}}，测试成功！