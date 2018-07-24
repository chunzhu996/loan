<?php
/*
    系统设置控制器
 */
namespace app\admin\controller;
use app\common\Erweima;
use think\Request;
use think\response\Redirect;

class Setting extends Common
{

    public function index()
    {
        if(Request()->isPost())
        {
            $action = input('post.action');
            if(!$action)
            {
                $this->error("操作参数错误");
            }
            unset($_POST['action']);


            if(!save_config($_POST,$action,true,true))
            {
                $this->error("保存配置失败");
            }
            $this->success("操作成功");
        }
        $site = config('site');
        $api  = config('api');
        $this->assign('site',$site);
        $this->assign('api',$api);



        //找到图片
        $ErweimaModel = model('Erweima');
        $list = $ErweimaModel->select();
        if($list)
        {
            for($i=0;$i<count($list);$i++)
            {
                $list[$i] = $list[$i]->toArray();
            }
        }
        else
        {
            $list = [];
        }

        $this->assign('list',$list);

        return $this->fetch();
    }




    public function uploadEwm()
    {
        if(Request()->isPost())
        {

            $erweima = Request()->file('erweima');
            $mark = input('mark');
            $fn1 = $this->saveAuthPhoto($erweima);
            //文件上传失败
            if( !$fn1)
            {
                $this->error('文件上传出错,请稍后再试');
            }
            else
            {
                //文件上传成功

                //写入数据库
                $ErweimaModel = model('Erweima');
                $data =
                [
                  'url'=> $fn1,
                  'is_using' => 0,
                  'mark' =>   $mark?$mark:time(),//是否写有备注，没有的话写入时间
                  'create_time' => time(),
                ];
                $result = $ErweimaModel->save($data);
                if($result)
                {
                    return ['msg'=>'添加成功'];
                }
                else
                {
                    return ['msg'=>'添加失败'];
                }

            }
        }
    }


    // rewrite photo upload
    private function saveAuthPhoto($file)
    {
        // check file
        if(!$file)
            return false;
        // get file info
        $date = date('Ymd');
        // use md5 for to file folder, fixed at same time update errors.
        $info = $file->rule('md5')->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . $date );
        if($info)
        {
            return $date.DS.$info->getSaveName();
        }
        return $info->getError();
    }




    //处理上传图片
    private function savePhoto($file)
    {
        if(!$file) return false;
        $site = config('site');

        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' );
            if($info){
                // 成功上传后 获取上传信息

                // echo $info->getExtension();


                $this->photos1= $info->getSaveName(0);
                $this->photos2= $info->getSaveName(1);
                $this->photos3= $info->getSaveName(2);
                $this->photos4= $info->getSaveName(3);
                // 输出 42a79759f284b767dfcb2a0197904287.jpg
                // echo $info->getFilename();

            }else{
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }

    }



    //启用图片
    function startPhoto()
    {
        //需要启用的图片id
        $photoId = input('photo_id');

        //先关闭已启用的
        $ErweimaModel = model('Erweima');

        $alreadyStart = $ErweimaModel->where(['is_using'=>1])->find();
        if($alreadyStart)
        {
            $ErweimaModel->where(['is_using'=>1])->update(['is_using'=>0]);
        }

        //开启
        $which_one = $ErweimaModel->where(['id'=>$photoId])->find();
        if($which_one)
        {
           $result = $ErweimaModel->where(['id'=>$photoId])->update(['is_using'=>1]);
           if($result)
           {
               return ['msg'=>'已成功开启','state'=>200];
           }
           else
           {
               return ['msg'=>'开启失败','state'=>0];
           }
        }
        else
        {
            return ['msg'=>'图片id传输错误！！！','state'=>0];
        }


    }



}
