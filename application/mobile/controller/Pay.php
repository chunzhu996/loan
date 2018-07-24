<?php
namespace app\mobile\controller;
class Pay extends Common
{

    //建立充值订单
    public function newOrder()
    {
        $user = $this->isLogin();
        $money = input("money");
        $money = floatval($money);
        $money = makeMoney($money);
        if($money <= 0)
        {
            $this->error("充值金额不规范");
        }
        $orderNum = date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        $pay_model = model("Pay");
        if(!$pay_model->addOrder($user['id'],$money,$orderNum))
        {
            $this->error("订单创建失败");
        }
      $this->success('正在跳转', '/pay/shanpay.php?WIDout_trade_no='.$orderNum.'&WIDsubject=钱包充值&WIDtotal_fee='.$money);
	  exit;
    }

    //发起支付
    public function index()
    {
        $orderNum = input("order");
        if(!$orderNum)
        {
            $this->error("非法参数");
        }
       
     
        return $this->fetch();
    }

}
