<?php

namespace app\common\model;

use think\Model;

class Loan extends Model
{

    protected $type = [
        'lasttime' => 'timestamp',
        'add_time' => 'timestamp'
    ];

    //模型关联
    public function user()
    {
        return $this->belongsTo('User', 'uid');
    }


    //判断是否允许借款
    public function canBorrow($uid)
    {
        $r = $this->where(['status' => 0, 'uid' => $uid])->find();
        $s = $this->where(['status' => 3, 'uid' => $uid])->find();


        if (!$r and !$s) return true;
        return false;
    }

    // 判断是会否有余额支付服务费用
    public function canBalance($uid, $money)
    {
        $user_model = model("User");
        $nowMoney = $user_model->where(['id' => $uid])->value('money'); // 返回当前金额

        return (int)$nowMoney >= (int)$money ? true : false;
    }


    //添加借款订单
    public function addBorrow($uid, $data)
    {
        $info_model = model("Info");
        $info = $info_model->getInfo($uid);
        $identity = json_encode([
            'name' => $info['name'],
            'idcard' => $info['idcard']
        ]);
        $repaymenttype = $data['repaymenttype'];
        $realmoney = $data['realmoney'];
        $this->data = [
            'uid' => $uid,
            'borrowmoney' => $data['money'],
            'borrowtime' => $data['time'],
            'rate' => $data['more']['rate'],
            'ratemoney' => $data['more']['rateMoney'],
            'servicecharge' => $data['more']['servicecharge'],
            'time_n' => $data['more']['time_n'],
            'lasttime' => $data['more']['lastTime'],
            'repaymenttype' => $repaymenttype,
            'allmoney' => $data['more']['allMoney'],
            'realmoney' => $realmoney,
            'status' => 0,
            'errorstr' => '',
            'overdue' => 0,
            'overdueday' => 0,
            'overduesettime' => 0,
            'identity' => $identity,
            'person' => $info['personAuth'],
            'mobile' => $info['mobileAuth'],
            'taobao' => $info['taobaoAuth'],
            'photo' => $info['photoAuth'],
            'bank' => $info['bankAuth'],
            'add_time' => time()
        ];
        $r = $this->isUpdate(false)->save();
        if (!$r) return false;
        return $this->id;
    }

    //获取借款列表
    public function getList($uid = 0, $page = 1)
    {
        $onePageNum = 10;//每页显示数量
        $limit = ($page - 1) * $onePageNum . "," . $onePageNum;
        $field = ['id', 'uid', 'borrowmoney', 'borrowtime', 'rate', 'ratemoney', 'servicecharge', 'time_n', 'lasttime', 'allmoney', 'overdue', 'overdueday', 'status', 'add_time'];
        if (!$uid) {
            $r = $this->order('add_time', 'desc')->field($field)->limit($limit)->select();
        } else $r = $this->where('uid', $uid)->field($field)->limit($limit)->order('add_time', 'desc')->select();
        if (!$r) return [];
        for ($i = 0; $i < count($r); $i++) {
            $r[$i]['borrowmoney'] = makeMoney($r[$i]['borrowmoney']);
            $r[$i]['allmoney'] = makeMoney($r[$i]['allmoney']);
            $r[$i]['ratemoney'] = makeMoney($r[$i]['ratemoney']);
            $r[$i]['servicecharge'] = makeMoney($r[$i]['servicecharge']);
            $r[$i]['overdue'] = makeMoney($r[$i]['overdue']);

        }
        return $r;
    }

    //订单还款
    public function rePayment($oid)
    {
        $user_model = model("User");
        //查询订单信息
        $info = $this->find($oid);
        if (!$info) return false;
        $info = $info->toArray();
        $r = $user_model->moneyAmple($info['uid'], $info['allmoney']);
        if (!$r) return 2;
        //余额扣款
        $r = $user_model->changeMoney($info['uid'], 0, $info['allmoney']);
        if (!$r) return false;
        //欠款扣除
        $r = $user_model->changeMoney($info['uid'], 0, $info['allmoney'], 't');
        if (!$r) return false;
        //保存订单状态
        $r = $this->isUpdate(true)->save(['status' => 1], ['id' => $oid]);
        if (!$r) return false;
        $msg_content = '您的订单已还款成功.';
        model("Msg")->newMsg($info['uid'], '借款订单通知', $msg_content);
        $this->uid = $info['uid'];
        $user_info = $this->user;
        $tplid = config('api')['loanrepayment'];
//        model("Smscode")->sendSomeSms($user_info['mobile'],$tplid,$info['id']);
        model("Smscode")->sendSomeSms($user_info['mobile'], $tplid, null);
        return 1;
    }

    //后台管理列表获取
    //与前台不同的是分页
    public function getListAdmin($where = 0)
    {
        $onePageNum = 25;
        $r = $this
            ->order('add_time', 'desc');
        if ($where) $r = $r->where($where);
        $r = $r->paginate($onePageNum);
        for ($i = 0; $i < count($r); $i++) {
            $mobile_obj = $r[$i]->user;
            if ($mobile_obj) {
                $r[$i]['mobile'] = $mobile_obj->mobile;
            } else {
                $r[$i]['mobile'] = '';
            }
            $r[$i]['borrowmoney'] = makeMoney($r[$i]['borrowmoney']);
            $r[$i]['allmoney'] = makeMoney($r[$i]['allmoney']);
            $r[$i]['ratemoney'] = makeMoney($r[$i]['ratemoney']);
            $r[$i]['servicecharge'] = makeMoney($r[$i]['servicecharge']);
            $r[$i]['overdue'] = makeMoney($r[$i]['overdue']);
        }
        return $r;
    }

    //后台管理逾期列表获取
    public function getListAdminOverdue($ifnotsetoverdue = false, $where = 0)
    {
        $onePageNum = 25;
        $r = $this
            ->order('add_time', 'desc')
            ->where(['lasttime' => ['<', time()], 'status' => 3]);
        if ($ifnotsetoverdue) $r = $r->where(['overduesettime' => ['<', time()]]);
        if ($where) $r = $r->where($where);
        $r = $r->paginate($onePageNum);
        for ($i = 0; $i < count($r); $i++) {
            $mobile_obj = $r[$i]->user;
            if ($mobile_obj) {
                $r[$i]['mobile'] = $mobile_obj->mobile;
            } else {
                $r[$i]['mobile'] = '';
            }
            $r[$i]['borrowmoney'] = makeMoney($r[$i]['borrowmoney']);
            $r[$i]['allmoney'] = makeMoney($r[$i]['allmoney']);
            $r[$i]['ratemoney'] = makeMoney($r[$i]['ratemoney']);
            $r[$i]['servicecharge'] = makeMoney($r[$i]['servicecharge']);
            $r[$i]['overdue'] = makeMoney($r[$i]['overdue']);
        }
        return $r;
    }


    //订单删除
    public function delOrder($id)
    {
        $info = $this->find($id);
        if (!$info) return false;
        $arr = $info->toArray();
        if (!$arr) return false;
        if ($arr['status'] == 3) return false;//未还款状态不允许删除
        $r = $this->where('id', $id)->delete();
        return $r;
    }

    //订单审核驳回处理
    public function rejectOrder($id, $str)
    {
        $info = $this->find($id);
        if (!$info) return false;
        $arr = $info->toArray();
        if (!$arr) return false;
        if ($arr['status'] != 0) return false;
        $result = $this->isUpdate(true)->save(['status' => 2, 'errorstr' => $str], ['id' => $id]);
        $msg_content = '您的借款订单已审核,审核结果:失败.';
        if ($str) $msg_content .= '<br>失败原因:' . $str;
        model("Msg")->newMsg($arr['uid'], '借款订单通知', $msg_content);
        $this->uid = $info['uid'];
        $user_info = $this->user;
        $tplid = config('api')['loanreject'];
//        model("Smscode")->sendSomeSms($user_info['mobile'],$tplid,$info['id']);
        model("Smscode")->sendSomeSms($user_info['mobile'], $tplid, null);
        return $result;
    }

    // 订单扣除服务费用
    public function buckle($uid,$rid){
        $info = $this->find($rid);
        $user_model = model("User");
        // 账户余额扣除审核费用
        $buckle = $user_model->buckleMoney($uid, $info['servicecharge']);
        if(!$buckle)
            return false;
        return true;
    }


    //订单审核通过
    public function agreeLoan($id)
    {
        $info = $this->find($id);
        if (!$info) return false;
        $arr = $info->toArray();
        if (!$arr) return false;

        if ($arr['status'] != 4) return false;
        $result = $this->isUpdate(true)->save(['status' => 5], ['id' => $id]);//订单标记
        if (!$result) return false;
        $user_model = model("User");
        $result = $user_model->changeMoney($arr['uid'], 1, $arr['allmoney'], 't');//标记待还金额
        if (!$result) return false;

        $result = $user_model->changeMoney($arr['uid'], 1, $arr['realmoney']);//资金入账
        if (!$result) return false;
        $msg_content = '您的借款订单已审核成功,资金已入账.';
        model("Msg")->newMsg($arr['uid'], '借款订单通知', $msg_content);
        $this->uid = $info['uid'];
        $user_info = $this->user;
        $tplid = config('api')['loanagree'];
//        model("Smscode")->sendSomeSms($user_info['mobile'],$tplid,$info['id']);
        model("Smscode")->sendSomeSms($user_info['mobile'], $tplid, null);
        return true;
    }

    //订单手动还款
    public function setRepayment($id)
    {
        $info = $this->find($id);
        if (!$info) return false;
        $arr = $info->toArray();
        if (!$arr) return false;
        if ($arr['status'] != 3) return false;
        $result = $this->isUpdate(true)->save(['status' => 1], ['id' => $id]);//订单标记
        if (!$result) return false;
        $user_model = model("User");
        $result = $user_model->changeMoney($arr['uid'], 0, $arr['allmoney'], 't');//标记待还金额
        if (!$result) return false;
        $msg_content = '您的订单已还款成功.';
        model("Msg")->newMsg($arr['uid'], '借款订单通知', $msg_content);
        $this->uid = $info['uid'];
        $user_info = $this->user;
        $tplid = config('api')['loanrepayment'];
//        model("Smscode")->sendSomeSms($user_info['mobile'],$tplid,$info['id']);
        model("Smscode")->sendSomeSms($user_info['mobile'], $tplid, null);
        return true;
    }

    //获取订单认证信息
    public function getLoanAuth($id)
    {
        $info = $this->find($id);
        if (!$info) return false;
        $arr = $info->toArray();
        if (!$arr) return false;
        $data = [
            'identity' => $arr['identity'],
            'person' => $arr['person'],
            'mobile' => $arr['mobile'],
            'taobao' => $arr['taobao'],
            'photo' => $arr['photo'],
            'bank' => $arr['bank']
        ];
        return $data;
    }

    //更新信息
    public function updateInfo($id, $data)
    {
        $r = $this->allowField(true)->where('id', $id)->update($data);
        return $r;
    }

    // 短信提醒逾期数据
    public function getOverdueSms($ifnotsetoverdue = false, $where = 0)
    {
        $onePageNum = 10000;
        $r = $this
            ->order('add_time', 'desc')
            ->where(['lasttime' => ['<', time()], 'status' => 3]);
        if ($ifnotsetoverdue) $r = $r->where(['overduesettime' => ['<', time()]]);
        if ($where) $r = $r->where($where);
        $r = $r->paginate($onePageNum);
        for ($i = 0; $i < count($r); $i++) {
            $mobile_obj = $r[$i]->user;
            if ($mobile_obj) {
                $r[$i]['mobile'] = $mobile_obj->mobile;
            } else {
                $r[$i]['mobile'] = '';
            }
            $r[$i]['borrowmoney'] = makeMoney($r[$i]['borrowmoney']);
            $r[$i]['allmoney'] = makeMoney($r[$i]['allmoney']);
            $r[$i]['ratemoney'] = makeMoney($r[$i]['ratemoney']);
            $r[$i]['servicecharge'] = makeMoney($r[$i]['servicecharge']);
            $r[$i]['overdue'] = makeMoney($r[$i]['overdue']);
        }
        return $r;
    }

}
