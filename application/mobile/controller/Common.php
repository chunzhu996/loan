<?php

namespace app\mobile\controller;

use think\Request;
use think\Session;

class Common extends \think\Controller
{

    public function _initialize()
    {
        $site = config("site");
        if ($site && $site['closed']) {
            die("网站已关闭");
        }
        $request = Request::instance();
        $controller_name = $request->Controller();
        $allow_controller = ['index', 'callback'];
        if (!in_array(strtolower($controller_name), $allow_controller) && !$this->isLogin()) {
            //$this->error("您还没有登录,请先登录!",'Index/login');
            $this->redirect('Index/login');
        }
        /*全站公共|逾期订单累积逾期金额*/
        $this->ifOverdue();
    }

    //判断用户是否已登录,如已登录则返回用户信息
    protected function isLogin()
    {
        $arr = Session::get('CVPHP_HOME');
        if (!$arr)
            return false;
        else
            return $arr;
    }

    //设置用户登录状态
    protected function setLogin($arr = [])
    {
        if ($arr)
            Session::set('CVPHP_HOME', $arr);
        else
            Session::delete('CVPHP_HOME');
    }

    //生成加密密码
    protected function makePass($str = '')
    {
        if (!$str)
            return '';
        $pass = sha1(md5($str));
        return $pass;
    }

    //逾期费用累加
    private function ifOverdue()
    {
        $loan_model = model("Loan");
        $user_model = model("User");
        $loan_config = config('Loan');
        $overduerate = empty($loan_config['overdue']) ? 0 : $loan_config['overdue'];
        $servicecharge = $loan_config['servicecharge'];
        $list = $loan_model->getListAdminOverdue(true);
        if (!$list) return false;
        foreach ($list as $v) {
            $uinfo = $user_model->getInfo(0, $v['uid']);
            $overdueday = ceil((time() - strtotime($v['lasttime'])) / (60 * 60 * 24));
            $borrowmoney = $v['borrowmoney'];
            $overdue = round(($overdueday * $borrowmoney * $overduerate) / 100, 2);  // 逾期费用
            $overduesettime = time() + 3600 * 24 * 30;
//            $oldallmoney = makeMoney(($borrowmoney*$v['rate']) / 100 * $v['borrowtime'] + $servicecharge) + $borrowmoney;
            $oldallmoney = makeMoney(($borrowmoney * $v['rate']) / 100 * $v['borrowtime'] + (($borrowmoney * $servicecharge) / 100)) + $borrowmoney;   // 历史总金额
            $allmoney = $oldallmoney + $overdue;
            $their = ($uinfo['their'] - $v['allmoney']) + $allmoney;
            if ($their < 0) $their = 0;
            $r = $loan_model->updateInfo($v['id'], ['overdue' => $overdue, 'allmoney' => $allmoney, 'overdueday' => $overdueday, 'overduesettime' => $overduesettime]);
            $user_model->updateInfo($v['uid'], ['their' => $their]);
        }
        return;
    }

}
