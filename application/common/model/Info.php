<?php
namespace app\common\model;
use think\Model;
class Info extends Model
{

    //用户注册同时添加信息表
    public function regInfo($uid)
    {
        $this->data = [
            'uid'       =>      $uid,
        ];
        $r = $this->isUpdate(false)->save();
    }

    //是否实名认证
    public function ifIdentity($uid)
    {
        $r = $this->where(['uid'=>$uid])->find();
        if(!$r) return false;
        $r = $r->visible(['name','idcard'])->toArray();
        if(!$r['name'] || !$r['idcard'])
            return false;
        return true;
    }


    //是否填写了个人资料
    public function ifPerson($uid)
    {
        $r = $this->where(['uid'=>$uid])->find()->visible(['personAuth'])->toArray();
        if(!$r || !$r['personAuth']) return false;
        return true;
    }

    //是否已经进行授权
    public function ifMobileTb($uid)
    {
        $r = $this->where(['uid'=>$uid])->find()->visible(['mobileAuth','taobaoAuth'])->toArray();
        if(!$r || !$r['mobileAuth'] || !$r['taobaoAuth']) return false;
        return true;
    }

    //是否上传了图片
    public function ifPhotoUp($uid)
    {
        $r = $this->where(['uid'=>$uid])->find()->visible(['photoAuth'])->toArray();
        if(!$r || !$r['photoAuth']) return false;
        return true;
    }

    //是否绑定银行卡
    public function ifAddBank($uid)
    {
        $r = $this->where(['uid'=>$uid])->find()->visible(['bankAuth'])->toArray();
        if(!$r || !$r['bankAuth']) return false;
        return true;
    }

    //判断身份证号码是否存在数据库
    public function hasIdcard($idcard)
    {
        $r = $this->where(['idcard' => $idcard])->find();
        if($r) return true;
        return false;
    }

    //更新信息
    public function updateInfo($uid,$data)
    {
        $r = $this->allowField(true)->where('uid',$uid)->update($data);
        return $r;
    }

    //获取用户资料
    public function getInfo($uid)
    {
        $r = $this->where('uid',$uid)->find();
        if(!$r) return false;
        return $r->toArray();;
    }

    //获取信息认证概括
    public function getAuth($uid)
    {
        $info = $this->getInfo($uid);
        if(!$info) return false;
        if($info)
        {
            unset($info['id']);
            unset($info['uid']);
        }
        foreach ($info as $key => $value)
        {
            if(empty($value))
                $info[$key] = 0;
            else
                $info[$key] = 1;
        }
        return $info;
    }



}
