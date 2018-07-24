<?php
namespace app\common\model;
use think\Model;
class Msg extends Model
{

    protected $type = [
        'add_time'  =>  'timestamp'
    ];

    //获取消息列表
    public function getList($uid = 0,$page = 1)
    {
        $onePageNum = 10;//每页显示数量
        $limit = ($page-1) * $onePageNum . "," . $onePageNum;
        if(!$uid)
        {
            $r = $this->order('add_time','desc')->limit($limit)->select();
        }
        else $r = $this->where('uid',$uid)->limit($limit)->order('add_time','desc')->select();
        if(!$r) return [];
        $list = $r;
        $r = [];
        for ($i=0; $i < count($list); $i++)
        {
            $data = $list[$i]->data;
            if(!$data['status'])
            {
                $r[] = ['id'=>$data['id'],'status'=>1];
            }
        }
        $this->saveAll($r);
        return $list;
    }

    //查询未读消息数量
    public function getUnreadNum($uid)
    {
        $r = $this->where(['uid'=>$uid,'status' => 0])->count();
        return $r;
    }

    //新增消息
    public function newMsg($uid,$title,$content)
    {
        $arr = [
            'uid'       =>      $uid,
            'title'     =>      $title,
            'content'   =>      $content,
            'add_time'  =>      time(),
            'status'    =>      0
        ];
        $result=$this->isUpdate(false)->save($arr);
        return $result;
    }


}
