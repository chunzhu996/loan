<?php
namespace app\mobile\controller;
class Callback extends Common
{

    //运营商认证回调
    public function mobile()
    {
        $authid = input("get.authid");
        $token = input("get.token");
        $status = input("get.status");
        $uid = input("get.uid");
        if($authid && $token)
        {
            $mobileauth_model = model("Mobileauth");
            $mobileauth_model->setStatus($authid,$token,$status);
            if($status == 1)
            {
                //授权成功爬取数据
                $result = $mobileauth_model->getAuthResult($uid,$token);
                echo 'success';
            }
        }
    }

    //淘宝认证回调
    public function taobao()
    {
        $authid = input("get.authid");
        $token = input("get.token");
        $status = input("get.status");
        $uid = input("get.uid");
        if($authid && $token)
        {
            $taobaoauth_model = model("Taobaoauth");
            $taobaoauth_model->setStatus($authid,$token,$status);
            if($status == 1)
            {
                //授权成功爬取数据
                $result = $taobaoauth_model->getAuthResult($uid,$token);
                echo 'success';
            }
        }
    }

    //支付宝支付结果回调
    public function alipay()
    {
        $api = config('api');
        $alipayNotify = new \Alipay_wap\AlipayNotify([
            'sign_type'         =>          strtoupper('MD5'),
            'key'               =>          $api['alipaykey'],
            'input_charset'     =>          trim(strtolower('utf-8')),
            'transport'         =>          'http',
            'cacert'            =>          '',
            'partner'           =>          $api['alipaypid']
        ]);
        $verify_result = $alipayNotify->verifyNotify();
        if($verify_result)
        {
            $oid = $_POST['out_trade_no'];//充值订单号
            $trade_no = $_POST['trade_no'];//支付宝交易号
            $trade_status = $_POST['trade_status'];//交易状态
            $pay_mode = model("Pay");
            $pay_mode->undateOrder($oid);//更新订单
            echo "success";
        }else
        {
            echo "fail";
        }
    }


}
