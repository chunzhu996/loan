<?php
namespace app\common\model;
use think\Model;
class Taobaoauth extends Model
{

    //查询用户有没有未完成认证记录
    public function hasNotAuth($uid)
    {
        $r = $this->where(['uid'=>$uid,'token'=>'','status'=>0,'add_time'=>['>', (time()-10*60) ] ])->count();
        if($r) return true;
        return false;
    }

    public function getAuthUrl($uid,$info)
    {
        $key = config('api')['tbkey'];
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
            'apiurl'        =>      'http://e.apix.cn/apixanalysis/tb/grant/ele_business/taobao/pages',
            'key'           =>      $key,
            'callback_url'  =>      $siteurl.'/mobile/callback/taobao/?authid='.$this->id.'&uid='.$uid,
            'success_url'   =>      $siteurl.'/mobile/user/index',
            'failed_url'    =>      $siteurl.'/mobile/user/index',
        ];
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
        $key = config('api')['tbkey'];
        $arr = [
            'apiurl'        =>      'http://e.apix.cn/apixanalysis/tb/retrieve/ele_business/taobao/query',
            'key'           =>      $key,
            'query_code'    =>      $token,
        ];
        $result = curlGet($arr);
        if(!$result) return false;
        $info_model = model("Info");
        $r = $info_model->updateInfo($uid,['taobaoAuth'=>$result]);
        if(!$r) return false;
        return true;
    }


}
