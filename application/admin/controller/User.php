<?php
namespace app\admin\controller;
/*
    后台用户管理控制器
 */
class User extends Common
{

    //用户列表
    public function index()
    {
        $user_model = model("User");
        $seach = empty($_GET['seach'])?['status'=>'-1','username'=>'','id'=>'']:$_GET['seach'];
        $where = [];
        if($seach)
        {
            if($seach['id']) $where['id'] = $seach['id'];
            if($seach['username']) $where['mobile'] = ['like',"%{$seach['username']}%"];
            $this->assign('seach',$seach);
        }
        $data = $user_model->getListAdmin($where);

        for ($i=0;$i<count($data);$i++)
        {

            $userModel = model('Info');
            $userOne = $userModel->where(['uid'=>$data[$i]['id']])->find();
            if($userOne)
            {
                $data[$i]['username'] = $userOne['name'];
                $test = json_decode($userOne['bankAuth']);
                if($test)
                {
                    $data[$i]['bankcard'] = json_decode($userOne['bankAuth'])->card;
                }
                else
                {
                    $data[$i]['bankcard'] = '';
                }

            }

        }
       
        $page = $data->render();
        $this->assign('data',$data);
        //var_dump($data);exit();
        $this->assign('page',$page);
        return $this->fetch();
    }
	//修改用户银行卡
	public function gaicard()
	{
	$data = input('post.');
	$info= db('info')->where('uid',$data['id'])->find();
	if(!$info['bankAuth']){
		$this->error('无数据');	
	}
	//print_r(json_decode($info['bankAuth'],true));
	//exit;
	$infodata = json_decode($info['bankAuth'],true);
	$newdata = ['card'=>$data['card'],'phone'=>$infodata['phone']];
	$ok = db('info')->where('uid',$data['id'])->update(['bankAuth'=>json_encode($newdata)]);
		if($ok){
		$this->success('修改成功');	
			
		}else{
		$this->error('修改失败');	
		}
	}
    //查看用户资料
    public function viewauth($id=0)
    {
        $id = intval($id);
        if(!$id) $this->error('参数错误');
        $info = model("Info")->getInfo($id);
        if(!$info) $this->error('信息获取失败');
        $this->assign('data',$info);
        return $this->fetch();
    }

    //删除用户
    public function deluser($id=0)
    {
        $id = intval($id);
        if(!$id) $this->error('参数错误');
        $r = model("User")->delUser($id);
        if(!$r) $this->error('操作失败');
        $this->success('success');
    }

    //用户后台手动充值
    public function userPay()
    {
        $id = intval(input('post.id'));
        $money = floatval(input('post.money'));
        if(!$id) $this->error('参数错误');
        if(!$money) $this->error('充值金额必须大于1元');
        $r = model("User")->changeMoney($id,1,$money,'m');
        if(!$r) $this->error('操作失败');
        $this->success('success');
    }

    //用户后台手动扣款
    public function userPayOut()
    {
        $id = intval(input('post.id'));
        $money = floatval(input('post.money'));
        if(!$id) $this->error('参数错误');
        if(!$money) $this->error('充值金额必须大于1元');
        $r = model("User")->changeMoney($id,0,$money,'m');
        if(!$r) $this->error('操作失败');
        $this->success('success');
    }


}
