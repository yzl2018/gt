<?php
header("Content-type: text/html; charset=utf-8");
$show_activation = true;
$sent_activation_guide = config('system.sent_activation_guide');
if(in_array($mer_code,$sent_activation_guide)){
	$show_activation = true;
}
?>
<!DOCTYPE html>
<html lang="en" >

<head>
    <meta charset="UTF-8">
    <title>Sent guide mail</title>
    <!-- Styles -->
    <link href="http://id1.gsdtechz.com/assets/guide/guide-mail.css" rel="stylesheet">
</head>

<body>

<div class="container">

    <div class="login">
        <h1 class="login-heading">
            <strong>Guidance mail:</strong> <br>Registration and Purchase.</h1>

            <input type="text" id="email_purchase" name="email_purchase" placeholder="TO：Customer Email" required="required" class="input-txt" />
            <div class="login-footer">
                <p id="purchase_span" class="lnk">
                    Help customers complete purchases
                </p>
                <button type="button" onclick="PrepareForSent('purchase')" class="btn btn--right">Send mail  </button>

            </div>

    </div>

    <?php
        if($show_activation){
        	?>
    <div  class="login">
        <h1 class="login-heading">
            <strong>Guidance mail.</strong><br> Exchange Voucher Activation.</h1>

        <input type="text" id="email_activation" name="email_activation" placeholder="TO：Customer Email" required="required" class="input-txt" />
        <div class="login-footer">
            <p id="activation_span" class="lnk">
                Help customers complete activation
            </p>
            <button type="button" onclick="PrepareForSent('activation')" class="btn btn--right">Send mail  </button>

        </div>

    </div>
        <?php
        }
    ?>

</div>

<script type="text/javascript" src="http://id1.gsdtechz.com/assets/guide/jQuery-3.2.1.min.js"></script>
<script>

	function PrepareForSent(type){
        var email = document.getElementById('email_'+type).value;

        if(email === "" || email === null){
        	alert("Please enter customer email");
        }
        else{

			span_id = type + '_span';
			var span_html = "<span style=\"font-size: 16px;color: #a6b8cc;padding-right: 5px;\">Request in progress</span>\n" +
				"                    <img src=\"http://id1.gsdtechz.com/assets/guide/5-1503130Q911.gif\" />";
			$("#"+span_id).html(span_html);

			$.ajax({
				type: "post",
				url:"/api/guide/prepare-sent/"+type,
				contentType: "application/json;charset=utf-8",
				data :JSON.stringify(
					{
						'email' : email,
						'mer_code'  : '{{$mer_code}}',
						'client_ip' : '{{$client_ip}}',
						'access_token' : '{{$access_token}}',
					}
				),
				dataType: "json",
				beforeSend: function (XMLHttpRequest) {
					//XMLHttpRequest.setRequestHeader("token", "eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiIxOD.....");
				},
				success: function (resp) {
					if(resp.code === 0xFFF){
						SendMailToCustomer(resp.data);
					}
					else{
						$("#"+span_id).html('<span style="color: #f1ff0a;">'+resp.message+'</span>');
					}
				},error:function(error){
					alert('Network anomaly');
					console.log(error);
				}
			});

        }

    }

    function SendMailToCustomer(params){

		$.ajax({
			type: "post",
			url:"http://103.117.132.40/api/guide/sent-mail",
			contentType: "application/json;charset=utf-8",
			data :JSON.stringify(
				// {
				// 	'Sign' : '998b515b5c64f8b7a246d1c2608c2094f75649694c6aeb8b3cc6453abb43b3cba3d3a2a2b71b8260f4bfd7cd318ee241bb18fd28398530e7836263f5898a401b',
				// 	'Data'  : '1WRyVWbvR3c1NkIsISMyADMxIiOiUGZvNEduFG6j%XZN%yefikTMx4yM34SMuYTMxIiOiAXS05WZpx2wi*SOxEDO1ojIlBXeUVGZpV3Ri*iIt92YuMjNxA0MxUjM3YDN5EDZ0%iOi*W6hQ=='
				// }
				params
			),
			dataType: "json",
			beforeSend: function (XMLHttpRequest) {
				//XMLHttpRequest.setRequestHeader("token", "eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiIxOD.....");
			},
			success: function (resp) {
				if(resp.Code === 0xE10){
					$("#"+span_id).html('<span style="color: #20f605;">'+resp.Message+'</span>');
				}
				else{
					$("#"+span_id).html('<span style="color: #f1ff0a;">'+resp.Message+'</span>');
				}
			},error:function(error){
				alert('Network anomaly');
				console.log(error);
			}
		});

    }

</script>

</body>

</html>
