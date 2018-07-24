<?php
namespace app\common\model;
use think\Model;
class Withdrawals extends Model
{

    //模型关联
    public function user()
    {
        return $this->belongsTo('User','uid');
    }


    //创建提现订单
    public function addOrder($uid,$money,$oid)
    {
        $arr = [
            'uid'       =>      $uid,
            'money'     =>      $money,
            'ordernum'  =>      $oid,
            'add_time'  =>      time(),
            'status'    =>      0
        ];
        $this->data = $arr;
        $r = $this->isUpdate(false)->save();
        $msg_content = '您的提现信息已提交申请，请耐心等待系统审核.';
        model("Msg")->newMsg($uid,'提现订单通知',$msg_content);
        $user_info = $this->user;
        $tplid = config('api')['withdrawals'];
//        model("Smscode")->sendSomeSms($user_info['mobile'],$tplid,$oid);
        model("Smscode")->sendSomeSms($user_info['mobile'],$tplid,null);
        return $r;
    }

    //获取订单列表
    public function getListAdmin($where = 0)
    {
        $onePageNum = 25;
        $r =$this
            ->order('add_time','desc');
        if($where) $r = $r->where($where);
        $r = $r->paginate($onePageNum);
        return $r;
    }


    //删除订单
    public function delOrder($id)
    {
        $r = $this->where('id',$id)->delete();
        return $r;
    }

    //订单驳回
    public function rejectOrder($oid,$error)
    {
        $order = $this->where('ordernum',$oid)->find();
        if(!$order) return false;
        $order=$order->toArray();
        if($order['status'] != 0) return false;
        //标记订单
        $r = $this->isUpdate(true)->save(['status'=>2,'error'=>$error],['ordernum'=>$oid]);
        if(!$r) return false;
        //返还金额
        $r = model("User")->changeMoney($order['uid'],1,$order['money']);
        if(!$r) return false;
        $msg_content = '您的提现信息已审核，审核结果：失败.失败原因:'.$error.'。';
        model("Msg")->newMsg($order['uid'],'提现订单通知',$msg_content);
        $this->uid = $order['uid'];
        $user_info = $this->user;
        $tplid = config('api')['withdrawalsreject'];
//        model("Smscode")->sendSomeSms($user_info['mobile'],$tplid,$oid);
        model("Smscode")->sendSomeSms($user_info['mobile'],$tplid, null);
        return $r;
    }

    //订单审核完成
    public function agreeOrder($oid)
    {
        $order = $this->where('ordernum',$oid)->find();
        if(!$order) return false;
        $order=$order->toArray();
        if($order['status'] != 0) return false;
        //标记订单
        $r = $this->isUpdate(true)->save(['status'=>1],['ordernum'=>$oid]);
        if(!$r) return false;
        $msg_content = '您的提现信息已审核通过，资金即将汇入您的绑定银行卡。';
        model("Msg")->newMsg($order['uid'],'提现订单通知',$msg_content);
        $this->uid = $order['uid'];
        $user_info = $this->user;
        $tplid = config('api')['withdrawalsagree'];
//        model("Smscode")->sendSomeSms($user_info['mobile'],$tplid,$oid);
        model("Smscode")->sendSomeSms($user_info['mobile'],$tplid,null);
        return $r;
    }

}
