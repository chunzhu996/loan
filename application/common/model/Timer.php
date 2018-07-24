<?php
/**
 * Created by PhpStorm.
 * User: jiang
 * Date: 2018/10/15
 * Time: 13:39
 */

namespace app\common\model;
use think\Model;


class Timer extends Model
{
    const LOG_PRE = 'timer/';
    private $tips;

    public function addTimer()
    {
        // 如果不需要定时发送  include return 0 即可
        $run = include 'Timerconfig.php';
        if(!$run) die('process abort');
        $loan_model = model("Loan");
        $res =  $loan_model->getOverdueSms();
        foreach ($res as $item)
        {
            // 判断当前账户今日是否已经发过提醒
            $overdue_model = model('Overduesms');
            if($item['overdueday']>0){
                // 发送带延期的模板
                if($overdue_model->addSms($item['uid'],$item['mobile'])){
//                     model("Smscode")->sendSomeSms($item['mobile'],2004,$item['id']);
                     model("Smscode")->sendSomeSms($item['mobile'],2004,null);
                     $this->log($item['mobile']);
                }
            }else{
                // 发送提醒今日到期的模板
                if($overdue_model->addSms($item['uid'],$item['mobile'])){
                     model("Smscode")->sendSomeSms($item['mobile'],2004,null);
                     $this->log($item['mobile']);
                }
            }
        }
    }

    /**
     *  日志记录
     */
    private function log($con, $type = '') {
        $folder = self::LOG_PRE.$type.'/';
        $file = './TimerData/'.$folder;
        $time = time();
        $file = $file.'/'.date('Ym', $time).'/'.date('Ymd', $time).'.log';
        // 创建文件
        if (!$this->file($file)) {
            return false;
        }
        // 追加写入
        if (file_put_contents($file, var_export($con, true), FILE_APPEND | LOCK_EX) === false) {
            $this->tips = '写入文件失败！';
            return false;
        }
        return true;
    }

    /**
     *  判断文件是否存在 ，不存在创建
     */
    public function file($file) {
        $result = true;
        if (file_exists($file)) {
            return true;
        }
        // 创建目录
        if (!$this->mkDir(dirname($file))) {
            return false;
        }
        // 创建文件
        $fp = fopen($file, "x+");
        if (!$fp) {
            $result = false;
            $this->tips = $file.' 文件创建失败！';
        }
        fclose($fp);
        return $result;
    }

    /**
     *  创建文件夹
     */
    public function mkDir($path) {
        if (is_dir($path)) {
            return true;
        }
        if (!is_dir(dirname($path))) {
            if (!$this->mkDir(dirname($path))) {
                return false;
            }
            return $this->mkDir($path);
        }
        if (!mkdir($path)) {
            $this->tips = $path.' 目录创建失败！';
            return false;
        }
        return true;
    }
    /**
     *  获取 当前时间 毫秒级
     */
    public function microTime() {
        list($h, $t) = explode(' ', microtime());
        $date = date('Y-m-d H:i:s', $t);
        $time = $date.'.'.substr($h, 2);
        return $time;
    }

}