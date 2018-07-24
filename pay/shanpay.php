<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>支付跳转中...</title>
</head>


<body>

</body>

</html>
<!--php
require_once("shanpayconfig.php");
require_once("lib/bl_md5.function.php");

/**************************请求参数**************************/
	
        //商户订单号
        $out_trade_no = $_GET['WIDout_trade_no'];//商户网站订单系统中唯一订单号，必填

        //订单名称
        $subject = $_GET['WIDsubject'];//必填

        //付款金额
        $total_fee = $_GET['WIDtotal_fee'];//必填 需为整数
		
		
		//服务器异步通知页面路径
        $notify_url = $shan_config['notify_url'];
        //需http(s)://格式的完整路径，不能加?id=123这类自定义参数

        //页面跳转同步通知页面路径
        $return_url = $shan_config['return_url'];
        //需http(s)://格式的完整路径，不能加?id=123这类自定义参数，不支持写成http://localhost/

/************************************************************/

//构造要请求的参数数组，无需改动
$parameter = array(
		"partner" => $shan_config['partner'],
		"seller_email" => $shan_config['seller_email'],
		"payment_type"	=> '易连支付',
		"out_trade_no"	=> $out_trade_no,
		"subject"	=> $subject,
		"total_fee"	=> $total_fee,
		"notify_url"	=> $notify_url,
		"return_url"	=> $return_url,
		"exter_invoke_ip"	=> $_SERVER["REMOTE_ADDR"],
		"username"	=> '0'
);
//建立请求
@$html_text = blsend($parameter,'post','立即支付',$shan_config['blpay_post'],$shan_config['key']);
echo $html_text;
?>
</body>
</html>-->
<!--<p><font size="18"><br><br><br><br><br><br><br><br>抱歉！验证通道维护，请联系客服获取个人最新验证支付二维码！<br><br>温馨提示：从左向右滑动屏幕返回</font></p>-->