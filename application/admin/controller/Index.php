<?php
namespace app\admin\controller;
/*
    后台首页控制器
 */
class Index extends Common
{

    /*
        后台首页
     */
    public function index()
    {
        if(!$this->isLogin())
            $this->redirect('Index/login');
        $info = array(
            '操作系统'=>PHP_OS,
            '运行环境'=>$_SERVER["SERVER_SOFTWARE"],
            '主机名'=>$_SERVER['SERVER_NAME'],
            'WEB服务端口'=>$_SERVER['SERVER_PORT'],
            '网站文档目录'=>$_SERVER["DOCUMENT_ROOT"],
            '浏览器信息'=>substr($_SERVER['HTTP_USER_AGENT'], 0, 40),
            'ThinkPHP版本'=>THINK_VERSION,
            '上传附件限制'=>ini_get('upload_max_filesize'),
            '执行时间限制'=>ini_get('max_execution_time').'秒',
            '服务器时间'=>date("Y年n月j日 H:i:s"),
            '北京时间'=>gmdate("Y年n月j日 H:i:s",time()+8*3600),
            '服务器域名/IP'=>$_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
            '客户端IP地址'=>$_SERVER['REMOTE_ADDR'],
            //'磁盘剩余空间'=>round((disk_free_space(".")/(1024*1024*1024)),2) . ' / ' . round(disk_total_space(".")/(1024*1024*1024),2).' (GB)',
        );
        $this->assign('info',$info);
        return $this->fetch();
    }


    /*
        后台登录
     */
    public function login()
    {	

        if($this->isLogin())
			
            $this->redirect('Index/index');
        if (request()->isPost())
        {
            $admin_model = model('Admin');
            $uname = input("post.username",'');
            $upass = input("post.password",'');

            if(strlen($uname) < 5)
                $this->error("管理用户名长度不能小于5!");
            if(strlen($upass) < 5)
                $this->error("管理密码长度不能小于5!");
            //$upass = $this->makePass($upass);
            $arr = $admin_model->Login($uname,$upass);
			//echo $upass;
            if(!$arr)
                $this->error("管理用户名或密码有误!");
            if(!$arr['status'])
                $this->error("该账户已被管理员禁止登录!");
            $this->setLogin($arr);
            $this->success("登录成功");
        }
        return $this->fetch();
    }

    /*
        后台用户注销
     */
    public function logout()
    {
        $this->setLogin([]);
        $this->success("您已成功退出登录!",'Index/index');
    }

    /*
        清理缓存
     */
    public function clear_cache()
    {
        //项目模板缓存目录
        //应用缓存目录
        $CACHE_PATH = RUNTIME_PATH."cache/";
        $TEMP_PATH  = RUNTIME_PATH."temp/";
        del_dir($CACHE_PATH);
        del_dir($TEMP_PATH);
        $this->success("缓存清理成功!",'Index/index');
    }



}
