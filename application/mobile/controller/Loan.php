<?php
namespace app\mobile\controller;
use think\Session;

class Loan extends Common
{

    //借款申请(确认页面)
    public function index()
    {
        $user = $this->isLogin();
        $info_model = model("Info");
        if(!$info_model->ifAddBank($user['id']))
        {
            $this->redirect('/mobile/user/userconfirm');
        }
        $money = input('post.borrowMoney');
        $time  = input('post.borrowTime');
        $money = floatval($money);
        if(!$money)
            $this->error("非法操作");
        $time = intval($time);
        if(!$time)
        {
            $this->error("非法操作");
        }
        $loan = config('loan');
        $borrowMoney = explode(',',$loan['money']);
		$borrowTime = explode(',',$loan['time']);
        if(!in_array($money,$borrowMoney)) $this->error("借款金额不允许");
        if(!in_array($time,$borrowTime)) $this->error("借款期限不允许");
        $arr = $this->makeLoanData($money,$time);
        $this->assign('data',$arr);


        //从后台动态获取利息

        //1读取配置文件 在loan.php
        $loan = config('loan');

        $rate = $loan['rate'];

        $this->assign('rate',$rate);



        return $this->fetch();
    }


    //借款处理
    public function confirmLoan()
    {
        $user = $this->isLogin();
        $info_model = model("Info");
        if(!$info_model->ifAddBank($user['id']))
        {
            $this->redirect('User/index');
        }
        $loan_model = model("Loan");
         
        if(db('loan')->where(['uid'=>$user['id'],'status'=>['<>',1]])->find()) $this->error("您有未审核或未还款订单,暂时不能发起新借款");
        // 检测当前账户是否有余额扣除审核费用
        $servicecharge = input('post.servicecharge'); // 审核费用
        if(!$loan_model->canBalance($user['id'], $servicecharge)) $this->error("账户余额不足支付审核费用,暂时不能发起借款,请联系客服充值");

        $money = input('post.money');
        $time  = input('post.time');
        $money = floatval($money);
        if(!$money) $this->error("非法操作");
        $time = intval($time);
        if(!$time) $this->error("非法操作");
        $loan = config('loan');
        $borrowMoney = explode(',',$loan['money']);
        $borrowTime = explode(',',$loan['time']);
        if(!in_array($money,$borrowMoney)) $this->error("借款金额不允许");
        if(!in_array($time,$borrowTime)) $this->error("借款期限不允许");
        $arr = $this->makeLoanData2($money,$time);
        $data = [
            'money' =>  $money,
            'time'  =>  $time,
            'repaymenttype' =>  $loan['repaymenttype'],
            'realmoney' =>  $arr['realMoney'],
            'more'  =>  $arr
        ];
        $r = $loan_model->addBorrow($user['id'],$data);
        if(!$r) $this->error('借款订单已经提交,请耐心等待审核结果');

        // 创建订单后直接扣除服务费用
        $s = $loan_model->buckle($user['id'],$r); // 用户id，订单id
        if (!$s) $this->error('借款订单已经提交,请耐心等待审核结果');

        $this->success("success",'Loan/lists');
    }

    //组合计算借款信息
    private function makeLoanData($money,$time)
    {
        $loan = config('loan');
        $rate = $loan['rate'];//费率
        // 服务费 -- 直接从余额扣除
        $servicecharge = $loan['serviceStatus'] == 1 ? (($loan['servicecharge'] / 100 )*$money) : null;//服务费(贷款金额1% -- 后台开启服务费用)
        $rateMoney = makeMoney(($money*$rate) / 100 * $time);//利息费用

        $time_n = $loan['time_n'];
        $nowtime = time();
        $repaymenttype = $loan['repaymenttype'];
        if($time_n == 'day')
        {
            $lastTime = $nowtime + $time * 24 * 60 * 60;
            $time_n_str = '天';
        }else
        {
            $lastTime = strtotime("+".$time." months");
            $time_n_str = '月';
        }
        $time2=time()+30*24*3600;
        $lastTime=date('Y年m月d日',$time2);
        if($repaymenttype)
        {
            $allMoney = makeMoney($money + $rateMoney);
            $realmoney = $money;
        }else
        {
            $allMoney = makeMoney($money);
            $realmoney = makeMoney($money-$rateMoney);
        }
        $data = [
            'money'         =>      $money,
            'time'          =>      $time,
            'rate'          =>      $rate,
            'servicecharge' =>      $servicecharge,
            'rateMoney'     =>      $rateMoney,
            'time_n'        =>      $time_n_str,
            'lastTime'      =>      $lastTime,
            'allMoney'      =>      $allMoney,
            'realMoney'     =>      $realmoney
        ];
        return $data;
    }

    private function makeLoanData2($money,$time)
    {
        $loan = config('loan');
        $rate = $loan['rate'];//费率
        // 服务费 -- 直接从余额扣除
        $servicecharge = $loan['serviceStatus'] == 1 ? (($loan['servicecharge'] / 100 )*$money) : null;//服务费(贷款金额1% -- 后台开启服务费用)
        $rateMoney = makeMoney(($money*$rate) / 100 * $time);//利息费用

        $time_n = $loan['time_n'];
        $nowtime = time();
        $repaymenttype = $loan['repaymenttype'];
        if($time_n == 'day')
        {
            $lastTime = $nowtime + $time * 24 * 60 * 60;
            $time_n_str = '天';
        }else
        {
            $lastTime = strtotime("+".$time." months");
            $time_n_str = '月';
        }

        if($repaymenttype)
        {
            $allMoney = makeMoney($money + $rateMoney);
            $realmoney = $money;
        }else
        {
            $allMoney = makeMoney($money);
            $realmoney = makeMoney($money-$rateMoney);
        }
        $data = [
            'money'         =>      $money,
            'time'          =>      $time,
            'rate'          =>      $rate,
            'servicecharge' =>      $servicecharge,
            'rateMoney'     =>      $rateMoney,
            'time_n'        =>      $time_n_str,
            'lastTime'      =>      $lastTime,
            'allMoney'      =>      $allMoney,
            'realMoney'     =>      $realmoney
        ];
        return $data;
    }

    //我的借款
    public function lists()
    {
       // var_dump(Session::get('CVPHP_HOME')['id']);
        return $this->fetch();
    }

    //获取借款列表
    public function getList()
    {
        $user = $this->isLogin();
        $loan_model = model("Loan");
        $page = input("page");
        if(!$page) $page = 1;
        $lists = $loan_model->getList($user['id'],$page);
        if(!$lists) $this->error("没有更多数据了");


        $name = model("Info")->where(['uid'=>$user['id']])->find();
        $username = '';

        if($name)
        {
            $name = $name->toArray();
            $username = $name['name'];
            for($i=0;$i<count($lists);$i++)
            {

                $lists[$i]['username'] = $username;
                $lists[$i]['idcard'] = $name['idcard'];
            }
        }

        $this->success('success','',$lists);
    }




    public function getUserName()
    {

        $id = Session::get('CVPHP_HOME')['id'];
        $user = model("Info");
        $result = $user->where(['uid'=>$id])->find();
        return $result;

    }

    //订单还款
    public function repayment()
    {
        $oid = input("oid");
        if(!$oid) $this->error('订单参数有误');
        $loan_model = model("Loan");
        $result = $loan_model->rePayment($oid);
        if(!$result) $this->error('操作失败');
        if($result == 1) $this->success('success');
        if($result == 2) $this->error('请联系客服获取验证二维码');
    }





    //充值页面  二维码

    public function erweima()
    {
        $ErweimaModel = model('Erweima');

        $which_one = $ErweimaModel->where(['is_using'=>1])->find();
        if($which_one)
        {
            $this->assign('data',$which_one->toArray());
        }
        return $this->fetch();
    }

    public function payCenter()
    {
        return $this->fetch();
    }


}
