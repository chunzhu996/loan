<?php
/**
 * 数据签名
 * @param $prestr 需要签名的字符串
 * @param $key 私钥
 * return 签名结果
 */
function md5Sign($prestr, $key) {
	$prestr = $prestr . $key;
	return md5($prestr);
}

/**
 * 验证签名
 * @param $prestr 需要签名的字符串
 * @param $sign 签名结果
 * @param $key 私钥
 * return 验证签名结果
 */
function md5Verify($prestr, $sign, $key) {
	$prestr = $prestr . $key;
	$mysgin = md5($prestr);

	if($mysgin == $sign) {
		return true;
	}
	else {
		return false;
	}
}
/**
 * 自动提交表单
 * @param $para_temp 需要提交的数组
 * @param $method 表单提交方式post或者get
 * @param $button_name 提交按钮name属性
 * @param $url 表单提交地址
 * @param $key 私钥
 * return 表单
 */
function blsend($para_temp, $method, $button_name,$url,$key) {
		//待请求参数数组
		$para = buildRequestPara($para_temp,$key);

		$sHtml = "<form id='blpaysubmit' name='blpaysubmit' action='http://www.paysuyou.com/index.php/go/index/pay' accept-charset='utf-8' method='POST'>";
		while (list ($key, $val) = each ($para)) {
            $sHtml.= "<input type='hidden' name='".$key."' value='".$val."'/>";
        }

		//submit按钮控件请不要含有name属性
        $sHtml = $sHtml."<input type='submit' value='".$button_name."'></form>";
		
		$sHtml = $sHtml."<script>document.forms['blpaysubmit'].submit();</script>";
		
		return $sHtml;
	}
	/**
 * 对需要提交的数组进行整理和添加签名参数
 * @param $para_temp 需要提交的数组
 * @param $key 私钥
 * return 整理后的数组
 */
function buildRequestPara($para_temp,$key) {
		//除去待签名参数数组中的空值和签名参数
		$para_filter = paraFilter($para_temp);

		//对待签名参数数组排序
		$para_sort = argSort($para_filter);

		//生成签名结果
		$mysign = buildRequestMysign($para_sort,$key);
		
		//签名结果加入请求提交参数组中
		$para_sort['sign'] = $mysign;
		
		return $para_sort;
	}
//除去待签名参数数组中的空值和签名参数
	function paraFilter($para) {
	$para_filter = array();
	while (list ($key, $val) = each ($para)) {
		if($key == "sign" || $key == "sign_type" || $val == "")continue;
		else	$para_filter[$key] = $para[$key];
	}
	return $para_filter;
}
/**
 * 对数组排序
 * @param $para 排序前的数组
 * return 排序后的数组
 */
function argSort($para) {
	ksort($para);
	reset($para);
	return $para;
}
//把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
function buildRequestMysign($para_sort,$key) {
		
		$prestr = createLinkstring($para_sort);
		$mysign = "";
        $mysign = md5Sign($prestr, $key);
        return $mysign;
	}
	/**
 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
 * @param $para 需要拼接的数组
 * return 拼接完成以后的字符串
 */
function createLinkstring($para) {
	$arg  = "";
	while (list ($key, $val) = each ($para)) {
		$arg.=$key."=".$val."&";
	}
	//去掉最后一个&字符
	$arg = substr($arg,0,count($arg)-2);
	
	//如果存在转义字符，那么去掉转义
	if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}
	
	return $arg;
}
	/**
 * 验签
 * @param $data 需要验签的数组，数组必须包含以下参数否则验签失败
        $out_trade_no;
        $total_fee; 
        $trade_status;
	    $username;
		$sign;
 * return 验签结果
 */

	function verifypost($data){	
	 $url="http://www.paysuyou.com/index.php/go/index/pay";
     $ch = curl_init($url);
     curl_setopt($ch, CURLOPT_URL, $url);
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     curl_setopt($ch, CURLOPT_POST, 1);
     curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
     $output = curl_exec($ch);
     curl_close($ch);
    
	 return $output;
	 }
?>