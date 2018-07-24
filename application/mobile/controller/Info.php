<?php
namespace app\mobile\controller;
class Info extends Common
{

    //初始化
    public function _initialize()
    {
        $this->user = $this->isLogin();
        if(!$this->user) $this->redirect('Index/login');
    }

    //获取信息认证数据
    public function getInfoAuth()
    {
        $info_model = model("Info");
        $info = $info_model->getAuth($this->user['id']);
        $this->success('success','',$info);
    }

    //实名认证
    public function identityAuth()
    {
        $user = $this->user;
        //判断是否已经认证
        $info_model = model("Info");
        if($info_model->ifIdentity($user['id']))
        {
            $this->error("实名认证成功后不允许修改");
        }
        if(Request()->isPost())
        {
            $name = input("name");
            $idcard = input("idcard");
            if(!isChineseName($name))
            {
                $this->error("请输入真实的姓名");
            }
            if(!isIdCard($idcard))
            {
                $this->error("请输入真实的身份证号码");
            }
            //判断身份证号码有没有被使用
            if($info_model->hasIdcard($idcard))
            {
                $this->error("身份证号码已被其他账户使用");
            }
            $idauth_model = model("Idauth");
            $r = $idauth_model->checkIdentity($user['mobile'],$name,$idcard);
            //返回值:  -1:请求失败,0:查询失败,1:不符合,2:符合,3:其他错误
            if($r == -1)
            $this->error('网络请求失败,请稍后再试');
            if($r == 0)
            $this->error('认证查询失败,请稍后再试');
            if($r == 1)
            $this->error('认证失败,请核对信息后重试');
            if($r == 2)
            {
                $data=['name'=>$name,'idcard'=>$idcard];
                $r = $info_model->updateInfo($user['id'],$data);
                if(!$r) $this->error('实名信息保存失败');
                $this->success('success','/mobile/user/userconfirm');
            }
            $this->error('认证失败,系统异常');
        }
        return $this->fetch();
    }

    //个人资料(联系地址，紧急联系人)
    public function personAuth()
    {
        $user = $this->user;
        //判断是否已经实名认证
        $info_model = model("Info");
        if(!$info_model->ifIdentity($user['id']))
        {
            $this->error("请先实名认证");
        }
        if(Request()->isPost())
        {
            foreach ($_POST as $value)
            {
                if(!$value || $value=='请选择' || $value=='请选择学历') $this->error('资料填写不完整');
            }
            $r = $info_model->updateInfo($user['id'],['personAuth'=>json_encode($_POST)]);
            if(!$r)
            {
                $this->error('保存失败');
            }
            $this->success('success','/mobile/user/userconfirm');
        }
        $info = $info_model->getInfo($user['id']);
        $personInfo = json_decode($info['personAuth'],true);

        $this->assign('data',$personInfo);
        return $this->fetch();
    }

    //运营商授权
    public function mobileAuth()
    {
        $user = $this->user;
        //判断是否已经填写个人资料
        $info_model = model("Info");
        if(!$info_model->ifPerson($user['id']))
        {
            $this->error("请先填写个人资料");
        }
        $mobileauth_model = model("Mobileauth");
        if($mobileauth_model->hasNotAuth($user['id']))
        {
            $this->error("您已申请授权认证,请等待授权结果返回");
        }
        $result = $mobileauth_model->getAuthUrl($user['id'],$info_model->getInfo($user['id']));
        if(!$result) $this->error('请求失败');
        if($result['code'] != '0') $this->error('请求出错:'.$result['code']);
        $url = $result['url'];
        $this->success('success',$url);
    }

    //淘宝授权
    public function taobaoAuth()
    {
        $user = $this->user;
        //判断是否已经填写个人资料
        $info_model = model("Info");
        if(!$info_model->ifPerson($user['id']))
        {
            $this->error("请先填写个人资料");
        }
        $taobaoauth_model = model("Taobaoauth");
        if($taobaoauth_model->hasNotAuth($user['id']))
        {
            $this->error("您已申请授权认证,请等待授权结果返回");
        }
        $result = $taobaoauth_model->getAuthUrl($user['id'],$info_model->getInfo($user['id']));
        if(!$result) $this->error('请求失败');
        if($result['errorCode'] != '0') $this->error('请求出错:'.$result['code']);
        $url = $result['url'];
        $this->success('success',$url);
    }

    //资料上传
    public function photoAuth()
    {
        $user = $this->user;
        //判断是否已经进行授权
        $info_model = model("Info");

		if(!$info_model->ifIdentity($user['id']))
        {
            $this->error("请先进行在资格验证");
        }
        $data = $info_model->getInfo($user['id']);
        $photo = json_decode($data['photoAuth'],true);
        if(!$photo)
        {
            $photo = ['idCardFrontImg'=>'','idCardReverseImg'=>'','selfPhotoImg'=>'','selfCardPhotoImg'=>''];
        }
        if(Request()->isPost())
        {
            $idCardFrontImg = Request()->file('idCardFrontImg');
            $idCardReverseImg = Request()->file('idCardReverseImg');
            $selfCardPhotoImg = Request()->file('selfCardPhotoImg');
            $selfPhotoImg = Request()->file('selfPhotoImg');
            if( (!$idCardFrontImg && !$photo['idCardFrontImg']) || (!$idCardReverseImg && !$photo['idCardReverseImg']) || (!$selfCardPhotoImg && !$photo['selfCardPhotoImg']) || (!$selfPhotoImg && !$photo['selfCardPhotoImg']) )
            {
                $this->error('请选择图片');
            }
			/*
            $fn1 = $this->savePhoto($idCardFrontImg);
            $fn2 = $this->savePhoto($idCardReverseImg);
            $fn3 = $this->savePhoto($selfCardPhotoImg);
            $fn4 = $this->savePhoto($selfPhotoImg);
			*/
			
			
			$fn1 = $this->saveAuthPhoto($idCardFrontImg);
			$fn2 = $this->saveAuthPhoto($idCardReverseImg);
			$fn3 = $this->saveAuthPhoto($selfCardPhotoImg);
			$fn4 = $this->saveAuthPhoto($selfPhotoImg);
			
            //dump($fn1);dump($fn2);dump($fn3);dump($fn4);
            if( (!$fn1 && !$photo['idCardFrontImg']) || (!$fn2 && !$photo['idCardReverseImg']) || (!$fn3 && !$photo['selfCardPhotoImg']) || (!$fn4 && !$photo['selfCardPhotoImg']) )
            {
                $this->error('文件上传出错,请稍后再试');
            }
            $data = [
				'idCardFrontImg'        =>      "uploads/".$fn1,
				'idCardReverseImg'      =>      "uploads/".$fn2,
				'selfCardPhotoImg'      =>      "uploads/".$fn3,
				'selfPhotoImg'          =>      "uploads/".$fn4
            ];
			
            $r = $info_model->updateInfo($user['id'],['photoAuth'=>json_encode($data)]);
            if(!$r) $this->error('保存失败');
            $this->success('success','/mobile/user/userconfirm');
        }
        $this->assign('data',$photo);
        //dump($photo);die;
        return $this->fetch();
    }
	
	// rewrite photo upload
	private function saveAuthPhoto($file)
	{
		// check file
		if(!$file) 
			return false;
		// get file info 
		$date = date('Ymd');
		// use md5 for to file folder, fixed at same time update errors.
		$info = $file->rule('md5')->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . $date );
		if($info)
		{
			return $date.DS.$info->getSaveName();
		}
		return $info->getError();
	}
	

    //处理上传图片
    private function savePhoto($file)
    {
        if(!$file) return false;
        $site = config('site');
    
		// 移动到框架应用根目录/public/uploads/ 目录下
		if($file){
			$info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' );
			if($info){
				// 成功上传后 获取上传信息
		 
			   // echo $info->getExtension();

		   
				$this->photos1= $info->getSaveName(0);
				$this->photos2= $info->getSaveName(1);
				$this->photos3= $info->getSaveName(2);
				$this->photos4= $info->getSaveName(3);
				// 输出 42a79759f284b767dfcb2a0197904287.jpg
			   // echo $info->getFilename(); 
			  
			}else{
				// 上传失败获取错误信息
				echo $file->getError();
			}
		}

    }

    //绑定提现银行卡
    public function bankAuth()
    {
        $user = $this->user;
        //判断是否已经上传照片
        $info_model = model("Info");
        if(!$info_model->ifPhotoUp($user['id']))
        {
            $this->error("请先完成资料上传");
        }
        $userinfo = $info_model->getInfo($user['id']);
        if(Request()->isPost())
        {
            $bankcard = input('post.bankcard');
            $phone    = input('post.phone');
            if(!checkMobile($phone))
            {
                $this->error("请输入正确的手机号");
            }
            if(!isBankCard($bankcard))
            {
                $this->error("请输入正确的银行卡号");
            }
            $bankauth_model = model("Bankauth");
            $r = 2;//$bankauth_model->checkBank($user['id'],$bankcard,$userinfo['name'],$userinfo['idcard'],$phone);
            //$r = 2;
            //返回值:  -1:请求失败,0:查询失败,1:不符合,2:符合,3:其他错误
            if($r == -1)
            $this->error('网络请求失败,请稍后再试');
            if($r == 0)
            $this->error('信息查询失败,请稍后再试');
            if($r == 1)
            $this->error('绑定失败,请核对信息后重试');
            if($r == 2)
            {
                $data=['bankAuth'=>json_encode(['card'=>$bankcard,'phone'=>$phone])];
                $r = $info_model->updateInfo($user['id'],$data);
                if(!$r) $this->error('银行卡信息保存失败');
                $this->success('success','/mobile/user/userconfirm');
            }
            $this->error('认证失败,系统异常');
        }
        $this->assign('info',$userinfo);
        return $this->fetch();
    }


}
