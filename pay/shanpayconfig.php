<?php
//↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
//请登录商户后台查看支付相关参数

//易连支付商户PID
$shan_config['partner']		= '23022787';
//易连支付商户邮箱
$shan_config['seller_email'] = '251022816@qq.com';
//易连支付商户key
$shan_config['key']		= 'b2c4d857607f9c8c298203e8cb22b519';

$url = $_SERVER['SERVER_NAME'];
// 服务器异步通知页面路径  需http(s)://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
$shan_config['notify_url'] = "http://www.weibocon.cn/pay/notify_url.php";

// 页面跳转同步通知页面路径 需http(s)://格式的完整路径，不能加?id=123这类自定义参数，必须外网可以正常访问
$shan_config['return_url'] = "http://www.weibocon.cn/mobile/user/coin/";

//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

?>