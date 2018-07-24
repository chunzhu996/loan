<?php
namespace app\common\model;
use think\Model;
class User extends Model
{

    //关联用户的借款模型
    public function loan()
    {
        return $this->hasMany('Loan','uid');
    }

    //关联用户的信息模型
    public function info()
    {
        return $this->hasOne('Info','uid');
    }

    //添加用户
    public function addUser($mobile,$passwd,$money=0)
    {
        $this->data = [
            'mobile'        =>      $mobile,
            'password'      =>      $passwd,
            'money'         =>      $money,
            'create_time'   =>      time()
        ];
        $r = $this->isUpdate(false)->save();
        if(!$r)
            return false;
        return true;
    }

    //判断用户是否存在
    public function ifExist($mobile)
    {
        $r = $this->where(['mobile'=>$mobile])->count();
        if($r)
            return true;
        return false;
    }

    //修改用户密码
    public function changePass($mobile,$pass)
    {
        $r = $this->save(['password'=>$pass],['mobile'=>$mobile]);
        return $r;
    }

    //用户登录验证
    public function Login($mobile,$passwd)
    {
        $r = $this->where(['mobile'=>$mobile,'password'=>$passwd])->find();
        if(!$r) return false;
        $user = $r->hidden(['money','their','password'])->toArray();
        $info_model = model("Info");
        $r = $info_model->where(['uid'=>$user['id']])->find();
        if(!$r) $info_model->regInfo($user['id']);
        return $user;
    }

    //获取用户账户信息
    public function getInfo($mobile=0,$uid=0)
    {
        if($mobile) $r = $this->where(['mobile'=>$mobile])->find();
        else $r = $this->where(['id'=>$uid])->find();
        if(!$r) return false;
        return $r->hidden(['password','create_time'])->toArray();
    }

    // 账户余额扣除审核费用
    public function buckleMoney($uid, $money)
    {
        $u = $this->where('id',$uid)->find();
        if(!$u) return false;
        $u = $u->visible(['money'])->toArray();
        if(!$u) return false;
        $newMoney = $u['money'] - $money;
        $arr = ['money'=>$newMoney];
        $r = $this->where('id',$uid)->update($arr);
        return $r;
    }


    //更新用户钱包余额
    public function changeMoney($uid,$act,$money,$type='m')
    {
        $u = $this->where('id',$uid)->find();
        if(!$u) return false;
        $u = $u->visible(['money','their'])->toArray();
        if(!$u) return false;
        $oldMoney = empty($u['money']) ? 0:$u['money'];
        $oldTheir = empty($u['their']) ? 0:$u['their'];
        if($act)
        {
            $newMoney = $oldMoney + $money;
            $newTheir = $oldTheir + $money;
        }else
        {
            $newMoney = $oldMoney - $money;
            $newTheir = $oldTheir - $money;
        }
        if($type=='m')
        {
            $arr = ['money'=>$newMoney];
        }else
        {
            $arr = ['their'=>$newTheir];
        }
        $r = $this->where('id',$uid)->update($arr);
        return $r;
    }

    //判断钱包余额是否大于等于预设金额
    public function moneyAmple($uid,$money)
    {
        $arr = $this->where('id',$uid)->find();
        if(!$arr) return false;
        $arr = $arr->toArray();
        if($arr['money'] >= $money) return true;
        return false;
    }


    //更新信息
    public function updateInfo($id,$data)
    {
        $r = $this->allowField(true)->where('id',$id)->update($data);
        return $r;
    }

    //获取用户列表
    public function getListAdmin($where=0)
    {
        $onePageNum = 25;
        $r =$this
            ->order('create_time','desc');
        if($where) $r = $r->where($where);
        $r = $r->withCount('loan')->paginate($onePageNum);
        return $r;
    }

    //删除用户
    public function delUser($id)
    {
        $r = $this->where('id',$id)->delete();
        return $r;
    }

}
