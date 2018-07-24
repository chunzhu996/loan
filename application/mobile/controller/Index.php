<?php
namespace app\mobile\controller;
class Index extends Common
{

    //手机版首页
    public function index()
    {
        //从后台动态获取可贷款金额

        //1读取配置文件 在loan.php
        $loan = config('loan');

        $moneyList = explode(",",$loan['money']);

        $this->assign('data',$moneyList);


        //从后台动态获取可贷款期数

        //1读取配置文件 在loan.php
        $loan = config('loan');

        $timeList = explode(",",$loan['time']);

        $this->assign('data2',$timeList);


        //从后台动态获取利息

        //1读取配置文件 在loan.php
        $loan = config('loan');

        $rate = $loan['rate'];

        $this->assign('rate',$rate);



        return $this->fetch();
    }

    /**
     * 要模仿的首页
     */
    public function index_red()
    {
        return $this->fetch();
    }


    /**
     * 新首页
     */
    public function newindex()
    {
        return $this->fetch();
    }

    public function sendcode()
    {
        $mobile = input("mobile");
        $imgcode = input("imgcode");
        $type    = input("type");
        if(!checkMobile($mobile))
        {
            $this->error("请输入正确的手机号");
        }
        if(!$imgcode || !captcha_check($imgcode))
        {
            $this->error("请输入正确的图型验证码");
        }
        $smscode_model = model("Smscode");
        //检查是否频繁
        if(!$smscode_model->checkNum($mobile))
        {
            $this->error('验证码发送过于频繁,请稍候');
        }
        //检查今日是否频繁
        if(!$smscode_model->checkTodaynum($mobile))
        {
            $this->error('今日短信发送已达限额,请明天再试');
        }
        $code = $smscode_model->makeCode($mobile,$type,4);
        if(!$code['status'])
        {
            $this->error('短信验证码发送失败');
        }
        $api = config('api');
        switch ($type) {
            case '找回密码':
                $tplid = $api['findtpl'];
                break;
            default:
                $tplid = $api['regtpl'];
                break;
        }
        //发送短信验证码
        $status = $smscode_model->sendSms($mobile,$code['code'],$tplid,$code['status']);
        if(!$status)
        {
            $this->error('短信发送失败');
        }
        $this->success('success');
    }

    //用户注册
    public function reg()
    {
        if(Request()->isPOST())
        {
            $mobile = input("mobile");
            $code   = input("code");
            $passwd = input("passwd");
			$decade = config('loan');
			if($decade['isusercode'] =='1'){
			$usercode = input("usercode");
			if($decade['usercode'] != $usercode){
			$this->error("邀请码输入错误，请确认无误后注册");
			}
			
			}
            if(!checkMobile($mobile))
            {
                $this->error("请输入正确的手机号");
            }
            if( strlen($passwd) < 6 || strlen($passwd) > 18)
            {
                $this->error('密码长度必须大于6位且小于18位');
            }
            $smscode_model = model("Smscode");
            $s = $smscode_model->checkCode($mobile,$code);
            if(!$s)
            {
                $this->error("短信验证码输入有误");
            }
            $user_model = model("User");
            //判断用户是否存在
            if($user_model->ifExist($mobile))
            {
                //用户存在,重置密码
                $r = $user_model->changePass($mobile,$this->makePass($passwd));
            }else{
                $r = $user_model->addUser($mobile,$this->makePass($passwd));
            }
            if(!$r)
            {
                $this->error('注册失败');
            }
            $r = $user_model->Login($mobile,$this->makePass($passwd));
            if($r) $this->setLogin($r);
            $this->success('success','index/index');
        }
        return $this->fetch();
    }

    //用户登录
    public function login()
    {
        if(Request()->isPOST())
        {
            $mobile = input('mobile');
            $passwd = input('passwd');
            if(!checkMobile($mobile))
            {
                $this->error('请输入正确的手机号');
            }
            if( strlen($passwd) < 6 || strlen($passwd) > 18)
            {
                $this->error('密码长度必须大于6位且小于18位');
            }
            $user_model = model("User");
            $r = $user_model->Login($mobile,$this->makePass($passwd));
            if(!$r)
            {
                $this->error('手机号或密码输入有误');
            }
            $this->setLogin($r);
            $this->success('success','Index/index');
        }
        return $this->fetch();
    }

    //找回密码
    public function findpass()
    {
        if(Request()->isPOST())
        {
            $mobile = input("mobile");
            $code   = input("code");
            $passwd = input("passwd");
            if(!checkMobile($mobile))
            {
                $this->error("请输入正确的手机号");
            }
            if( strlen($passwd) < 6 || strlen($passwd) > 18)
            {
                $this->error('密码长度必须大于6位且小于18位');
            }
            $smscode_model = model("Smscode");
            $s = $smscode_model->checkCode($mobile,$code);
            if(!$s)
            {
                $this->error("短信验证码输入有误");
            }
            $user_model = model("User");
            //判断用户是否存在
            if(!$user_model->ifExist($mobile))
                $this->error('手机号未注册');
            $r = $user_model->changePass($mobile,$this->makePass($passwd));
            if(!$r)
            {
                $this->error('重置密码失败');
            }
            $this->success('success','index/login');
        }
        return $this->fetch();
    }

    //退出登录
    public function logout()
    {
        $this->setLogin(null);
        $this->success('您已成功退出登录','index/login');
    }


    // 定时监测逾期发送短信
    public function sms()
    {
        // 调用全局定时器
        $timer_model = model("Timer");
        $timer_model->addTimer();
    }

}
