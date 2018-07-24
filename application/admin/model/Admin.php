<?php
/*

        后台管理员模型

 */
namespace app\admin\model;
use think\Model;
class Admin extends Model
{

    /*后台登录验证*/
    public function Login($uname,$upass)
    {
        $r = $this->where(['username'=>$uname,'password'=>md5($upass)])->find();
        if(!$r)
            return false;
        $r = $r->hidden(['password'])->toArray();
        return $r;
    }

    /*设置管理员登录IP*/
    public function setLoginIp($id,$ip)
    {
        $result = $this->where(['id'=>$id])->update(['login_ip' => $ip]);
        return $result;
    }

    /*后台管理密码修改*/
    public function ModiFypass($pass,$id=0,$name='')
    {
        $r = false;
        if($id)
        {
            $r = $this->save(['password'=>$pass],['id'=>$id]);
        }
        if($name)
        {
            $r = $this->save(['password'=>$pass],['username'=>$name]);
        }
        if($r)
            return true;
        return false;
    }

    /*获取管理员列表*/
    public function getList()
    {
        $r = $this->select();
        if(!$r)
            return false;
        return $r;
    }

    /*根据ID/用户名获取管理员信息*/
    public function getInfo($id=0,$username='')
    {
        $info = '';
        if($id)
        {
            $info = $this->find($id);
        }else
        {
            if($username)
            {
                $info = $this->where(['username'=>$username])->find();
            }
        }
        if(!$info)
            return false;
        return $info->toArray();
    }

    /*根据ID修改管理员信息*/
    public function editInfo($id,$data)
    {
        $data['id'] = $id;
        $result = $this->update($data);
        return $result;
    }

    /*根据ID删除管理员*/
    public function del($id)
    {
        $result = $this->where(['id'=>$id])->delete();
        return $result;
    }

    /*添加管理员*/
    public function addAdmin($data)
    {
        $this->data = $data;
        $result = $this->isUpdate(false)->save();
        return $result;
    }

}
