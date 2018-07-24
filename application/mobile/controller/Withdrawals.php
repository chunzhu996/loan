<?php
namespace app\mobile\controller;
class Withdrawals extends Common
{

    //建立提现订单
    public function index()
    {
        $user = $this->isLogin();
        //判断是否绑定银行卡
        if(!model("Info")->ifAddBank($user['id']))
        {
            $this->error("请先绑定提现银行卡");
        }
        $money = input("money");
        $money = floatval($money);
        $money = makeMoney($money);
		$decade = config('loan');
		if($decade['ismoneycode'] =='1'){
		$moneycode = input('moneycode');
		if($decade['moneycode'] != $moneycode){
			 $this->error("验证编码不正确或您还未验证首期，请验证后重试");
		}
		}
        if($money <= 0)
        {
            $this->error("提现金额不规范");
        }
        $orderNum = date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        $withdrawals_model = model("Withdrawals");
        $user_model = model("User");
        //判断账户金额
        if(!$user_model->moneyAmple($user['id'],$money))
        {
            $this->error("账户钱包余额不足,请确认提现金额");
        }
        $loan_config = config('loan');
        if($money < $loan_config['withdrawalsminmoney'])
        {
            $this->error("提现金额不能少于{$loan_config['withdrawalsminmoney']}元");
        }
        if(!$user_model->changeMoney($user['id'],0,$money))
        {
            $this->error("账户钱包操作失败,请重试");
        }
        if(!$withdrawals_model->addOrder($user['id'],$money,$orderNum))
        {
            $this->error("订单创建失败");
        }
        $this->success('success');
    }

}
