<?php
namespace app\mobile\controller;
class Msg extends Common
{
    //消息列表
    public function index()
    {
        return $this->fetch();
    }

    //获取消息列表
    public function getMsg()
    {
        $page = input("post.page");
        if(!$page) $page = 1;
        $user = $this->isLogin();
        $msg_model = model("Msg");
        $data = $msg_model->getList($user['id'],$page);
        if(!$data) $this->error("暂时没有新消息了");
        $this->success("success",'',$data);
    }

    //获取未读消息数量
    public function getUnreadNum()
    {
        $user = $this->isLogin();
        $msg_model = model("Msg");
        $num = $msg_model->getUnreadNum($user['id']);
        $this->success('success','',$num);
    }


}
