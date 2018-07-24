<?php
/* *
 * 功能：服务器异步通知页面
 */

//require_once("shanpayconfig.php");
//require_once("lib/bl_md5.function.php");
?>

<?php
//如果数据不为空判断是否已经支付
header("Content-type: text/html; charset=utf-8");
	//商户订单号
	$out_trade_no = $_REQUEST['out_trade_no'];
	//支付金额
	$total_fee = $_REQUEST['total_fee'];
	//支付金额
	$trade_status = $_REQUEST['trade_status'];
	$sign = $_REQUEST['sign'];
	$time = time();
	$ip = $_SERVER["REMOTE_ADDR"];
	if($trade_status == "1"){
		
		//echo "true";
		//1、创建数据库连接对象
$mysqli = new MySQLi("localhost","root","root","kak");
if($mysqli->connect_error){
 die($mysqli->connect_error);
}
$mysqli->query("set names 'utf8'");
//2、数据插入语句
$sqls = "UPDATE cv_pay SET status = 1 WHERE ordernum = '$out_trade_no'";

$sql = $mysqli->query("SELECT * FROM cv_pay WHERE ordernum = '$out_trade_no'");

$arr = mysqli_fetch_row($sql);
$status =$arr['5'];

$uid = $arr['1'];
if($status == 0){
$sqls = "UPDATE cv_user SET money = money + '$total_fee' WHERE id = '$uid'";
$res = $mysqli->query($sqls);//返回的是布尔值
//4、判断是否执行成功
if(!$res){
 echo "数据失败";
}else{
 echo "成功！！！";
}
}else{
	echo  "请勿恶意提交";
	
	
}
//5、关闭连接
$mysqli->close();

		

		

	}else{
		
		echo "002";
		
	}

?>