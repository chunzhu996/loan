<?php
namespace app\common\model;
use think\Model;
class Idauth extends Model
{

    //利用接口实名认证
    //返回值:  -1:请求失败,0:查询失败,1:不符合,2:符合,3:其他错误
    public function checkIdentity($mobile,$name,$idcard)
    {
        /*$apiurl = 'http://api.id98.cn/api/idcard';
        $appkey = config('api')['smskey'];
        $url = $apiurl . '?appkey=' .$appkey.'&name='.$name.'&cardno='.$idcard;
        $result = file_get_contents($url);
        if(!$result) return -1;*/
        //$arr = ['isok'=>1];
        //数据库保存信息
        $data= [
            'mobile'        =>      $mobile,
            'name'          =>      $name,
            'idcard'        =>      $idcard,
            'status'        =>      2,
            'code'          =>      2
        ];
        $this->data = $data;
        $this->isUpdate(false)->save();

            return 2;
   
    }




}
