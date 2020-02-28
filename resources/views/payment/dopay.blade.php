<?php

if(empty($params)){
	exit("Invalid request");
}

if(is_string($params)){
	if(is_null(json_decode($params))){
		exit("Invalid request");
    }
    else{
		$params = json_decode($params,true);
    }
}

?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
</head>
<body>
Loading...
<form name="dinpayForm" method="<?php echo $params['method']; ?>" action="<?php echo $params['url']; ?>">
	<?php
	foreach($params['data'] as $key=>$val){
		echo "<input type='hidden' name='".$key."' value='".$val."' />";
	}
	?>
    <script>
		document.dinpayForm.submit();
    </script>
</form>
</body>
</html>