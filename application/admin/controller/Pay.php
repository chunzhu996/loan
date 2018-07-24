<?php
namespace app\admin\controller;
/*
    后台充值记录管理控制器
 */
class Pay extends Common
{

    //充值订单列表
    public function index()
    {
        $pay_model = model("Pay");
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
        $data = $pay_model->getListAdmin($where);
        $page = $data->render();
        $this->assign('data',$data);
        $this->assign('page',$page);
        return $this->fetch();
    }

    //手动设置入账
    public function confirmPay($oid=0)
    {
        if(!$oid) $this->error('订单参数错误');
        $r = model("Pay")->undateOrder($oid);
        if(!$r) $this->error('操作失败');
        $this->success('success');
    }

    //删除订单
    public function delOrder($id=0)
    {
        $id = intval($id);
        if(!$id) $this->error('参数错误');
        $r = model("Pay")->delOrder($id);
        if(!$r) $this->error('操作失败');
        $this->success('success');
    }


}
