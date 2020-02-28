<?php

if(empty($active_uri)){
	exit('Bad request');
}

?>

<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <!-- Styles -->
    <link href="{{ asset('bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body>
<div class="container" style="margin-top: 50px;">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <div class="panel panel-color panel-orange">
                <div class="panel-heading">
                    <h3 class="panel-title">G-CARD 充值</h3>
                </div>
                <div class="panel-body">
                    <div class="custom-dd dd">
                        <form class="form-horizontal" method="post" action="<?php echo $active_uri; ?>">
							<div class="form-group">
								<label for="voucherValue" class="col-sm-3 control-label" style="font-size: 13px;">充值面额:</label>
								<div class="col-sm-8" style="margin-top: 5px;">
									<input type="number" id="voucherValue" value="1" name="voucherValue" class="form-control">
								</div>
							</div>
							<div class="form-group">
								<label for="currency" class="col-sm-3 control-label" style="font-size: 13px;">充值货币:</label>
								<div class="col-sm-8" style="margin-top: 5px;">
									<select id="currency" name="currency" class="form-control">
										<option value="CNY">人民币</option>
										<option value="USD">美元</option>
									</select>
								</div>
							</div>
                            <div class="form-group">
                                <label for="orderAmount" class="col-sm-3 control-label" style="font-size: 13px;">充值卡号:</label>
                                <div class="col-sm-8" style="margin-top: 5px;">
                                    <input type="text" id="orderAmount" value="MN1807MKBET2JALI" name="voucherNo" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="voucherKey" class="col-sm-3 control-label" style="font-size: 13px;">充值卡密码:</label>
                                <div class="col-sm-8" style="margin-top: 5px;">
                                    <input type="password" id="voucherKey" value="U73JxK" name="voucherKey" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-offset-3 col-sm-8" style="text-align: left;">
                                    <button type="submit" id="submitBtn" class="btn btn-danger waves-effect waves-light">
确认充值
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
