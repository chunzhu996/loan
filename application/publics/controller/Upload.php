<?php
/**
 * 公共文件上传控制器
 */
namespace app\publics\controller;
use think\Request;
use think\Session;
class Upload extends \think\Controller
{
    public function index()
    {
        $file = Request()->file('upfilename');
        if(!$file)
        {
            $this->error('请选择上传文件');
        }
        $site = config('site');
        $site['maxupload'] = intval($site['maxupload']);
        $maxfilesize = empty($site['maxupload'])?2:$site['maxupload'];
        $allowext = empty($site['fileallow'])?'':$site['fileallow'];
        $info = $file->validate(['size'=>($maxfilesize*1024*1024),'ext'=>$allowext])->rule('date')->move(APP_PATH . '../public/uploads',true,false);
        if($info){
            // 成功上传后 获取上传信息
            // 输出 jpg
            //echo $info->getExtension();
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
            //echo $info->getSaveName();
            // 输出 42a79759f284b767dfcb2a0197904287.jpg
            //echo $info->getFilename();
            $file_url = '/uploads/'.$info->getSaveName();
            $file_url = str_replace('\\','/',$file_url);
            $this->success($file_url);
        }else{
            // 上传失败获取错误信息
            $this->error($file->getError());
        }
    }



}
