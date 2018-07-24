<?php
namespace app\common\model;
use think\Model;
class Pay extends Model
{

    //模型关联
    public function user()
    {
        return $this->belongsTo('User','uid');
    }

    //创建订单
    public function addOrder($uid,$money,$oid)
    {
        $data = [
            'uid'       =>      $uid,
            'money'     =>      $money,
            'ordernum'  =>      $oid,
            'add_time'  =>      time(),
            'status'    =>      0
        ];
        $this->data = $data;
        $r = $this->isUpdate(false)->save();
        return $r;
    }

    //获取调用支付信息
    public function getPayData($oid)
    {
        $order = $this->where('ordernum',$oid)->find();
        if(!$order) return false;
        $data = $order->toArray();
        if($data['status']) return false;
        //调用支付宝SDK组合支付参数
        $api = config('api');
        $siteurl = Request()->root(true);
        //include_once(APP_PATH."/../extend/Alipay_wap/alipay_submit.class.php");
        $parameter = array(
        	"service"       => "alipay.wap.create.direct.pay.by.user",
    		"partner"       => $api['alipaypid'],
    		"seller_id"     => $api['alipaypid'],
    		"payment_type"	=> "1",
    		"notify_url"	=> $siteurl.'/mobile/callback/alipay/',
    		"return_url"	=> $siteurl.'/mobile/user/coin/',
    		"_input_charset"=> trim(strtolower('utf-8')),
    		"out_trade_no"	=> $oid,
    		"subject"	    => '充值订单:'.$oid,
    		"total_fee"	    => $data['money'],
    		"show_url"	    => $siteurl.'/mobile/user/coin/',
    		"app_pay"	    => "Y",//启用此参数能唤起钱包APP支付宝
    		"body"	        => "",
    		//其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.2Z6TSk&treeId=60&articleId=103693&docType=1
            //如"参数名"	=> "参数值"   注：上一个参数末尾需要“,”逗号。
        );
        //建立请求
        $alipaySubmit = new \Alipay_wap\AlipaySubmit([
            'sign_type'         =>      strtoupper('MD5'),
            'key'               =>      $api['alipaykey'],
            'input_charset'     =>      trim(strtolower('utf-8')),
        ]);
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
        return $html_text;
    }

    //更新订单信息并为用户入账
    public function undateOrder($oid)
    {
        $order = $this->where('ordernum',$oid)->find();
        if(!$order) return false;
        $data = $order->toArray();	#1E90FF
        if($data['status']) return false;
        $money = $data['money'];
        $uid = $data['uid'];
        $user_model = model("User");
        $r = $user_model->changeMoney($uid,1,$money);
        if(!$r) return false;
        $r = $this->where('ordernum',$oid)->update(['status'=>1]);
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


}
