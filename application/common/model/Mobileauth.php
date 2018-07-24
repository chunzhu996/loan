<?php
namespace app\common\model;
use think\Model;
class Mobileauth extends Model
{

    //查询用户有没有未完成认证记录
    public function hasNotAuth($uid)
    {
        $r = $this->where(['uid'=>$uid,'status'=>0,'add_time'=>['>', (time()-10*60) ] ])->count();
        if($r) return true;
        return false;
    }

    //获取认证H5页面地址
    public function getAuthUrl($uid,$info)
    {
        $key = config('api')['mkey'];
        $siteurl = Request()->root(true);
        //写入数据库记录
        $this->data = [
            'uid'   =>      $uid,
            'token' =>      '',
            'status'=>      0,
            'add_time'  =>  time()
        ];
        $r = $this->isUpdate(false)->save();
        if(!$r) return false;
        $arr = [
            'apiurl'        =>      'http://e.apix.cn/apixanalysis/mobile/yys/phone/carrier/page',
            'key'           =>      $key,
            'callback_url'  =>      $siteurl.'/mobile/callback/mobile/?authid='.$this->id.'&uid='.$uid,
            'success_url'   =>      $siteurl.'/mobile/user/index',
            //'failed_url'    =>      $siteurl.'/mobile/user/index',
            'name'          =>      $info['name'],
            'cert_no'       =>      $info['idcard'],
            'show_nav_bar'  =>      'true'
        ];
        $info = model("Info")->where('uid',$uid)->find();
        if(!$info) return false;
        $info = $info->toArray();
        $info=json_decode($info['personAuth'],true);
        if($info)
        {
            $arr['contact_list'] = $info['relationship1'].':'.$info['rs_realname1'].':'.$info['rs_phone1'].','.$info['relationship2'].':'.$info['rs_realname2'].':'.$info['rs_phone2'];
        }
        $result = curlGet($arr);
        if(!$result) return false;
        $result = json_decode($result,true);
        return $result;
    }

    //保存授权结果(非查询结果)
    public function setStatus($authid,$token,$status)
    {
        $data = [
            'token'     =>      $token,
            'status'    =>      $status
        ];
        $r = $this->where('id',$authid)->update($data);
        return $r;
    }

    //获取查询结果
    public function getAuthResult($uid,$token)
    {
        $key = config('api')['mkey'];
        $arr = [
            'apiurl'        =>      'http://e.apix.cn/apixanalysis/mobile/retrieve/phone/data/analyzed',
            'key'           =>      $key,
            'query_code'    =>      $token,
        ];
        $result1 = curlGet($arr);
        // $arr = [
        //     'apiurl'        =>      'http://e.apix.cn/apixanalysis/mobile/retrieve/phone/data/query',
        //     'key'           =>      $key,
        //     'query_code'    =>      $token,
        // ];
        // $result2 = curlGet($arr);
        //if(!$result1 && !$result2) return false;
        if(!$result1) return false;
		$result1 = json_decode($result1,true);
		if(isset($result1['errorMsg'])) return false;
		// $result2 = json_decode($result2,true);
		// if($result2['errorMsg']) return false;
        // $data = [
        //     'analyzed'      =>      json_decode($result1,true),
        //     'data'          =>      json_decode($result2,true)
        // ];
        $data = $result1;
        $info_model = model("Info");
        $r = $info_model->updateInfo($uid,['mobileAuth'=>json_encode($data)]);
        if(!$r) return false;
        return true;
    }


}
