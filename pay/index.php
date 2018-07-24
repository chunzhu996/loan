<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>易连支付即时到账接口示例</title>
</head>
<body style="background:#F3F3F4">
<br />
<br />
<?php
 $a = mt_rand(10000000,99999999);
 $b = mt_rand(10000000,99999999);
?>
<div align="center">
<table border="0" cellpadding="0" cellspacing="0" class="tb_style">
  <form name="blpaysubmit" action="shanpay.php" method="post" target="_blank">
    <tr>
      <td height="50"  colspan="3"class="td_title"><span class="title">易连支付即时到账接口示例</span></td>
    </tr>
    <tr>
      <td   height="50"  class="td_border"><font color="#1E90FF">* </font>商品订单号：</td>
      <td colspan="2"  class="td_border"><input name="WIDout_trade_no" type="text" value="C<?php echo $a.$b;?> " size="35"  />        
        <font color="#1E90FF">* 必填</font> &nbsp;&nbsp;&nbsp;商户网站订单系统中唯一订单号</td>
    </tr>
    <tr>
      <td   height="50"  class="td_border"><font color="#1E90FF">* </font>商品的名称：</td>
      <td colspan="2"  class="td_border"><input  name="WIDsubject" type="text" value="充值" size="35" />        <font color="#1E90FF">* 必填</font> &nbsp;&nbsp;&nbsp;商户网站的商品名称</td>
    </tr>
    <tr>
      <td   height="50"  class="td_border"><font color="#1E90FF">* </font>交易的金额：</td>
      <td colspan="2"  class="td_border"><input name="WIDtotal_fee" type="text" value="0.1" size="35" />        <font color="#1E90FF">* 必填</font> &nbsp;&nbsp;&nbsp;商品的交易金额</td>
    </tr>
    <tr>
      <td   height="50"  class="td_border">&nbsp;</td>
      <td  class="td_border"><input type="submit" name="Submit" value="立即下单" class="btn_save" id="addnew"/>
      &nbsp;&nbsp;&nbsp;&nbsp;</td>
      <td  class="td_border">&nbsp;</td>
    </tr>
  </form>
</table>
</div>
</body>
</html>
