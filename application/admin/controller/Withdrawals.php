<?php
namespace app\admin\controller;
/*
    后台提现记录管理控制器
 */
class Withdrawals extends Common
{

    //充值订单列表
    public function index()
    {
        $Withdrawals_model = model("Withdrawals");
        $seach = empty($_GET['seach'])?['status'=>'-1','username'=>'','id'=>'','ordernum'=>'']:$_GET['seach'];
        $where = [];
        if($seach)
        {
            if($seach['status'] != -1) $where['status'] = $seach['status'];
            if($seach['id']) $where['id'] = $seach['id'];
            if($seach['ordernum']) $where['ordernum'] = $seach['ordernum'];
            if($seach['username'])
            {
                $user = model("User")->getInfo($seach['username']);
                if($user) $where['uid'] = $user['id'];
            }
            $this->assign('seach',$seach);
        }
        $data = $Withdrawals_model->getListAdmin($where);
        $page = $data->render();
        $this->assign('data',$data);
        $this->assign('page',$page);
        return $this->fetch();
    }

    //订单驳回
    public function rejectOrder()
    {
        $oid = input('oid');
        $error = input('error');
        if(!$oid) $this->error('订单参数错误');
        $r = model("Withdrawals")->rejectOrder($oid,$error);
        if(!$r) $this->error('操作失败');
//		db('loan')->where(['uid'=>$lists['uid'],'status'=>5])->update(['status'=>3]);
		db('loan')->where(['uid'=>$oid,'status'=>5])->update(['status'=>3]);
        $this->success('success');
    }

    //订单通过审核
    public function agreeOrder($oid=0)
    {
        if(!$oid) $this->error('订单参数错误');
        $r = model("Withdrawals")->agreeOrder($oid);
        if(!$r) $this->error('操作失败');
		$lists = db('Withdrawals')->where('ordernum',$oid)->find();
		$ok = db('loan')->where(['uid'=>$lists['uid'],'status'=>5])->find();
		db('loan')->where('id',$ok['id'])->update(['status'=>3]);
        $this->success('success');
    }

    //删除订单
    public function delOrder($id=0)
    {
        $id = intval($id);
        if(!$id) $this->error('参数错误');
        $r = model("Withdrawals")->delOrder($id);
        if(!$r) $this->error('操作失败');
        $this->success('success');
    }


}
