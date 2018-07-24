<?php
namespace app\index\controller;
use Think\Controller;
use think\Request;
class Common extends \think\Controller
{
    //前台控制器初始化
	public function _initialize()
	{
        $site = config("site");
        if($site && $site['closed'])
        {
            die("网站已关闭");
        }
		//检测是否为手机访问
		$noMobile = input('get.noMobile');
		if(Request::instance()->isMobile() && !$noMobile)
			$this->redirect('mobile/index/index');

	}


}
