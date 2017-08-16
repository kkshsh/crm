<?php
/**
 * Created by PhpStorm.
 * User: lilifeng
 * Date: 2016-05-24
 * Time: 13:17
 */
namespace App\Modules\Channel\Adapter;
use App\Models\OrderModel;
use Illuminate\Support\Facades\DB;
use App\Models\Channel\AccountModel;
use App\models\Publish\Smt\smtCategoryAttribute;
use App\Models\Message\MessageModel;
set_time_limit(1800);
Class AliexpressAdapter implements AdapterInterface
{
    const GWURL = 'gw.api.alibaba.com';
    private $_access_token;         //获取数据令牌
    private $_refresh_token;        //刷新令牌
    private $_access_token_date;    //获取令牌时间
    private $_appkey;               //应用key
    private $_appsecret;            //应用密匙
    private $_returnurl;            //回传地址
    private $_version = 1;
    private $_aliexpress_member_id;
    public  $_operator_id;
    public  $_customer_service_id;
    public function __construct($config)
    {
        $this->_appkey = $config["appkey"];
        $this->_appsecret = $config["appsecret"];
        $this->_returnurl = $config["returnurl"];
        $this->_access_token_date = $config["access_token_date"];
        $this->_refresh_token = $config["refresh_token"];
        $this->_aliexpress_member_id = $config['aliexpress_member_id'];
        $this->_access_token = $access_token = $this->resetAccesstokenEveryTime();   //刷新accesstoken
        $this->_operator_id = $config['operator_id'];
        $this->_customer_service_id = $config['customer_service_id'];
    }
    /** 获取订单
     * @param $startDate 开始时间
     * @param $endDate 结束时间
     * @param array $status 订单状态
     * @param int $perPage 页码
     * @param string $nextToken
     * @return array
     */
    public function listOrders($startDate, $endDate, $status = [], $perPage = 10, $nextToken = '')
    {
        if (empty($nextToken)) {
            $nextToken = 1;
        }
        $orders = [];
        $startDate = empty($startDate) ? date("m/d/Y H:i:s", strtotime('-30 day')) : date("m/d/Y H:i:s",
            strtotime($startDate));
        $startDate = $startDate>'2016-10-27 17:00:00'?$startDate:'2016-10-27 17:00:00';
        $endDate = empty($endDate) ? date("m/d/Y H:i:s", strtotime('-12 hours')) : date("m/d/Y H:i:s",
            strtotime($endDate));
        $param = "page=" . $nextToken . "&pageSize=" . $perPage . "&orderStatus=" . $status . "&createDateStart=" . rawurlencode($startDate) . "&createDateEnd=" . rawurlencode($endDate);


        $orderjson = $this->getJsonData('api.findOrderListQuery', $param);
        $orderList = json_decode($orderjson, true,512,JSON_BIGINT_AS_STRING);
        unset($orderjson);
        if (isset($orderList['orderList'])) {
            foreach ($orderList['orderList'] as $list) {
                //echo $list['orderStatus'];exit;
                if($list['orderStatus'] !='IN_CANCEL'){
                    $thisOrder = orderModel::where('channel_ordernum',
                        $list['orderId'])->first();     //获取详情之前 进行判断是否存在 存在就没必要调API了
                    if ($thisOrder) {
                        continue;
                    }
                }
                $param = "orderId=" . $list['orderId'];
                $orderjson = $this->getJsonData('api.findOrderById', $param);
                $orderDetail = json_decode($orderjson, true,512,JSON_BIGINT_AS_STRING);
                if ($orderDetail) {
                    $order = $this->parseOrder($list, $orderDetail);
                    if ($order) {
                        $orders[] = $order;
                    }
                } else {
                    continue;
                }
            }
            $nextToken++;
        } else {
            if(isset($orderList['error_code'])){
                return  [
                    'error' => [
                        'code' => $orderList['error_code'],
                        'message' =>isset($orderList['error_message'])?$orderList['error_message']:''
                    ]
                ];
            }
            $nextToken ='';
        }
        return ['orders' => $orders, 'nextToken' => $nextToken];
    }
    /**获取订单(暂时没有用)
     * @param $startDate
     * @param $endDate
     * @param $status
     * @param int $page
     * @param int $perPage
     * @return mixed
     */
    public function listOrdersOther($startDate, $endDate, $status, $page = 1, $perPage = 10)
    {
        $startDate = empty($startDate) ? date("m/d/Y H:i:s", strtotime('-30 day')) : date("m/d/Y H:i:s",
            strtotime($startDate));
        $endDate = empty($endDate) ? date("m/d/Y H:i:s", strtotime('-12 hours')) : date("m/d/Y H:i:s",
            strtotime($endDate));
        $param = "page=" . $page . "&pageSize=" . $perPage . "&orderStatus=" . $status . "&createDateStart=" . rawurlencode($startDate) . "&createDateEnd=" . rawurlencode($endDate);
        //echo $param.'<br/>';
        $orderjson = $this->getJsonData('api.findOrderListQuery', $param);
        return json_decode($orderjson, true);
    }
    /** 获取订单详情
     * @param $orderID 订单号
     * @return mixed
     */
    public function getOrder($orderID)
    {
        $param = "orderId=" . $orderID;
        $orderjson = $this->getJsonData('api.findOrderById', $param);
        return json_decode($orderjson, true);
    }
    /** 回传追踪号
     * @param $tracking_info 需要上传的信息
     * @return array
     */
    public function returnTrack($tracking_info)
    {
        $return = [];
        $action = 'api.sellerShipment';
        $app_url = "http://" . self::GWURL . "/openapi/";
        $api_info = "param2/" . $this->_version . "/aliexpress.open/{$action}/" . $this->_appkey . "";
        $tracking_info['access_token'] = $this->_access_token;
        $tracking_info['_aop_signature'] = $this->getApiSignature($api_info, $tracking_info);
        $result = $this->postCurlHttpsData ( $app_url.$api_info,  $tracking_info);
        $result = json_decode($result,true);
        if (isset($result['success']) && ($result['success'] == 'true')) {
            $return['status'] = true;
            $return['info'] = 'Success';
        } else {
            $return['status'] = false;
            $return['info'] = isset($result['error_message']) ? $result['error_message'] : "error";
        }
        return $return;
    }
    /** 获取需要评价的订单
     * @param int $currentPage 页码
     * @param int $pageSize 页数
     * @return bool
     */
    public function getSellerEvaluationOrderList($currentPage=1, $pageSize=100){
        $param ="currentPage=$currentPage&pageSize=$pageSize";
        $result = $this->getJsonData('api.evaluation.querySellerEvaluationOrderList', $param);
        $result = json_decode($result,true);
        if(isset($result['listResult'])||!empty($result['listResult'])){
            return     $result['listResult'];
        }else{
            return false;
        }
    }
    /** 评价订单
     * @param $orderID 订单号
     * @param string $text 内容
     * @param int $score 评分
     */
    public function evaluateOrder($orderID,$text='Excellent buyer, welcome! Any questions please contact us.',$score=5){
        $feedbackContent = rawurlencode($text);//评价的内容
        $param = "orderId=$orderID&score=$score&feedbackContent=$feedbackContent";
        $result = $this->getJsonData("api.evaluation.saveSellerFeedback", $param);
        var_dump($result);
    }
    /** 订单信息的组装
     * @param $list
     * @param $orderDetail
     * @return array
     */
    public function parseOrder($list, $orderDetail)
    {
        $orderInfo = array();
        $productInfo = array();
        $ship_price = 0;
        $orderProductArr = $list ["productList"][0];
        $order_remark = array();
        foreach ($list ["productList"] as $p) {
            if (isset($p['memo']) && !empty($p['memo'])) {
                $order_remark[$p['childId']] = $p['memo']; //带ID进去吧
            }
            if (trim($p['logisticsServiceName']) != "Seller's Shipping Method") {
                $orderProductArr ["logisticsServiceName"] = $p['logisticsServiceName'];
            }
            $ship_price = $p["logisticsAmount"] ["amount"]; //多个sku的运费 不进行叠加了 因为这个时候就是总运费了
        }
        $orderInfo['channel_ordernum'] = $list['orderId'];
        $orderInfo["email"] = isset($list["buyerInfo"]["email"]) ? $list["buyerInfo"]["email"] : '';
        $orderInfo['amount'] = $list ["payAmount"] ["amount"];
        $orderInfo['currency'] = $list["payAmount"] ["currencyCode"];
        $orderInfo['payment'] = $list['paymentType'];
        $orderInfo['amount_shipping'] = $ship_price;
        $orderInfo['shipping'] = $orderProductArr['logisticsServiceName'];
        $orderInfo['customer_remark'] = $order_remark ? addslashes(implode('<br />', $order_remark)) : ''; //订单备注
        $orderInfo['shipping_firstname'] =  isset($orderDetail ["receiptAddress"]["contactPerson"])?$orderDetail ["receiptAddress"]["contactPerson"]:'';
        $orderInfo['shipping_lastname'] = '';
        $orderInfo['shipping_address'] = isset($orderDetail ["receiptAddress"] ["detailAddress"])?$orderDetail ["receiptAddress"] ["detailAddress"]:'';
        $orderInfo['shipping_address1'] = isset($orderDetail ["receiptAddress"] ["address2"]) ? $orderDetail ["receiptAddress"] ["address2"] : '';
        $orderInfo['shipping_city'] = isset($orderDetail ["receiptAddress"]["city"])?$orderDetail ["receiptAddress"]["city"]:'';
        $orderInfo['shipping_state'] = isset($orderDetail ["receiptAddress"] ["province"])?$orderDetail ["receiptAddress"] ["province"]:'';
        $orderInfo['shipping_country'] = isset($orderDetail ["receiptAddress"] ["country"])?$orderDetail ["receiptAddress"] ["country"]:'';
        $orderInfo['shipping_zipcode'] = isset($orderDetail ["receiptAddress"] ["zip"])?$orderDetail ["receiptAddress"] ["zip"]:'';
        if($list['orderStatus']=='IN_CANCEL'){
            $orderInfo['status'] = 'CANCEL';
        }else{
            $orderInfo['status'] = 'PAID';
        }
        $leftSendGoodDay = isset($list["leftSendGoodDay"])?(int)$list["leftSendGoodDay"]:0;
        $leftSendGoodHour = isset($list["leftSendGoodHour"])?(int)$list["leftSendGoodHour"]:0;
        $leftSendGoodMin = isset($list["leftSendGoodMin"])?(int)$list["leftSendGoodMin"]:0;
        $orderInfo['orders_expired_time'] =date('Y-m-d H:i:s',time()+$leftSendGoodDay*24*60*60+$leftSendGoodHour*60*60+$leftSendGoodMin*60);
        $mobileNo = isset($orderDetail ["receiptAddress"] ["mobileNo"]) ? $orderDetail ["receiptAddress"] ["mobileNo"] : '';
        $phoneCountry = isset($orderDetail ["receiptAddress"] ["phoneCountry"]) ? $orderDetail ["receiptAddress"] ["phoneCountry"] : '';
        $phoneArea = isset($orderDetail ["receiptAddress"] ["phoneArea"]) ? $orderDetail ["receiptAddress"] ["phoneArea"] : '';
        $phoneNumber = isset($orderDetail ["receiptAddress"] ["phoneNumber"]) ? $orderDetail ["receiptAddress"] ["phoneNumber"] : '';
        $phoneNumber = $phoneCountry . "-" . $phoneArea . "-" . $phoneNumber;
        $orderInfo['shipping_phone'] = $mobileNo != "" ? $mobileNo : $phoneNumber;
        $orderInfo['payment_date'] = $this->getPayTime($list['gmtPayTime']);
        $orderInfo['aliexpress_loginId'] = $orderDetail['buyerInfo']['loginId'];
        $orderInfo['by_id'] = isset($orderDetail ["buyerSignerFullname"])?$orderDetail ["buyerSignerFullname"]:'';
        $childProductArr = $orderDetail['childOrderList'];
        foreach ($childProductArr as $childProArr) {
            $skuCode = trim($childProArr ["skuCode"]);
            $n = strpos($skuCode, '*');
            $sku_new = $n !== false ? substr($skuCode, $n + 1) : $skuCode;
            $n = strpos($sku_new, '#');
            $sku_new = $n !== false ? substr($sku_new, 0, $n) : $sku_new;
            $sku_new = str_ireplace('{YY}', '', $sku_new);
            unset($qty);
            $qty = 1;
            if (strpos($sku_new, '(') !== false) {
                $matches = array();
                preg_match_all("/(.*?)\([a-z]?([0-9]*)\)?/i", $sku_new, $matches);
                $sku_new = trim($matches[1][0]);
                $qty = trim($matches[2][0]) ? trim($matches[2][0]) : 1;
            }
            $productInfo[$sku_new]['channel_sku'] = trim($childProArr ["skuCode"]);
            $productInfo[$sku_new]["sku"] = $sku_new;
            $productInfo[$sku_new]["price"] = $qty ? $childProArr["productPrice"]["amount"] / $qty : $childProArr["productPrice"]["amount"];
            $productInfo[$sku_new]["quantity"] = isset($productInfo[$sku_new]["quantity"]) ? $productInfo[$sku_new]["quantity"] : 0;
            $productInfo[$sku_new]["quantity"] += $qty ? $childProArr["productCount"] * $qty : $childProArr["productCount"];
            $productInfo[$sku_new]['currency'] = $childProArr['initOrderAmt']['currency']['currencyCode'];
            $productInfo[$sku_new]['orders_item_number'] = $childProArr['productId'];
            if (!empty($order_remark) && !empty($order_remark[$childProArr['id']])) { // --各SKU相应的备注信息
                $productInfo[$sku_new]["remark"] = isset($productInfo[$sku_new]["remark"]) ? $productInfo[$sku_new]["remark"] . ' ' . $order_remark[$childProArr['id']] : $order_remark[$childProArr['id']]; //备注信息
            }
        }
        foreach ($productInfo as $pro) {
            $orderInfo['items'][] = $pro;
        }
        return $orderInfo;
    }
    /** 支付时间 转换
     * @param $paytime
     * @return bool|string
     */
    public function getPayTime($paytime)
    {
        $str = mb_substr($paytime, 0, 14);
        return date('Y-m-d H:i:s', strtotime($str));
    }
    /**
     * 使用access_token 令牌获取数据
     * @param string $action api动作
     * @param string $parameter 传输参数
     * @param boolen $_aop_signature 是否需要签名
     */
    public function getJsonData($action, $parameter, $_aop_signature = true)
    {
        //接口URL
        $app_url = "http://" . self::GWURL . "/openapi/";
        //apiinfo	aliexpress.open
        $apiInfo = "param2/" . $this->_version . "/aliexpress.open/{$action}/" . $this->_appkey;
        //参数
        $app_parameter_url = ($parameter ? "$parameter&" : '') . "access_token=" . $this->_access_token;
        $sign_url = '';
        if ($_aop_signature) { //是否需要签名
            //获取对应URL的签名
            $sign = $this->getApiSign($apiInfo, $app_parameter_url);
            $sign_url = "&_aop_signature=$sign"; //签名参数
        }
        //组装URL
        $get_url = $app_url . $apiInfo . '?' . $app_parameter_url . $sign_url;
        //if ( $this->debug ) echo $get_url. "\n";
        $result = $this->getCurlData($get_url);
        return $result;
    }
    /**
     * Curl http Get 数据
     * 使用方法：
     */
    public function getCurlData($remote_server)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remote_server);
        //curl_setopt ( $ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded; charset=utf-8'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 获取数据返回
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true); // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        $output = curl_exec($ch);
        if (curl_errno($ch)) {
            // $this->setCurlErrorLog(curl_error ( $ch ));
            die(curl_error($ch)); //异常错误
        }
        curl_close($ch);
        $this->checkToken($output);
        return $output;
    }
    /**
     * Curl http Post 数据
     * 使用方法：
     * $post_string = "app=request&version=beta";
     */
    public function postCurlData($remote_server, $post_string)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remote_server);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        $data = curl_exec($ch);
        if (curl_errno($ch)) {
            //  $this->setCurlErrorLog(curl_error ( $ch ));
            die(curl_error($ch)); //异常错误
        }
        curl_close($ch);
        $this->checkToken($data);
        return $data;
    }
    /**
     *
     * API签名算法主要是使用urlPath和请求参数作为签名因子进行签名，主要针对api 调用
     * @param $apiInfo URL信息
     * @param $strcode 参数
     */
    public function getApiSign($apiInfo, $strcode)
    {
        $code_arr = explode("&", $strcode);//去掉&
        $newcode_arr = array();
        foreach ($code_arr as $key => $val) {
            $code_narr = explode("=", $val);//分割=
            $newcode_arr [$code_narr [0]] = $code_narr [1];//重组数组
        }
        ksort($newcode_arr);//排序
        $sign_str = "";
        foreach ($newcode_arr as $key => $val) {//获取值
            $sign_str .= $key . rawurldecode($val);
        }
        $sign_str = $apiInfo . $sign_str;//连接
        //加密
        //if ( $this->debug ) echo $sign_str. "\n";
        $code_sign = strtoupper(bin2hex(hash_hmac("sha1", $sign_str, $this->_appsecret, true)));
        return $code_sign;
    }
    /**
     * 计算签名
     * @param $apiInfo
     * @param $parameter_arr
     * @return string
     */
    public function getApiSignature($apiInfo, $parameter_arr)
    {
        ksort($parameter_arr);
        $sign_str = '';
        if (array_key_exists('domesticLogisticsCompanyId', $parameter_arr) && array_key_exists('domesticLogisticsCompany', $parameter_arr)) {
            $domesticLogisticsCompanyIdIndex = 0; //在数组中的位置
            $domesticLogisticsCompanyIndex = 0; //该元素在数组中的位置
            $domesticLogisticsCompanyIdStr = $domesticLogisticsCompanyStr = ''; //中间变量
            $i = 0;
            $temp = array(); //中间变量
            foreach ($parameter_arr as $key => $val) {
                $temp[$i] = $key . $val;
                if (in_array($key, array('domesticLogisticsCompanyId', 'domesticLogisticsCompany'))) {
                    $index = $key . 'Index';
                    $str = $key . 'Str';
                    $$index = $i;
                    $$str = $key . $val;
                }
                $i++;
            }
            //实现位置进行替换
            $temp[$domesticLogisticsCompanyIdIndex] = $domesticLogisticsCompanyStr;
            $temp[$domesticLogisticsCompanyIndex] = $domesticLogisticsCompanyIdStr;
            $sign_str = implode('', $temp);
            unset($temp);
        } else {
            foreach ($parameter_arr as $key => $val) {
                $sign_str .= $key . $val;
            }
        }
        $sign_str = $apiInfo . $sign_str;
        $code_sign = strtoupper(bin2hex(hash_hmac("sha1", $sign_str, $this->_appsecret, true)));
        return $code_sign;
    }
    /**
     * 获取acees_token
     * 判断access_token是否过期(10小时)
     */
    public function isResetAccesstoken($is_now=false)
    {
        $now = date("Y-m-d H:i:s");
        $hours = (strtotime($now) - strtotime($this->_access_token_date)) / 60 / 60;
        if($is_now){
            $json = $this->resetAccessToken(); //获取最新的access_token
            $data = json_decode($json, true);
            DB::table('message_accounts')->where('aliexpress_member_id', $this->_aliexpress_member_id)->update([
                'aliexpress_access_token' => $data["access_token"],
                'aliexpress_access_token_date' => date('Y-m-d H:i:s')
            ]);
        }else{
            if ($hours > 9.5) { //大于10小时(提前半小时)
                $json = $this->resetAccessToken(); //获取最新的access_token
                $data = json_decode($json, true);
                DB::table('message_accounts')->where('aliexpress_member_id', $this->_aliexpress_member_id)->update([
                    'aliexpress_access_token' => $data["access_token"],
                    'aliexpress_access_token_date' => date('Y-m-d H:i:s')
                ]);
                return $data["access_token"];
            } else {
                return false;
            }
        }
    }

    /**
     * 获取acees_token
     * 判断access_token是否过期(10小时)
     */
    public function resetAccesstokenEveryTime()
    {
        $json = $this->resetAccessToken(); //获取最新的access_token
        $data = json_decode($json, true);
        DB::table('message_accounts')->where('aliexpress_member_id', $this->_aliexpress_member_id)->update([
            'aliexpress_access_token' => $data["access_token"],
            'aliexpress_access_token_date' => date('Y-m-d H:i:s')
        ]);
        return $data["access_token"];

    }



    public function sendToSlme($access_token,$member_id){
        $url = 'http://v2.erp.moonarstore.com/admin/auto/auto_smt_refresh_access_token/get_v3_access_token?key=SLME5201314&access_token='.$access_token.'&member_id='.$member_id;
        $this-> getCurlData($url);
    }
    public function checkToken($data){
        $data = json_decode($data,true);
        if(isset($data['error_code'])&&isset($data['error_message'])){
            if($data['error_code']==401&&$data['error_message']='Request need user authorized'){
                $this->isResetAccesstoken(true);
            }
        }
    }
    /**
     *
     * refreshToken换取accessToken  POST https
     * @param string $refresg_token
     */
    public function resetAccessToken()
    {
        $serverurl = "https://" . self::GWURL . "/openapi/http/" . $this->_version . "/system.oauth2/getToken/" . $this->_appkey . "";
        $refresh_token = $this->_refresh_token;
        $postdata = "grant_type=refresh_token&client_id=" . $this->_appkey . "&client_secret=" . $this->_appsecret . "&refresh_token=" . $refresh_token . "";
        return $this->postCurlHttpsData($serverurl, $postdata);
    }

    /**
     *
     * 使用code获取令牌
     * 返回令牌   refresh_token 用于刷新令牌  access_token 用于获取数据  memderID 用户ID
     */
    public function getAppCode($code) {
        $getAppCodeUrl = "https://" . self::GWURL . "/openapi/http/".$this->_version."/system.oauth2/getToken/" . $this->_appkey . "";
        $postdata = "grant_type=authorization_code&need_refresh_token=true&client_id=" . $this->_appkey . "&client_secret=" . $this->_appsecret . "&redirect_uri=" . $this->_returnurl . "&code=" . $code . "";
        return $this->postCurlHttpsData ( $getAppCodeUrl, $postdata );
    }
    /**
     * Curl https Post 数据
     * 使用方法：
     * $post_string = "app=request&version=beta";
     *
     */
    public function postCurlHttpsData($url, $data)
    { // 模拟提交数据函数
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        // curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER ['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            // $this->setCurlErrorLog(curl_error ( $curl ));
            die(curl_error($curl)); //异常错误
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据
    }

    /**
     * API交互，POST方式
     * @param  [type] $api [description]
     * @param  [type] $arr [description]
     * @return [type]      [description]
     */
    public function getJsonDataUsePostMethod($action, $parameter){
        //接口URL
        $app_url  = "http://" . self::GWURL . "/openapi/";
        //apiinfo	aliexpress.open
        $api_info = "param2/" . $this->_version . "/aliexpress.open/{$action}/" . $this->_appkey . "";
        $parameter['access_token'] = $this->_access_token;

        $parameter['_aop_signature'] = $this->getApiSignature($api_info, $parameter);
        //参数
        $result = $this->postCurlHttpsData ( $app_url.$api_info,  $parameter);
        return $result;
    }

    public function getMessages()
    {
        $msgSourcesArr =array('order_msg','message_center');
        $method = 'api.queryMsgRelationList';
        $filter = 'readStat'; // 标签：未读
        //$filter = 'dealStat';  // 标签：未处理
        $pageSize = 100; //每页个数
        $j = 0;
        $message_list = [];
        foreach ($msgSourcesArr as $Sources){
            for($i=1; $i>0; $i++){
                $para = "currentPage=$i&pageSize=$pageSize&msgSources=$Sources&filter=$filter";
                $returnJson = $this->getJsonData($method,$para);
                $message_array = json_decode($returnJson, true);
//                $message_array = json_decode(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $returnJson), true); //替换特殊字符
                if(!empty($message_array['result'])){
                    foreach ($message_array['result'] as $item){
                        // 或者  跳过
                        /**
                         * 去除种状态的消息
                         * 1.最后一条消息是商家发送的
                         * 2.卖家账号为空
                         *
                         *
                         */
                        if($item['lastMessageIsOwn'] == true || empty($item['otherLoginId'])){
                            continue;
                        }
                        /**
                         * 获取信息详情
                         */
                        $detailArrJson = $this->getJsonData('api.queryMsgDetailList', "currentPage=1&pageSize=100&msgSources=$Sources&channelId=".$item['channelId']);
                        $detailArrJson = mb_convert_encoding($detailArrJson, "UTF-8","UTF-8");
                        $message_list[$j]['message_id'] = $item['lastMessageId'];
                        $message_list[$j]['list_id'] = $item['channelId'];
                        $message_list[$j]['from_name'] = addslashes($item['otherName']);
                        $message_list[$j]['from'] = $item['otherLoginId'];
                        $message_list[$j]['to'] = '客服';
                        $message_list[$j]['date'] = $this->changetime($item['messageTime']);
                        $message_list[$j]['subject'] = preg_replace("'\/\:0+([0-9]+0*)'", "<img style='width:25px' src='http://i02.i.aliimg.com/wimg/feedback/emotions/\\1.gif' />", $item['lastMessageContent']);

                        $message_list[$j]['attachment'] = ''; //附件
                        $message_list[$j]['labels'] = '' ;
                        //消息类别(product/order/member/store)不同的消息类别，typeId为相应的值，如messageType为product,typeId为productId,对应summary中有相应的附属性信，如果为product,则有产品相关的信息
                        $message_list[$j]['message_type'] = $Sources;
                        $message_list[$j]['rank'] = $item['rank'];
                        $message_list[$j]['dealStat'] = $item['dealStat'];
                        $message_list[$j]['channelId'] = $item['channelId'];
                        $message_list[$j]['unreadCount'] = $item['unreadCount'];
                        $message_list[$j]['readStat'] = $item['readStat'];
                        $message_fields_ary = false; //aliexress 平台特殊参数
                        if($Sources == 'order_msg'){
                            $message_list[$j]['label'] = '订单留言';
                            $message_list[$j]['channel_order_number'] =$item['channelId'];
                        }else{
                            $message_list[$j]['label'] = '站内信';
                            $message_list[$j]['channel_order_number'] ='';
                        }
                        $message_list[$j]['channel_message_fields'] = base64_encode(serialize($message_fields_ary));
                        $message_list[$j]['content'] = base64_encode(serialize(['aliexpress' => json_decode($detailArrJson)]));
                        $j++;
                    }
                }else{
                    break;
                }
            }
        }
        return (!empty($message_list)) ? array_reverse($message_list) : false;
    }


    /**
     * 同步分类在线产品属性2:新API，
     * 缺点：目前只获取了主要属性，不知道属性是否有子属性，需要再判断
     * @param $token_id
     * @param $category_id
     * @param string $parentAttrValueList
     * @return bool
     */
    public function getChildAttributesResultByPostCateIdAndPath($token_id, $category_id, $parentAttrValueList=''){
        if ($token_id && $category_id){
            //获取账号的信息
            $tokenInfo = AccountModel::where('id',$token_id)->get();
            if ($tokenInfo) {
                $api    = 'getChildAttributesResultByPostCateIdAndPath';
                $result = $this->getJsonData($api, 'cateId=' . $category_id.(!empty($parentAttrValueList) ? '&parentAttrValueList='.$parentAttrValueList : ''));
                $rs = json_decode($result, true);
                if (array_key_exists('success', $rs) && $rs['success']) { //返回成功了
                    //判断分类ID是否存在，不存在就插入，存在就UPdate
                    $options = array(
                        'category_id'      => $category_id,
                        'attribute'        => serialize($rs['attributes']),
                        'last_update_time' => date('Y-m-d H:i:s')
                    );

                    $category_attribute = smtCategoryAttribute::where('category_id',$category_id)->first();
                    $smtCategoryAttribute = new smtCategoryAttribute;
                    if ($category_attribute) {
                        smtCategoryAttribute::where('category_id',$category_id)->update($options);
                    } else {
                        $smtCategoryAttribute->create($options);
                    }
                    return $rs['attributes'];
                }else {
                    return false;
                }
            }else {
                return false;
            }
        }else {
            return false;
        }
    }

    /**
     * 过滤输出速卖通刊登的数据,排除Notice错误
     * @param $key
     * @param $data
     * @param $returnArray:是否返回数组
     * @return string
     */
    public function filterData($key, $data, $returnArray=false){
        return $data && array_key_exists($key, $data) ? $data[$key] : ($returnArray ? array() : '');
    }

    /**
     * 解析属性并返回 --这礼现在不解析SKU属性
     * $array:属性数组
     * @param $att:属性数值，循环后的一维数组
     * @param $pid:父属性ID
     * @param bool $isSku:是否SKU属性
     * @param array $array
     * @param array $array2
     * @return string
     */
    public function parseAttribute($att, $pid, $isSku=false, $array=array(), $array2=array()){
        $child_string      = ''; //属性值的子属性
        $customized_string = ''; //自定义属性
        $product_attribute = '';
        $required          = $att['required'] ? true : false; //必要？
        $required_string   = $att['required'] ? '<span class="red">*</span>' : '';        //必要属性
        $key_string        = $att['keyAttribute'] ? '<span class="green">！</span>' : '';  //关键属性
        $attribute_string  = ''; //属性显示方式
        $customized_flag   = ($att['customizedName'] || $att['customizedPic']) ? true : false; //自定义
        $other_string      = '';//其他属性

        $inputType         = $att['inputType']; //属性值的类型
        $units             = array_key_exists('units', $att) ? $att['units'] : array(); //单位

        switch ($att['attributeShowTypeValue']){
            case 'check_box': //复选框
                $attribute_string = '<div class="row"><div class="col-sm-6">';
                $attribute_string .= '<ul class="list-inline">';
                foreach ($att['values'] as $item):
                    $checked = false;
                    if (!empty($array)){
                        foreach ($array as $row){ //判断是否存在这个属性ID和值ID
                            if (empty($row['attrNameId']) || $att['id'] != $row['attrNameId']) continue;
                            if ($row['attrValueId'] == $item['id']) {$checked = true;break;}
                        }
                    }

                    $attribute_string .= '<li>';
                    $attribute_string .= '<label class="checkbox-inline">';
                    $attribute_string .= '<input type="checkbox" value="'.($isSku ? $item['id'] : $item['id'].'-'.$item['names']['en']).'" name="'.($isSku ? '' : 'sysAttrValueIdAndValue['.$att['id'].'][]').'" '.($checked ? 'checked' : '').'/>'.$item['names']['zh'];
                    $attribute_string .= '</label>';
                    $attribute_string .= '</li>';
                endforeach;
                $attribute_string .= '</ul>';
                $attribute_string .= '</div></div>';
                break;
            case 'list_box': //下拉列表
                $attribute_string = '<div class="row"><div class="col-sm-8">';
                $attribute_string .= '<select name="'.($isSku ? '' : 'sysAttrValueIdAndValue['.$att['id']).']" class="form-control" '.($required ? 'datatype="*"' : '').' attr_id="'.$att['id'].'">';
                $attribute_string .= '<option value="">---请选择---</option>';
                foreach ($att['values'] as $item):
                    $checked = false;
                    if (!empty($array)){
                        foreach ($array as $row){
                            if (empty($row['attrNameId']) || $att['id'] != $row['attrNameId']) continue;
                            if ($row['attrValueId'] == $item['id']) {$checked = true;break;}
                        }
                    }

                    //lang=0,说明没有子属性了
                    $attribute_string .= '<option value="'.($isSku ? $item['id'] : $item['id'].'-'.$item['names']['en']).'" lang="'.(!empty($item['attributes']) ? 0 : 1).'" attr_value_id="'.$item['id'].'" '.($checked ? 'selected="selected"' : '').'>'.$item['names']['zh'].'('.$item['names']['en'].')'.'</option>';
                    if (!empty($item['attributes'])){ //值还有子属性
                        foreach ($item['attributes'] as $i){
                            $child_string .= $this->parseAttribute($i, $item['id'], $isSku, $array);
                        }
                    }
                    $customized_string .= '<tr class="hide tr-p-'.$att['id'].'-'.$item['id'].'"><td>'.$item['names']['zh'].'</td>'.($att['customizedName'] ? '<td><input type="text" name="customizedName['.$att['id'].'_'.$item['id'].']" /></td>' : '').($att['customizedPic'] ? '<td><a href="javascript: void(0);" class="btn btn-defaut btn-xs">选择图片</a><a href="" class="view-custom-image"></a><a href="" class="del-custom-image">删除</a><input type="hidden" name="customizedPic['.$att['id'].'_'.$item['id'].']" value="" /></td>' : '').'</tr>';
                endforeach;
                $attribute_string .= '</select>';
                $attribute_string .= '</div></div>';
                if (array_key_exists($att['id'], $array2) && $array2[$att['id']]){
                    $other_string .= '<div class="form-group">';
                    $other_string .= '<div class="col-sm-10 col-sm-offset-2">';
                    $other_string .= '<input type="text" name="otherAttributeTxt['.$att['id'].']" class="form-control" value="'.$array2[$att['id']]['attrValue'].'"/>';
                    $other_string .= '</div>';
                    $other_string .= '</div></div>';
                }
                break;
            case 'group_table': //复选框 再有子复选框 --待扩展
                $attribute_string = '<div class="row"><div class="col-sm-6">';
                $attribute_string .= '<ul class="list-inline">';
                foreach ($att['values'] as $item):
                    $attribute_string .= '<li class="col-sm-4 no-padding-left groupTab">';
                    $attribute_string .= '<label class="checkbox-inline">';
                    $attribute_string .= '<input type="checkbox" value="'.($isSku ? $item['id'] : $item['id'].'-'.$item['names']['en']).'" name="'.($isSku ? '' : 'sysAttrValueIdAndValue['.$att['id'].']').'"/>'.$item['names']['zh'];
                    $attribute_string .= '</label>';
                    $attribute_string .= '</li>';
                endforeach;
                $attribute_string .= '</ul>';
                $attribute_string .= '</div></div>';
                break;
            case 'input':
            default:
                //验证信息类型及错误信息
                if ($inputType == 'NUMBER') {
                    $dataType = 'num';
                } else {
                    $dataType = '*';
                }
                //看看是否有单位
                $inputValue = $this->filterData($att['id'], $array2) ? $array2[$att['id']]['attrValue'] : '';
                if ($units) {
                    $input = '';
                    $u = '';
                    if ($inputValue){
                        list($input, $u) = explode(' ', $inputValue);
                    }

                    $attribute_string = '<div class="row"><div class="col-sm-6">';
                    $attribute_string .= '<input type="text" class="form-control" name="sysAttrIdAndValueName[' . $att['id'] . ']" ' . ($dataType ? 'datatype="' . $dataType . '" ' : ' ') . ($required ? '' : 'ignore="ignore" ') . ($dataType == 'n' ? 'errormsg="请输入数字" ' : '') . ' value="'.$input.'" />';
                    $attribute_string .= '</div>';

                    //单位处理
                    $attribute_string .= '<div class="col-sm-2">';
                    $attribute_string .= '<select name="sysAttrIdAndUnit['.$att['id'].']" class="form-control">';
                    foreach ($units as $unit){
                        $attribute_string .= '<option value="'.$unit['unitName'].'" '.($u == $unit['unitName'] ? 'selected="selected"' : '').'>'.$unit['unitName'].'</option>';
                    }
                    $attribute_string .= '</select>';
                    $attribute_string .= '</div></div>';
                } else {
                    $attribute_string = '<div class="row"><div class="col-sm-8">';
                    $attribute_string .= '<input type="text" class="form-control" name="sysAttrIdAndValueName[' . $att['id'] . ']" ' . ($dataType ? 'datatype="' . $dataType . '" ' : ' ') . ($required ? '' : 'ignore="ignore" ') . ($dataType == 'n' ? 'errormsg="请输入数字" ' : '') . ' value="' .$inputValue. '" />';
                    $attribute_string .= '</div></div>';
                }
                break;
        }
        $product_attribute .= '<div class="form-group p-'.$pid.' '.($pid > 0 && !$this->filterData($att['id'], $array) ? 'hide' : '').' '.($isSku ? 's_attr' : 'p_attr').'" attr_id="'.$att['id'].'" custome="'.(($att['customizedName'] || $att['customizedPic']) ? '1' : '0').'">';
        $product_attribute .= '<label class="col-sm-2 control-label">'.$required_string.$key_string.$att['names']['zh'].'：</label>';
        $product_attribute .= $attribute_string;
        $product_attribute .= '</div>';

        //还要添加些内容，比如自定义属性的设置
        if($customized_flag){ //自定义名称或者图片
            $product_attribute .= '<div class="form-group hide">';
            $product_attribute .= '<div class="col-sm-offset-2 col-sm-10">';
            $product_attribute .= '<table class="table table-bordered table-vcenter" id="custome-'.$att['id'].'">';
            $product_attribute .= '<thead><tr><th>'.$att['names']['zh'].'</th>'.($att['customizedName'] ? '<th>自定义名称</th>' : '').($att['customizedName'] ? '<th>图片（无图片可以不填）</th>' : '').'</tr></thead>';
            $product_attribute .= '<tbody>'.$customized_string.'</tbody>';
            $product_attribute .= '</table>';
            $product_attribute .= '</div>';
            $product_attribute .= '</div>';
        }

        return $product_attribute.$other_string.$child_string;
    }

    /**
     * 解析SKU属性 --应该都是check_box这个类型的
     * @param $att   属性数组
     * @param $array 多属性的值列表
     * @param $token_id 账号
     * @return string
     */
    function parseSkuAttribute($att, $array, $token_id){
        $child_string      = ''; //属性值的子属性
        $customized_string = ''; //自定义属性
        $product_attribute = '';
        $required          = $att['required'] ? true : false; //必要？
        $required_string   = $att['required'] ? '<span class="red">*</span>' : '';         //必要属性
        $key_string        = $att['keyAttribute'] ? '<span class="green">！</span>' : '';  //关键属性
        $customized_flag   = ($att['customizedName'] || $att['customizedPic']) ? true : false; //自定义

        $attribute_string = '<div class="row"><div class="col-sm-8">';
        $attribute_string .= '<ul class="list-inline">';
        foreach ($att['values'] as $k => $item):
            $attribute_string .= '<li>';
            $attribute_string .= '<label class="checkbox-inline">';
            $attribute_string .= '<input type="checkbox" name="'.$att['id'].'" value="'.$item['id'].'" '.(array_key_exists($item['id'], $this->filterData($att['id'], $array, true)) ?  'checked' : '').(($required && $k == 0) ? ' datatype="*" nullmsg="'.$att['names']['zh'].'不能为空"' : '').' />'.$item['names']['zh'];
            $attribute_string .= '</label>';
            $attribute_string .= '</li>';
            //自定义名称或图片
            $customized_string .= '<tr class="'.(array_key_exists($item['id'], $this->filterData($att['id'], $array, true)) ? '' : 'hide').' tr-p-'.$att['id'].'-'.$item['id'].'">'
                .'<td>'.$item['names']['zh'].'</td>'
                .($att['customizedName'] ? '<td><input type="text" name="customizedName['.$att['id'].'_'.$item['id'].']" value="'.($this->filterData($att['id'], $array, true) && $this->filterData($item['id'], $array[$att['id']]) ? $array[$att['id']][$item['id']]['propertyValueDefinitionName'] : '').'" /></td>' : '')
                .'<td><span class="customize-pic pull-right">'
                .(($this->filterData($att['id'], $array) && $this->filterData($item['id'], $array[$att['id']]) && $array[$att['id']][$item['id']]['skuImage']) ? '<img src="'.$array[$att['id']][$item['id']]['skuImage'].'" width="30" height="30" /><a href="javascript: void(0);" class="del-custom-image">删除</a>' : '').'</span>'
                .'<a href="javascript: void(0);" class="btn btn-default btn-xs add-custom-image pull-left" lang="'.$att['id'].'_'.$item['id'].'">选择图片</a>'
                .'<a href="javascript: void(0);" class="btn btn-default btn-xs copyToCust" onclick="copyToHere(this, \'pic-detail\');" >详情图片</a>'
                .'<input type="hidden" class="customized-pic-input customizedPic-'.$att['id'].'_'.$item['id'].'" name="customizedPic['.$att['id'].'_'.$item['id'].']" value="'.(($this->filterData($att['id'], $array) && $this->filterData($item['id'], $array[$att['id']]) && $array[$att['id']][$item['id']]['skuImage']) ? $array[$att['id']][$item['id']]['skuImage'] : '').'" /></td>'
                .'</tr>';
        endforeach;
        $attribute_string .= '</ul>';
        $attribute_string .= '</div></div>';

        $product_attribute .= '<div class="form-group  s_attr" attr_id="'.$att['id'].'" custome="'.(($att['customizedName'] || $att['customizedPic']) ? '1' : '0').'">';
        $product_attribute .= '<label class="col-sm-2 control-label">'.$required_string.$key_string.$att['names']['zh'].'：</label>';
        $product_attribute .= $attribute_string;
        $product_attribute .= '</div>';

        //还要添加些内容，比如自定义属性的设置
        if($customized_flag){ //自定义名称或者图片
            $product_attribute .= '<div class="form-group '.($this->filterData($att['id'], $array) ? '' : 'hide').'">';
            $product_attribute .= '<div class="col-sm-offset-2 col-sm-10">';
            $product_attribute .= '<table class="table table-bordered table-vcenter" id="custome-'.$att['id'].'">';
            $product_attribute .= '<thead><tr><th>'.$att['names']['zh'].'</th>'.($att['customizedName'] ? '<th>自定义名称</th>' : '').($att['customizedName'] ? '<th>图片（无图片可以不填）</th>' : '').'</tr></thead>';
            $product_attribute .= '<tbody>'.$customized_string.'</tbody>';
            $product_attribute .= '</table>';
            $product_attribute .= '</div>';
            $product_attribute .= '</div>';
        }

        return $product_attribute.$child_string;
    }

    function accounterFormat($string) {
        $string = trim($string);
        $newString = $string;
        if ($string == '速卖通' || $string == '线下交易' || $string == '网站' || $string == 'ebay补货' || strlen($string) <= 4 || substr_count($string, '@') > 0) {
            //
        } else {
            $newString = substr($string, 0, 4) . '****' . substr($string, strlen($string) - 1, 1);
            if ( $string == 'happy-store2013' )  $newString = 'h***st***3';
            if($string=='happyfish2012')$newString='happ**fish**2';
            if($string=='happycow2012')$newString='happ**cow**2';
            if($string=='happywill2013')$newString='happ**will**3';
            if($string=='pandamotos2012')$newString='pand**tos**2';
            if($string=='pandacars2012')$newString='pand**car**2';

        }
        return $newString;
    }

    /**
     * SKU列表显示排序
     * @param $skus
     * @param $sortAtt
     * @param $attVal
     * @return array
     */
    function sortSkuAttr($skus, $sortAtt, $attVal){
        $newSkus = array();
        $sKarr = array(); //所有键值
        foreach ($skus as $sku){
            $aeopSKUProperty = unserialize($sku['aeopSKUProperty']);
            $trClassArr = array();
            foreach ($sortAtt as $k1 => $sa){
                $matchFlag = false;
                $saVal = '';
                if($aeopSKUProperty){
                    foreach ($aeopSKUProperty as $Property){
                        if ($sa == $Property['skuPropertyId']){ //分类信息
                            $saVal = $Property['propertyValueId'];

                            foreach ($attVal[$sa] as $k2 => $av){
                                if ($av == $saVal){
                                    $sKarr[$k1][$k2] = $saVal;
                                    break;
                                }
                            }
                            $matchFlag = true;
                            break;
                        }
                    }
                    $trClassArr[] = $saVal;

                    if (!$matchFlag) $sKarr[$k1] = array();
                    ksort($sKarr[$k1]);
                }
            }
            $skus[implode('_', $trClassArr)] = $sku;
        }

        $newTrSortArr = $this->combineDika($sKarr); //行显示的排序数组

        foreach ($newTrSortArr as $ns){
            if (array_key_exists($ns, $skus)){
                $newSkus[] = $skus[$ns];
            }
        }
        return $newSkus; //返回排序后的新的SKU列表
    }

    /**
     * 对SMTSKU进行去前后缀处理
     * @param $smtSkuCode
     * @param $erpFlag:是否解析成ERPSKU
     * @return string
     */
    public function rebuildSmtSku($smtSkuCode, $erpFlag=false){
        // 去掉SKU的销售代码
        $n = strpos($smtSkuCode, '*');
        $sku_new = $n !== false ? substr($smtSkuCode, $n+1) : $smtSkuCode;

        // 去除sku的帐户代码
        $m = strpos($sku_new, '#');
        $sku_new = $m !== false ? substr($sku_new, 0, $m) : $sku_new;
        if ($erpFlag) {
            $sku_new = str_ireplace('{YY}', '', $sku_new);
        }
        return trim($sku_new);
    }

    /**
     * 多个数组的笛卡儿积
     * @param $data
     * @return array
     */
    public function combineDika($data)
    {
        $cnt    = count($data);
        $result = array();
        if ($cnt == 0) return $result;
        $result = $data[0];
        for ($i = 1; $i < $cnt; $i++) {
            $result = $this->combineArray($result, $data[$i]);
        }
        return $result;
    }

    /**
     * 两个数组的笛卡尔积
     * @param $arr1
     * @param $arr2
     * @return array
     */
    public function combineArray($arr1,$arr2) {
        $result = array();
        if (!empty($arr1)) {
            foreach ($arr1 as $item1) {
                if (!empty($arr2)) {
                    foreach ($arr2 as $item2) {
                        $result[] = $item1 . '_' . $item2;
                    }
                } else {
                    $result[] = $item1 . '_';
                }
            }
        } else {
            if (!empty($arr2)) {
                foreach ($arr2 as $item2) {
                    $result[] = '_' . $item2;
                }
            } else {
                $result[] = '_';
            }
        }

        return $result;
    }

    /**
     * 把SMT的module替换成图片，这样才能显示成图片的占位符
     * @param $detail
     * @return mixed
     */
    public function replaceSmtModuleToImg($detail){
        preg_match_all('/<kse:widget.*><\/kse:widget>/i', $detail, $matches);
        if (!empty($matches[0])) {
            foreach ($matches[0] as $m) {
                $pic    = '<img class="kse-widget" data="' . rawurlencode($m) . '" src="http://style.aliexpress.com/js/5v/lib/kseditor/plugins/widget/images/widget1.png"/>';
                $detail = str_replace($m, $pic, $detail);
                unset($pic);
            }
        }
        return $detail;
    }
    /**
     * 替换SMT描述的特殊图片成module:   ske:widget
     * @param $str
     * @return mixed
     */
    public function replaceSmtImgToModule($str){
        $detail = $str;
        preg_match_all('/<img\s*[^>]*class=\s*\"\s*kse-widget\s*\"[^>]*\/>/i', $str, $matches);
        if (!empty($matches[0])) {
            foreach ($matches[0] as $match) {
                //匹配出属性的值
                preg_match('/data\s*=\s*"([^>^"]*)"/i', $match, $data);
                $widget = rawurldecode($data[1]);
                $detail = str_replace($match, $widget, $detail);
                unset($data);
            }
            unset($matches);
        }
        return $detail;
    }
    public function sendMessages($replyMessage)
    {

        // TODO: Implement sendMessages() method.
        $message_obj = $replyMessage->message;
        if(!empty($message_obj)){
            // step1:发信息
            $send_param = [];
            $channelId = rawurlencode($message_obj->list_id);
            $buyerId = rawurlencode($message_obj->from);
            if($message_obj->label == '订单留言'){
                $msgSources = rawurlencode('order_msg');
            }else{
                $msgSources = rawurlencode('message_center');
            }
            $content = rawurlencode($replyMessage->content);
            $imgPath = rawurlencode($replyMessage->smt_return_img);
            $send_param ="channelId=$channelId&buyerId=$buyerId&msgSources=$msgSources&content=$content&imgPath=$imgPath";

            $api_return =  $this->getJsonData('api.addMsg', $send_param);
            $api_return_array = json_decode($api_return,true);
            if(isset($api_return_array['result']["isSuccess"])){
                if($api_return_array['result']["isSuccess"]){
                    //step2: 更新消息为已读
                    $update_param = [];
                    $update_param['channelId']  = $message_obj->list_id;
                    $update_param['msgSources'] = $msgSources;

                    $this->getJsonData('api.updateMsgRead',http_build_query($update_param));
                    $replyMessage->status = 'SENT';
                }else{
                    $replyMessage->status = 'FAIL';
                }
            }
        }else{
            $replyMessage->status = 'FAIL';
        }
        $replyMessage->save();
        return $replyMessage->status == 'SENT' ? true : false;
    }

    /**
     * 速卖通上传图片到图片银行或临时目录专用
     * @param  [type] $action     api名称
     * @param  [type] $fileName   若为数组格式array('srcFileName' => $filename),字符串格式则为srcFileName=$filename
     * @param  [type] $fileStream 图片流,二进制文件
     * @return [type]             [description]
     */
    /*  public function uploadBankImage($action, $file, $fileName=''){
         //接口URL
         $app_url  = 'http://'.self::GWURL.'/fileapi/param2/'.$this->_version.'/aliexpress.open/'.$action.'/'.$this->_appkey.'?access_token='.$this->_access_token;
         $param    = '';

         $parameter_arr['_access_token'] = $this->_access_token;
         $_aop_signature = $this->getApiSignature($app_url,$parameter_arr);
         $fileName = ($fileName ? $fileName : time()).'.jpg';
         if ($action == 'api.uploadImage') {
             $param = '&fileName='.$fileName.'&_aop_signature='.$_aop_signature;
         }elseif ($action == 'api.uploadTempImage') {
             $param = '&srcFileName='.$fileName;
         }
         $data = file_get_contents($file);
         $ch   = curl_init ();
         curl_setopt($ch, CURLOPT_URL, $app_url.$param);
         curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-from-urlencoded'));
         curl_setopt($ch, CURLOPT_POST, 1);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($ch, CURLOPT_TIMEOUT, 100);
         $result = curl_exec($ch);
         if (curl_error($ch)) {
             //$this->setCurlErrorLog(curl_error ( $ch ));
             die(curl_error ( $ch )); //异常错误
         }
         curl_close($ch);
         //$this->setCurlErrorLog($result);
         return json_decode($result, true);
     } */

    public function updateProductPublishState($action,$productId){
        $app_url  = "http://" . self::GWURL . "/openapi/";
        $api_info = "param2/" . $this->_version . "/aliexpress.open/{$action}/" . $this->_appkey;
        $parameter['access_token'] = $this->_access_token;
        $parameter['productIds'] = $productId;
        $parameter['_aop_signature'] = $this->getApiSignature($api_info, $parameter);


        //参数
        $result = $this->postCurlHttpsData ( $app_url.$api_info,  $parameter);

        return json_decode($result,true);
    }

    public function uploadBankImage($action, $file, $fileName=''){
        //接口URL
        $app_url = "http://" . self::GWURL . "/fileapi/";
        //apiinfo	aliexpress.open
        $apiInfo = "param2/" . $this->_version . "/aliexpress.open/{$action}/" . $this->_appkey;
        $parameter= '';
        $fileName = ($fileName ? rawurlencode($fileName) : time().rand(1000, 9999)).'.jpg';
        if ($action == 'api.uploadImage') {
            $param = 'fileName='.$fileName;
        }elseif ($action == 'api.uploadTempImage') {
            $param = 'srcFileName='.$fileName;
        }
        //参数
        $app_parameter_url = $param."&access_token=" . $this->_access_token;

        $sign_url = '';

        //获取对应URL的签名
        $sign     = $this->getApiSign ( $apiInfo, $app_parameter_url );
        $sign_url = "&_aop_signature=$sign"; //签名参数

        //组装URL
        $get_url = $app_url . $apiInfo . '?' . $app_parameter_url . $sign_url;

        $data = file_get_contents($file);
        $ch   = curl_init ();
        curl_setopt($ch, CURLOPT_URL, $get_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-from-urlencoded'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        $result = curl_exec($ch);
        if (curl_error($ch)) {
            $this->setCurlErrorLog(curl_error ( $ch ));
            die(curl_error ( $ch )); //异常错误
        }
        curl_close($ch);
        return json_decode($result, true);
    }

    /**
     * 获取定义的平台信息
     * @param string $platType
     * @return array
     */
    public function getDefinedPlatInfo($platType = 'SMT'){
        $platArray = $this->defineProductPublishPlatArray();
        foreach ($platArray as $plat){
            if (strtoupper($plat['platType']) == strtoupper($platType)){
                return $plat;
            }
        }
        return array();
    }

    public function defineProductPublishPlatArray( )
    {
        $array = array(
            array(
                'platID' => '101',
                'platTitle' => 'eBay.us',
                'platType' => 'USD',
                'platTypeID' => '1'
            ),
            array(
                'platID' => '102',
                'platTitle' => 'eBay.au',
                'platType' => 'AUD',
                'platTypeID' => '1'
            ),
            array(
                'platID' => '103',
                'platTitle' => 'eBay.uk',
                'platType' => 'GBP',
                'platTypeID' => '1'
            ),
            array(
                'platID' => '104',
                'platTitle' => 'eBay.de',
                'platType' => 'EUR',
                'platTypeID' => '1'
            ),
            array(
                'platID' => '105',
                'platTitle' => 'eBay.fr',
                'platType' => 'EUR',
                'platTypeID' => '1'
            ),
            array(
                'platID' => '106',
                'platTitle' => 'eBay.ca',
                'platType' => 'C',
                'platTypeID' => '1'
            ),
            array(
                'platID' => '199',
                'platTitle' => 'eBay.other',
                'platType' => 'ebay.other',
                'platTypeID' => '1'
            ),
            array(
                'platID' => '201',
                'platTitle' => 'Amazon.de',
                'platType' => 'Amazon.de',
                'platTypeID' => '3'
            ),
            array(
                'platID' => '202',
                'platTitle' => 'Amazon.uk',
                'platType' => 'Amazon.uk',
                'platTypeID' => '3'
            ),
            array(
                'platID' => '203',
                'platTitle' => 'Amazon.us',
                'platType' => 'Amazon.us',
                'platTypeID' => '3'
            ),
            array(
                'platID' => '204',
                'platTitle' => 'Amazon.ca',
                'platType' => 'Amazon.ca',
                'platTypeID' => '3'
            ),
            array(
                'platID' => '205',
                'platTitle' => 'Amazon.fr',
                'platType' => 'Amazon.fr',
                'platTypeID' => '3'
            ),
            array(
                'platID' => '299',
                'platTitle' => 'Amazon.other',
                'platType' => 'Amazon.other',
                'platTypeID' => '3'
            ),
            array(
                'platID' => '301',
                'platTitle' => 'Aliexpress',
                'platType' => 'SMT',
                'platTypeID' => '6'
            ),
            array(
                'platID' => '401',
                'platTitle' => 'DHgate',
                'platType' => 'DHgate',
                'platTypeID' => '4'
            ),
            array(
                'platID' => '501',
                'platTitle' => '网站',
                'platType' => 'B2C',
                'platTypeID' => '5'
            )
        );
        return $array;
    }

    /**
     * 对速卖通的SKU属性进行排序处理 --基本属性不进行处理
     * @param $attribute
     * @return mixed
     */
    public function sortAttribute($attribute){
        if ($attribute) {
            $spec = array();
            $temp = array();
            foreach ($attribute as $key => $row) {
                if ($row['sku']){ //是SKU属性
                    $spec[$key] = $row['spec']; //用来排序的数组
                    $temp[$key] = $row;
                    unset($attribute[$key]);
                }
            }
            array_multisort($spec, SORT_ASC, $temp); //对SKU属性数组进行排序
            $attribute = array_merge($attribute, $temp); //合并排序后的信息
        }
        return $attribute;
    }


    /**
     * 解析SKU成ERP内的SKU
     * @param $skuCode
     * @param $erpFlag:解析成ERP SKu(主要是去掉海外仓的标识)
     * @return array
     */
    public function buildSysSku($skuCode, $erpFlag=false){
        // 处理带销售代码的SKU：B702B#Y6 及海外仓标识
        $skus = $this->rebuildSmtSku($skuCode, $erpFlag);

        $sku_list = explode('+', $skus); // 处理组合的SKU：DA0090+DA0170+DA0137
        $sku_arr  = array();
        foreach ($sku_list as $value) {
            $len       = strpos($value, '('); // 处理有捆绑的SKU：MHM330(12)
            $sku_new   = $len ? substr($value, 0, $len) : $value;
            $sku_arr[] = $sku_new;
        }
        return !empty($sku_arr) ? $sku_arr : array();
    }

    /**
     * 获取SKU属性中的海外仓发货属性ID,没有就返回0
     * @param $aeopSKUProperty
     * @return int
     */
    public function checkProductSkuAttrIsOverSea($aeopSKUProperty){
        $valId = 0;
        if (!empty($aeopSKUProperty)){

            foreach ($aeopSKUProperty as $property){
                if ($property['skuPropertyId'] == 200007763){ //发货地的属性ID
                    $valId = $property['propertyValueId'];
                    break;
                }
            }
        }
        return $valId;
    }

    /**
     * 标点过滤函数 --主要是针对关键字
     * @param unknown $str
     * @return unknown
     */
    function filterForSmtProduct($str){
        $str = str_replace(';', ' ', $str);
        $str = str_replace(',', ' ', $str);
        return trim($str);
    }

    /**
     * 获取在线数据
     * @param $currentPage 每页查询商品数量
     * @param $pageSize 需要商品的当前页数
     */
    public function getOnlineProduct($productStatus = 'onSelling',$currentPage,$pageSize,$grounpId=''){
        $app_url  = "http://" . self::GWURL . "/openapi/";
        $api_info = "param2/" . $this->_version . "/aliexpress.open/api.findProductInfoListQuery/" . $this->_appkey;
        $parameter['access_token'] = $this->_access_token;
        $parameter['pageSize'] = $pageSize;
        $parameter['currentPage'] = $currentPage;
        $parameter['productStatusType'] = $productStatus;
        if($grounpId){
            $parameter['groupId'] = $grounpId;
        }
        $parameter['_aop_signature'] = $this->getApiSignature($api_info, $parameter);

        $result = $this->postCurlHttpsData ( $app_url.$api_info,  $parameter);
        return json_decode($result,true);
    }


    /**
     * 根据商品id查询单个商品的详细信息
     * @param $productId
     */
    public function findAeProductById($productId){
        $app_url  = "http://" . self::GWURL . "/openapi/";
        $api_info = "param2/" . $this->_version . "/aliexpress.open/api.findAeProductById/" . $this->_appkey;
        $parameter['access_token'] = $this->_access_token;
        $parameter['productId'] = $productId;
        $parameter['_aop_signature'] = $this->getApiSignature($api_info, $parameter);
        $result = $this->postCurlHttpsData ( $app_url.$api_info,  $parameter);
        return json_decode($result,true);
    }

    /**
     * 编辑商品单个SKU库存
     * @param  $productId  商品ID
     * @param  $skuStock   库存
     */
    public function editSingleSkuStock($data){
        $app_url  = "http://" . self::GWURL . "/openapi/";
        $api_info = "param2/" . $this->_version . "/aliexpress.open/api.editSingleSkuStock/" . $this->_appkey;
        $parameter['access_token'] = $this->_access_token;
        $parameter['productId'] = $data['productId'];
        $parameter['ipmSkuStock'] = $data['ipmSkuStock'];
        $parameter['skuId'] = $data['skuId'];
        $parameter['_aop_signature'] = $this->getApiSignature($api_info, $parameter);
        $result = $this->postCurlHttpsData ( $app_url.$api_info,  $parameter);
        return json_decode($result,true);
    }

    public function editSingleSkuPrice($data){
        $app_url  = "http://" . self::GWURL . "/openapi/";
        $api_info = "param2/" . $this->_version . "/aliexpress.open/api.editSingleSkuPrice/" . $this->_appkey;
        $parameter['access_token'] = $this->_access_token;
        $parameter['productId'] = $data['productId'];
        $parameter['skuPrice'] = $data['skuPrice'];
        $parameter['skuId'] = $data['skuId'];
        $parameter['_aop_signature'] = $this->getApiSignature($api_info, $parameter);
        $result = $this->postCurlHttpsData ( $app_url.$api_info,  $parameter);
        return json_decode($result,true);
    }


    /**
     * 纠纷
     */
    public function getIssues(){
        $issueAry = [];
        $issue_ary = array(
            'WAIT_SELLER_CONFIRM_REFUND',  //买家提起纠纷
            // 'SELLER_REFUSE_REFUND', //卖家拒绝纠
            // 'ARBITRATING', // 仲裁中
            // 'ACCEPTISSUE', //卖家接受纠纷     相当于完成了的纠纷
            // 'WAIT_BUYER_SEND_GOODS', //等待买家发货
            //  'WAIT_SELLER_RECEIVE_GOODS', // 买家发货，等待卖家收货
            //   'SELLER_RESPONSE_ISSUE_TIMEOUT' // 卖家响应纠纷超时  对应相关超时的不需要获取
        );
        $page = 1;
        $page_size = 10;
        foreach ($issue_ary as $issue){
            for($i = 1 ; $i>0; $i++){
                $method = 'api.queryIssueList';
                $para = "currentPage=$page&pageSize=$page_size&issueStatus=".$issue;
                $issue_list = json_decode($this->getJsonData($method, $para));
                if(isset($issue_list->success)) {
                    foreach ($issue_list->dataList as $key => $item) {
                        $detail_param = "issueId=".$item->id;
                        $return_detail = json_decode($this->getJsonData('alibaba.ae.issue.findIssueDetailByIssueId',$detail_param));
                        if(isset($return_detail->success)){
                            $issue_detail = $return_detail;
                        }else{
                            $issue_detail = '';
                        }
                        $issueAry[] = [
                            'issue_id'      => $item->id,
                            'gmtModified'   => $item->gmtModified,
                            'issueStatus'   => $item->issueStatus,
                            'gmtCreate'     => $item->gmtCreate,
                            'reasonChinese' => $item->reasonChinese,
                            'orderId'       => $item->orderId,
                            'reasonEnglish' => $item->reasonEnglish,
                            'issue_detail'  => $issue_detail,
                            'issueType'     => $issue,
                        ];
                    }
                }else{
                    break;
                }
            }
        }
        return $issueAry;
    }
    public function changetime($time){
        $time = date('Y-m-d H:i:s', substr($time, 0, 10));
        return $time;
    }
    /**
     * 过滤速卖通产品信息模块
     * @param $str 产品详情信息
     * @return mixed
     */
    function filterSmtRelationProduct($str){
        preg_match_all('/<kse:widget.*><\/kse:widget>/i', $str, $matches);
        if (!empty($matches[0])){
            foreach($matches[0] as $widget){
                $str = str_replace($widget, '', $str);
            }
        }
        return $str;
    }

    /**
     * @param $paramAry
     * compact('orderId','buyId','comments')
     */
    public function addMessageNew($paramAry){
        $paramAry['orderId'] = rawurlencode($paramAry['orderId']);
        $paramAry['buyId'] = rawurlencode($paramAry['buyId']);
        $paramAry['comments'] = rawurlencode($paramAry['comments']);
        // $order_detail_ary = json_decode($this->getJsonData('api.findOrderById',"orderId=".$paramAry['orderId']),true);
        $query ="channelId={$paramAry['orderId']}&buyerId={$paramAry['buyId']}&msgSources=order_msg&content={$paramAry['comments']}";
        $respon_ary = json_decode($this->getJsonData('api.addMsg',$query));
        return $respon_ary['result']['isSuccess'] ? true : false;
    }
    public function issuesRedfuse(){
        // $respon_ary = json_decode($this->getJsonData('api.addMsg','api.sellerRefuseIssue'));
    }

    //根据交易订单获取线上发货物流服务列表
    public function getOnlineLogisticsServiceListByOrderId($orderId){
        $query = 'orderId='.$orderId;
        $api = "api.getOnlineLogisticsServiceListByOrderId";
        $result = json_decode($this->getJsonData($api,$query),true);
        return $result;
    }
}