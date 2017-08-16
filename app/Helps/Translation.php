<?php
/**
 * 百度API翻译
 * Created by PhpStorm.
 * User: Norton
 * Date: 2016/8/10
 * Time: 17:30
 */
namespace App\Helps;
class Translation{

    private $URL='http://api.fanyi.baidu.com/api/trans/vip/translate';
    private $APP_ID='20160810000026566'; //测试账号
    private $SEC_KEY='lxFBZbXjMmV9BfUjnpZP';
    //翻译入口
    function translate($query, $from='auto', $to='en'){

        $args = array(
            'q' => $query,
            'appid' => $this->APP_ID,
            'salt' => rand(10000,99999),
            'from' => $from,
            'to' => $to,

        );
        $args['sign'] = $this->buildSign($query, $this->APP_ID, $args['salt'], $this->SEC_KEY);
        $ret = $this->call($this->URL, $args);
        $ret = json_decode($ret, true);
        return $ret;
    }

//加密
    function buildSign($query, $appID, $salt, $secKey){
        $str = $appID . $query . $salt . $secKey;
        $ret = md5($str);
        return $ret;
    }

//发起网络请求
    function call($url, $args=null, $method="post", $testflag = 0, $timeout = 10, $headers=array()){
        $ret = false;
        $i = 0;
        while($ret === false)
        {
            if($i > 1)
                break;
            if($i > 0) {
                sleep(1);
            }
            $ret = $this->callOnce($url, $args, $method, false, $timeout, $headers);
            $i++;
        }
        return $ret;
    }

    function callOnce($url, $args=null, $method="post", $withCookie = false, $timeout = 10, $headers=array()){
        $ch = curl_init();
        if($method == "post")
        {
            $data = $this->convert($args);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        else
        {
            $data = $this->convert($args);
            if($data)
            {
                if(stripos($url, "?") > 0) {
                    $url .= "&$data";
                } else {
                    $url .= "?$data";
                }
            }
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if(!empty($headers)){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        if($withCookie) {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $_COOKIE);
        }
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }

    function convert(&$args){
        $data = '';
        if (is_array($args)) {
            foreach ($args as $key=>$val) {
                if (is_array($val)) {
                    foreach ($val as $k=>$v) {
                        $data .= $key.'['.$k.']='.rawurlencode($v).'&';
                    }
                } else {
                    $data .="$key=".rawurlencode($val)."&";
                }
            }
            return trim($data, "&");
        }
        return $args;
    }
}
