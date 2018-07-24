<?php
namespace app\common\model;
use think\Model;
class Bankauth extends Model
{

    //请求验证银行卡
    //返回值:  -1:请求失败,0:查询失败,1:不符合,2:符合,3:其他错误
    public function checkBank($uid,$card,$name,$idcard,$phone)
    {
        $apiurl = 'http://api.id98.cn/api/v2/bankcard';
        $appkey = config('api')['smskey'];
        $url = $apiurl . '?appkey=' .$appkey.'&name='.$name.'&bankcardno='.$card.'&idcardno='.$idcard.'&tel='.$phone;
        $result = file_get_contents($url);
        if(!$result) return -1;
        $arr = json_decode($result,true);
        //数据库保存信息
        $data= [
            'uid'           =>      $uid,
            'bankcard'      =>      $card,
            'name'          =>      $name,
            'phone'         =>      $phone,
            'idcard'        =>      $idcard,
            'isok'          =>      $arr['isok'],
            'code'          =>      $arr['code']
        ];
        $this->data = $data;
        $this->isUpdate(false)->save();
        if($arr['isok'] == 0)
            return 0;
        if($arr['code'] == 1)
            return 2;
        if($arr['code'] == 2)
            return 1;
        return 3;
    }



}
