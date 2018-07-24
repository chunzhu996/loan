<?php
// 应用公共文件



/**
 * 删除一个目录
 * 包括子目录
 * @param $dir
 * @return bool
 */
function del_dir($dir,$deldir=false){
    if(is_dir($dir)){
        foreach(scandir($dir) as $row){
            if($row == '.' || $row == '..'){
                continue;
            }
            $path = $dir .'/'. $row;
            if(filetype($path) == 'dir'){
                del_dir($path);
            }else{
                unlink($path);
            }
        }
        if($deldir)
            rmdir($dir);
    }else{
        return false;
    }
}

/**
 * 将数组写出配置文件
 * @param $arr 写入数组
 * @param $filename 保存文件名
 * @param $reset 是否合并
 * @param $delother 删除多余
 * @return bool
 */
function save_config($arr,$filename,$reset = false,$delother = false)
{
    $filepath = CONF_PATH.'/extra/'.$filename.'.php';
    if(!file_exists($filepath)){
        $file = fopen($filepath,"w");
        fclose($file);
    }
    $oldarr = include($filepath);
    if(is_array($oldarr)){
        foreach ($oldarr as $key => $value)
        {
            if(!isset($arr[$key]))
            {
                if(!$delother)
                {
                    $arr[$key] = $value;
                }
            }else
            {
                if(!$reset)
                {
                    $arr[$key] = $value;
                }
            }
        }
    }
    //写出文件
    $str = '<?php return [';
    foreach ($arr as $key => $value)
    {
        $value = htmlspecialchars($value);
        $str .= "'{$key}'=>'{$value}',";
    }
    $str .= '];';
    if(!file_put_contents($filepath,$str))
        return false;
    return true;
}

/**
 *   验证手机号规则
 */
function checkMobile($mobile)
{
    if (!is_numeric($mobile)) {
        return false;
    }
    return preg_match('#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,1,2,3,4,5,6,7,8]{1}\d{8}$|^18[\d]{9}$#', $mobile) ? true : false;
}

/**
 *  验证身份证号码有效性
 */
function isIdCard($number) {
    if(empty($number)) return false;
    //加权因子
    $wi = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
    //校验码串
    $ai = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
    //按顺序循环处理前17位
    $sigma = 0;
    for ($i = 0;$i < 17;$i++) {
        //提取前17位的其中一位，并将变量类型转为实数
        $b = (int) $number{$i};
        //提取相应的加权因子
        $w = $wi[$i];
        //把从身份证号码中提取的一位数字和加权因子相乘，并累加
        $sigma += $b * $w;
    }
    //计算序号
    $snumber = $sigma % 11;
    //按照序号从校验码串中提取相应的字符。
    $check_number = $ai[$snumber];
    if ($number{17} == $check_number) {
        return true;
    } else {
        return false;
    }
}

/**
 *  验证姓名有效性
 */
function isChineseName($name){
    if (preg_match('/^([\xe4-\xe9][\x80-\xbf]{2}){2,4}$/', $name)) {
		return true;
	} else {
		return false;
	}
}

/*
 *  CurlGet请求
 */
function curlGet($par)
{
    $curl = curl_init();
    $apiurl = $par['apiurl'];
    $key = $par['key'];
    unset($par['apiurl']);
    unset($par['key']);
    $str = http_build_query($par);
    curl_setopt_array($curl, array(
        CURLOPT_URL => $apiurl.'?'.$str,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "accept: application/json",
            "apix-key: ".$key,
            "content-type: application/json"
        ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        return false;
    } else {
        return $response;
    }
}

/*
 *  格式化金额
 */
function makeMoney($money)
{
    return number_format($money,2,".","");
}


/**
 * PHP实现Luhn算法验证银行卡号
 * @author：http://nonfu.me
 */
function isBankCard($no)
{
    if(!$no) return false;
    $arr_no = str_split($no);
    $last_n = $arr_no[count($arr_no)-1];
    krsort($arr_no);
    $i = 1;
    $total = 0;
    foreach ($arr_no as $n){
        if($i%2==0){
            $ix = $n*2;
            if($ix>=10){
                $nx = 1 + ($ix % 10);
                $total += $nx;
            }else{
                $total += $ix;
            }
        }else{
            $total += $n;
        }
        $i++;
    }
    $total -= $last_n;
    $total *= 9;
    if($last_n == ($total%10)){
        return true;
    }
    return false;
}
