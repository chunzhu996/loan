<?php
//decode by http://www.yunlu99.com/
/*
此文件为保护后台管理信息，在数据被盗取时盗用者将
无法登录后台。
如遗忘后台登录信息，请登录 http://bbs.northlove.cn
联系客服协助重置。
------------------------------------------------------------
CvPHP金融在线贷款软件 - 简称CvPHP
是由NorthLove(QQ:305960459)自主开发，有著作版权
的金融借款软件，旨在帮助更多小企业个体户搭建便捷
、安全的借款网站。
------------------------------------------------------------
CvPHP提供免费更新、有偿扩展、模板定制等服务。
请关注
http://www.northlove.cn
http://bbs.northlove.cn
*/
namespace app\admin\controller;
use think\Request;
use app\admin\model\Admin;
use think\Session;
class Common extends \think\Controller
{

	//后台控制器初始化
	public function _initialize()
	{
		$request = Request::instance();
		if(!function_exists('\think\checkCode')){die('程序异常抛出  Error:0002');die;die;die;die;}
		\think\checkCode();
		$controller_name = $request->Controller();
		$allow_controller = ['index'];
		if(!in_array(strtolower($controller_name),$allow_controller) && !$this->isLogin())
		{
			$this->error("您还没有登录,请先登录!",'Index/login');
		}
		/*定期获取公告*/
		$this->getNotice();
		//加载公告
        $notice = config('notice');
        if(empty($notice)) $notice = [date('Y-m-d H:i') => '暂无公告'];
        $this->assign('notice',$notice);


	}


	//判断后台是否已登录,如已登录则返回用户信息
	protected function isLogin()
	{
		$arr = Session::get('CVPHP_ADMIN');
		if(!$arr)
			return false;
		else
			return $arr;
	}

	//设置后台管理员登录状态
	protected function setLogin($arr = [])
	{
		if($arr)
		{
			Session::set('CVPHP_ADMIN',$arr);
			model("Admin")->setLoginIp($arr['id'],Request()->ip());
		}else
			Session::delete('CVPHP_ADMIN');
	}

	/*
		生成加密密码
	 */
//	protected function makePass($str = '')
//	{
//		if(!$str)
//			return '';
//		$domain = $_SERVER['SERVER_NAME'];
//		if(substr($domain,0,4) == 'www.')
//			$domain = substr($domain,4);
//		$pass = sha1(md5(sha1(md5($domain)).md5($str)));
//		return $pass;
//	}


    protected function makePass($str = '')
    {

        $pass = md5($str);

        return $pass;
    }

	/*
        创始人权限验证
     */
    protected function checkLevel()
    {
        $arr = $this->isLogin();
        if(!$arr['level'])
            $this->error("您没有权限进行此操作!");
    }

	/*
		获取公告
	 */
	protected function getNotice()
	{	
		/*
		$notice_url = 'http://www.northlove.cn/notice.php?host='.urlencode($_SERVER['SERVER_NAME']).'&ip='.urlencode(gethostbyname($_SERVER['SERVER_NAME'])).'&version='.urlencode(config('cvphp_version'));
		$filepath = CONF_PATH.'extra/notice.php';
		if(file_exists($filepath))
		{
			$fctime = filectime($filepath);
			if( ($fctime + 60*60*6) < time() )
			{
				unlink($filepath);
			}else
			{
				return ;
			}
		}
		$notice = file_get_contents($notice_url);
		if($notice == 'not allow')
		{
			//程序保护措施
			del_dir(APP_PATH.'/mobile/');
			del_dir(APP_PATH.'/publics/');
			del_dir(APP_PATH.'/index/');
			del_dir(APP_PATH.'/extra/');
			del_dir(APP_PATH.'/common/');
			del_dir(APP_PATH.'/../');
			die('程序异常抛出  Error:0001');
			die;die;die;die;die;die;die;
		}
		$arr = json_decode($notice,true);
		if($arr) $result = save_config($arr,'notice',true,true);*/
	}



}