<?php
/**
 * Created by PhpStorm.
 * User: jiang
 * Date: 2018/10/15
 * Time: 14:28
 */

namespace app\common\model;
use think\Model;

class Overduesms extends Model
{
    //判断是否今天已经发送过提醒
    public function ifExist($mobile)
    {
        $sendTime = date('Y-m-d', $_SERVER['REQUEST_TIME']);
        $r = $this->where(['mobile'=>$mobile,'sendtime'=> $sendTime])->count();
        if($r)
            return false;
        return true;
    }

    // 发送后添加记录
    public function addSms($uid, $mobile)
    {
        if(!$this->ifExist($mobile))
            return false;

        $this->data = [
            'uid'        =>      $uid,
            'mobile'      =>      $mobile,
            'addtime'         =>     date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
            'sendtime'         =>     date('Y-m-d', $_SERVER['REQUEST_TIME']),
        ];
        $r = $this->isUpdate(false)->save();
        if(!$r)
            return false;
        return true;
    }

}