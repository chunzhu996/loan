<?php
/* *
 * 功能：服务器同步通知页面
 */

?>

<?php
//如果数据不为空判断是否已经支付

	//商户订单号
	$out_trade_no = $_REQUEST['out_trade_no'];
	//支付金额
	$total_fee = $_REQUEST['total_fee'];
	//支付金额
	$trade_status = $_REQUEST['trade_status'];
	if($trade_status == '1'){
		echo "支付成功";
		$postUrl = "http://www.weibocon.cn";//回调域名
		header ("location:$postUrl");
	}else{
		echo"支付失败";
	}

?>
