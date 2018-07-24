<?php
namespace app\mobile\controller;
class User extends Common
{

    //用户中心
    public function index()
    {
        return $this->fetch();
    }


    //用户中心
    public function usercenter()
    {
        return $this->fetch();
    }


    //立即认证
    public function userconfirm()
    {
        return $this->fetch();
    }

    //我的钱包
    public function coin()
    {	 $user = $this->isLogin();
		$data = db('withdrawals')->where('uid',$user['id'])->order('id desc')->find();
		$this->assign('isds',$data['status']);
        return $this->fetch();
    }

    //获取用户信息
    public function getuserinfo()
    {
        $user = $this->isLogin();
        $user_model = model("User");
        $info = $user_model->getInfo($user['mobile']);
        if(!$info)
        {
            $this->error("获取失败");
        }
        $info['money'] = number_format($info['money'],2);
        $info['their'] = number_format($info['their'],2);
        $this->success('success','',$info);
    }

    //用户钱包充值
    public function payin()
    {

    }

    //用户钱包提现
    public function payout()
    {

    }
	public function vipcard()
    {
		return $this->fetch();
    }


    //关于我们
    public function aboutUs()
    {
        return $this->fetch();
    }
	
}
