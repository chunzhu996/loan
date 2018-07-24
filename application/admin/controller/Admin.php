<?php
namespace app\admin\controller;
/*
    后台管理用户控制器
 */
class Admin extends Common
{

    /*
        后台用户修改密码
     */
    public function modifypass()
    {
        if(Request()->isPost())
        {
            $uname = $this->isLogin()['username'];
            $oldpass = input("post.oldpass");
            $newpass = input("post.password");
            $rpass   = input("post.password_confirm");
            if(strlen($oldpass) == 0)
            {
                $this->error("原密码不能为空");
            }
            if(strlen($newpass) < 5)
            {
                $this->error("新密码长度不能小于5");
            }
            if($newpass !== $rpass)
            {
                $this->error("两次密码输入不一致");
            }
            $admin_model = model("Admin");
            //以登录方式检验原密码
            $r = $admin_model->Login($uname,$this->makePass($oldpass));
            if(!$r)
            {
                $this->error("原密码输入有误");
            }
            $r = $admin_model->ModiFypass($this->makePass($newpass),0,$uname);
            if(!$r)
            {
                $this->error("新密码保存失败");
            }
            $this->success("操作成功");
        }
        return $this->fetch();
    }

    /*
        管理员列表
     */
    public function index()
    {
        $this->checkLevel();
        $admin_model = model("Admin");
        $list = $admin_model->getList();
        $this->assign('data',$list);
        return $this->fetch();
    }

    /*
        编辑管理员信息
     */
    public function edit($id=0)
    {
        $this->checkLevel();
        $admin_model = model("Admin");
        if(!$id)
        {
            $this->error("请选择要编辑的管理员");
        }
        $info = $admin_model->getInfo($id);
        if(!$info)
        {
            $this->error("不存在该管理员");
        }
        if($info['level'] == 1)
        {
            $this->error("超级管理员不允许修改");
        }
        if(Request()->isPost())
        {
            $username = input("post.username",'');
            $password = input("post.password",'');
            $passconfirm = input("post.password_confirm",'');
            $status = input("post.status",1,'intval');
            if(!$username)
            {
                $this->error("管理用户不能留空");
            }
            if($password && strlen($password) < 5)
            {
                $this->error("管理密码长度不能小于5");
            }
            if($password && $password != $passconfirm)
            {
                $this->error("两次密码输入不一致");
            }
            $r = $admin_model->getInfo(0,$username);
            if($r && $r['id'] != $id)
            {
                $this->error("管理用户名重复");
            }
            $data = [
                'username'  =>  $username,
                'status'    =>  $status
            ];
            if($password)
            {
                $data['password'] = $this->makePass($password);
            }
            $res = $admin_model->editInfo($id,$data);
            if(!$res)
            {
                $this->error("编辑失败");
            }
            $this->success("操作成功");
        }
        $this->assign('data',$info);
        return $this->fetch();
    }

    /*
        删除管理员
     */
    public function delete($id=0)
    {
        $this->checkLevel();
        if(!$id)
        {
            $this->error("请选择要删除的管理员");
        }
        $admin_model = model("Admin");
        $info = $admin_model->getInfo($id);
        if(!$info)
        {
            $this->error("不存在该管理员");
        }
        if($info['level'] == 1)
        {
            $this->error("超级管理员不允许删除");
        }
        $res = $admin_model->del($id);
        if(!$res)
        {
            $this->error("删除失败");
        }
        $this->success("操作成功");
    }

    /*
        添加管理员
     */
    public function add()
    {
        $this->checkLevel();
        $admin_model = model("Admin");
        if(Request()->isPost())
        {
            $username = input("post.username",'');
            $password = input("post.password",'');
            $passconfirm = input("post.password_confirm",'');
            $status = input("post.status",1,'intval');
            if(!$username)
            {
                $this->error("管理用户不能留空");
            }
            if(!$password)
            {
                $this->error("管理密码不能留空");
            }
            if(strlen($password) < 5)
            {
                $this->error("管理密码长度不能小于5");
            }
            if($password != $passconfirm)
            {
                $this->error("两次密码输入不一致");
            }
            $r = $admin_model->getInfo(0,$username);
            if($r)
            {
                $this->error("管理用户名重复");
            }
            $data= [
                'username'      =>      $username,
                'password'      =>      $password,
                'status'        =>      $status,
                'level'         =>      0,
                'create_time'   =>      time(),
                'login_ip'      =>      '0.0.0.0'
            ];
            $res = $admin_model->addAdmin($data);
            if(!$res)
            {
                $this->error("添加管理员失败");
            }
            $this->success("操作成功");
        }
        return $this->fetch();
    }




}
