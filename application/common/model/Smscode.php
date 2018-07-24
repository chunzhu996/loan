<?php
namespace app\common\model;
use think\Model;
class Smscode extends Model
{

    //生成验证码
    public function makeCode($mobile,$type,$len = 4)
    {
        $str = '';
        for($i=0;$i<$len;$i++)
        {
            $str .= rand(0,9);
        }
        $arr = [
            'mobile'    =>  $mobile,
            'type'      =>  $type,
            'code'      =>  $str,
            'sendtime'  =>  time(),
            'status'    =>  0,
            'error'     =>  ''
        ];
        $this->data = $arr;
        $status = $this->isUpdate(false)->save();
        $data=[
            'status'=>0,
            'code'=>$str
        ];
        if($status)
            $data['status'] = $this->id;
        return $data;
    }

    //发送验证码
    public function sendSms($mobile,$code,$tplid,$smsid)
    {
        $sendurl = 'http://api.id98.cn/api/sms';
        $api  = config('api');
        $appkey  = $api['smskey'];
        //----------------------------------------------
//        $url = $sendurl.'?appkey='.$appkey.'&templateid='.$tplid.'&phone='.$mobile.'&param='.$code.',5';
        $url = $sendurl.'?appkey='.$appkey.'&templateid='.$tplid.'&phone='.$mobile.'&param='.$code;
        $result = file_get_contents($url);
        $arr = json_decode($result,true);
        if($arr['errcode'] != 0)
        {
            $this->save(['status'=>2,'error'=>$arr['errcode']],['id'=>$smsid]);
            return  false;
        }
        $this->save(['status'=>1],['id'=>$smsid]);
        return true;
    }

    //验证短信验证码
    public function checkCode($mobile,$code)
    {
        $r = $this->where(['mobile'=>$mobile,'code'=>$code,'status'=>1])->find();
        if(!$r) return false;
        $r = $r->toArray();
        if(!$r || ($r['sendtime'] + 5 * 60) < time() )
            return false;
        return true;
    }

    //检查是否频繁
    public function checkNum($mobile)
    {
        $num = $this->where(['mobile'=>$mobile])->where('sendtime','>',time()-60)->count();
        if($num)
            return false;
        return true;
    }

    //检查今日是否频繁
    public function checkTodaynum($mobile)
    {
        $num = $this->where(['mobile'=>$mobile])->where('sendtime','>',strtotime(date('Y-m-d')))->count();
        if($num > 6)
            return false;
        return true;
    }

    //发送任意短信
    //手机号,模板ID,参数
    public function sendSomeSms($mobile,$tplid,$arr)
    {
        $sendurl = 'http://api.id98.cn/api/sms';
        $api  = config('api');
        $appkey  = $api['smskey'];
        if(!$tplid) return false;
        //----------------------------------------------
        if(!is_array($arr)) $arr=[$arr];
        $param = implode(',',$arr);
        $url = $sendurl.'?appkey='.$appkey.'&templateid='.$tplid.'&phone='.$mobile.'&param='.$param;
        $result = file_get_contents($url);
        $arr = json_decode($result,true);
        $data = [
            'mobile'        =>      $mobile,
            'type'          =>      'other',
            'code'          =>      $param,
            'sendtime'      =>      time(),
            'status'        =>      0
        ];
        if($arr['errcode'] != 0)
        {
            $data['status'] = 2;
            $data['error'] = $arr['errcode'];
            $this->isUpdate(false)->save($data);
            return false;
        }
        $data['status'] = 1;
        $this->isUpdate(false)->save($data);
        return true;
    }


}
