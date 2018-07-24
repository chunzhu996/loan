<?php
namespace app\admin\controller;
/*
    借款管理控制器
 */
class Loan extends Common
{

    //借款列表|总览
    public function index()
    {
        $loan_model = model("Loan");
        $seach = empty($_GET['seach'])?['status'=>'-1','username'=>'','id'=>'']:$_GET['seach'];
        $where = [];
        if($seach)
        {
            if($seach['id']) $where['id'] = $seach['id'];
            if($seach['username'])
            {
                $user = model("User")->getInfo($seach['username']);
                if($user) $where['uid'] = $user['id'];
            }
            if($seach['status'] != -1) $where['status'] = $seach['status'];
            $this->assign('seach',$seach);
        }
        $data = $loan_model->getListAdmin($where);

        for ($i=0;$i<count($data);$i++)
        {

            $userModel = model('Info');
            $userOne = $userModel->where(['uid'=>$data[$i]['uid']])->find();
            if($userOne)
            {
                $data[$i]['username'] = $userOne['name'];
            }

        }
//        var_dump($data[0]['username']);
//        return;
        $page = $data->render();
        $this->assign('data',$data);
        $this->assign('page',$page);
        return $this->fetch();
    }

//实际到账金额修改
	public function gaimoney()
    {
      $data = input('post.');
      //扣除手续费
      $cased =  db('loan')->where('id',$data['id'])->find();
	$site = config('loan');
	$moall = $site['rate']/100;
	$momoall = $data['sjje'] * $moall;
	$dzmoney = $data['sjje'] - $momoall;
   
     $ok = db('loan')->where('id',$data['id'])->update(['borrowmoney'=>$data['sjje'],'realmoney'=>$dzmoney,'allmoney'=>$data['sjje']]);
      if($ok){
       return ['msg'=>'修改成功','code'=>1]; 
      }else{
       return ['msg'=>'修改失败','code'=>0]; 
      }
    }

    //订单删除操作
    public function delOrder($id)
    {
        $id = intval($id);
        if(!$id) $this->error('订单参数错误');
        if(!model("Loan")->delOrder($id)) $this->error('订单操作失败');
        $this->success('success');
    }

    //订单审核驳回
    public function rejectOrder()
    {
        $id = input("id",0,'intval');
        $errorstr = input('error','');
        if(!$id) $this->error("订单参数错误");
        if(!model("Loan")->rejectOrder($id,$errorstr)) $this->error('订单操作失败');
        $this->success('success');
    }

    //订单审核通过
    public function agreeLoan($id)
    {
        $id = intval($id);
        $ifdb= db('loan')->where('id',$id)->find();
      	if($ifdb['status'] == 0)
      	{
         
            $msg_content = '您的借款订单初审通过,等待放款';
            $users = db('user')->where('id',$ifdb['uid'])->find();
            db('msg')->insert(['uid'=>$ifdb['uid'],'title'=>'借款订单通知','content'=>$msg_content,'add_time'=>time(),'status'=>0]);
            db('loan')->where('id',$id)->update(['status'=>4]);
               $this->success('success');
        }

        if($ifdb['status'] == 4)
        {
            if(!$id) $this->error('订单参数错误');
            if(!model("Loan")->agreeLoan($id)) $this->error('订单操作失败');
            $this->success('success');
        }
    }

    //订单手动还款
    public function setRepayment($id)
    {
        $id = intval($id);
        if(!$id) $this->error('订单参数错误');
        if(!model("Loan")->setRepayment($id)) $this->error('订单操作失败');
        $this->success('success');
    }
	//xiugai dingdan
    public function addmoney($id)
    { 
        $id = intval($id);
		
		$data['allmoney'] = htmlspecialchars($_POST['money']);
		model("Loan")->updateInfo($id,$data);
		$user = model("Loan")->find($id);
		$ids = $user['uid'];
		$dataa['their'] = htmlspecialchars($_POST['money']);
		model("User")->updateInfo($ids,$dataa);
		$this->success('借款金额修改成功');
    }
    //查看借款资料
    public function viewauth($id)
    {
        $id = intval($id);
        if(!$id) $this->error('订单参数错误');
        $auth = model("Loan")->getLoanAuth($id);
        if(!$auth) $this->error('订单信息获取失败');
        $this->assign('data',$auth);
        return $this->fetch();
    }


    //借款列表|逾期
    public function overdue()
    {
        $loan_model = model("Loan");
        $seach = empty($_GET['seach'])?['username'=>'','id'=>'']:$_GET['seach'];
        $where = [];
        if($seach)
        {
            if($seach['id']) $where['id'] = $seach['id'];
            if($seach['username'])
            {
                $user = model("User")->getInfo($seach['username']);
                if($user) $where['uid'] = $user['id'];
            }
            $this->assign('seach',$seach);
        }
        $data = $loan_model->getListAdminOverdue(false,$where);
        $page = $data->render();
        $this->assign('data',$data);
        $this->assign('page',$page);
        return $this->fetch();
    }


    public function changeState()
    {
        $state = intval(input('state'));
//        echo $state;
//        return;
        $id = input('order_id');
        if($state == 1 || $state == 2 || $state == 7 || $state == 8 || $state == 9 || $state == 10  || $state == 11 || $state == 12 || $state == 13 || $state == 14)
        {

            db('loan')->where('id',$id)->update(['status'=>$state]);
            return ['msg' => '修改成功','status' => 200];
        }
        else
        {
            return ['msg' => '未知错误,请稍后再试','status' => 0];
        }

    }


}
