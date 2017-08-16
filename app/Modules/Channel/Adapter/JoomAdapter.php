<?php
/*Time:2016-09-30 15:18:00
 *Joom channel data
 *@param $config
 *@user  hejiancheng
 */

namespace App\Modules\Channel\Adapter;
header("Content-type:text/html;charset=utf-8");
use App\Models\Order\ItemModel;

use App\Models\Publish\Wish\WishSellerCodeModel;

use Illuminate\Support\Facades\DB;

set_time_limit(1800);

Class JoomAdapter implements AdapterInterface
{

    private $publish_code;
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $refresh_token;
    private $access_token;
    private $expiry_time;
    private $proxy_address;


    public function __construct($config)
    {
        $this->publish_code = $config["publish_code"];
        $this->client_id = $config["client_id"];
        $this->client_secret = $config["client_secret"];
        $this->redirect_uri = $config["redirect_uri"];
        $this->refresh_token = $config["refresh_token"];
        $this->expiry_time = $config['expiry_time'];
        $this->proxy_address = $config['proxy_address'];
        $this->joom_sku_resolve = $config['sku_resolve'];
        $access_token = $this->isResetAccesstoken();   //是否过期的access_token
        $this->access_token = $access_token == false ? $config["access_token"] : $access_token;


    }

    public function getOrder($orderID)
    {
        return $orderID;
    }

    /** 获取joom订单
     * @param $startDate
     * @param $endDate
     * @param array $status
     * @param int $perPage
     * @param string $nextToken
     * @return array
     */
    public function listOrders($startDate, $endDate, $status = [],  $nextToken = '',$perPage = 200)
    {
        $orders = [];
        $returnOrders = [];
        if (empty($nextToken)) {
            $nextToken = 0;
        }
        $url = "https://api-merchant.joom.it/api/v2/order/get-fulfill?";
        $apiArr = array();//api请求数组
        $apiArr['limit'] = urlencode($perPage);
        $apiArr['start'] = urlencode($nextToken * $perPage);
        if ($startDate != '') {
            $apiArr['since'] = urlencode(date("Y-m-d", strtotime($startDate)));
        }
        $apiArr['access_token'] = urldecode($this->access_token);
        $apiString = http_build_query($apiArr);
        $url = $url . $apiString;
        $orderjson = $this->getCurlData($url);
        $orderList = json_decode($orderjson, true);
        if (isset($orderList['code']) && ($orderList['code'] == 0) && !empty($orderList['data'])) {
            foreach ($orderList['data'] as $order) {
                $orders[$order['Order']['transaction_id']][] = $order;
            }
            $nextToken++;
        } else {    //if empty break
            print_r($orderList);
            $nextToken='';
            return '';
        }
        foreach ($orders as $key => $order) {
            $midOrder = $this->parseOrder($order, $key);
            if ($midOrder) {
                $returnOrders[] = $midOrder;
            }
        }
        return ['orders' => $returnOrders, 'nextToken' => $nextToken];
    }
    /**回传追踪信息
     * @param $tracking_info
     * @return array
     */
    public function returnTrack($tracking_info)
    {
        $return = [];
        $tracking_info['access_token'] = urldecode($this->access_token);
        array_filter($tracking_info);
        $url =$tracking_info['api'];
        unset($tracking_info['api']);
        $resultJson = $this->postCurlHttpsData($url, $tracking_info);
        $result = json_decode($resultJson, true);
        if (isset($result['code']) && ($result['code'] == 0)) {
            $return['status'] = true;
            $return['info'] = isset($result['message']) ? $result['message'] : 'Success';

        } else {
            $return['status'] = false;
            $return['info'] = isset($result['message']) ? $result['message'] : '未知错误';
        }
        return $return;


    }
    /**组装订单信息
     * @param $order
     * @param $transaction_number
     * @return array|bool
     */
    public function parseOrder($order, $transaction_number)
    {
        $orderInfo = array();
        $amount = 0; //先设置 总金额为0
        $amount_shipping = 0; //先设置运费为0
        $channel_ordernum = array(); //渠道号数组
        $items = array();//SKU 信息
        foreach ($order as $key => $orderSingle) {
            $orderSingle = $orderSingle['Order'];
            //ItemModel 先判断这个order_id 是否存在
            $thisOrder = ItemModel::where('channel_order_id', $orderSingle['order_id'])->first();     //判断一下这订单 是否已经插入过
            if ($thisOrder) {    //joom exist
                echo $orderSingle['order_id'] . ' 存在<br/>';
                continue;
            }
            $orderInfo["email"] = ''; //joom API 不返回
            $orderInfo['currency'] = 'USD';
            $orderInfo['payment'] = 'moonarstore@gmail.com';
            $orderInfo['shipping'] = isset($orderSingle['shipping_provider']) ? $orderSingle['shipping_provider'] : '';
            $orderInfo['shipping_firstname'] = isset($orderSingle['ShippingDetail']['name']) ? $orderSingle['ShippingDetail']['name'] : ''; //只有一个名字字段
            $orderInfo['shipping_lastname'] = '';
            $orderInfo['shipping_address'] = isset($orderSingle['ShippingDetail']['street_address1']) ? $orderSingle['ShippingDetail']['street_address1'] : '';
            $orderInfo['shipping_address1'] = '';
            $orderInfo['shipping_city'] = isset($orderSingle['ShippingDetail']['city']) ? $orderSingle['ShippingDetail']['city'] : '';
            $orderInfo['shipping_state'] = isset($orderSingle['ShippingDetail']['state']) ? $orderSingle['ShippingDetail']['state'] : '';
            $orderInfo['shipping_country'] = isset($orderSingle['ShippingDetail']['country']) ? $orderSingle['ShippingDetail']['country'] : '';
            $orderInfo['shipping_zipcode'] = isset($orderSingle['ShippingDetail']['zipcode']) ? $orderSingle['ShippingDetail']['zipcode'] : '';
            $orderInfo['shipping_phone'] = isset($orderSingle['ShippingDetail']['phone_number']) ? $orderSingle['ShippingDetail']['phone_number'] : '';
            $orderInfo['payment_date'] = $this->getPayTime($orderSingle['order_time']);
            $orderInfo['status'] = 'PAID';
            //处理一下 SKU的前后缀问题
            $erpSku = $this->filter_sku($orderSingle['sku'], $this->joom_sku_resolve); //根据账号的sku解析设定
            $allSkuNum = $erpSku['skuNum'];
            unset($erpSku['skuNum']);
            foreach ($erpSku as $sku) {
                $skuArray = [];
                $skuArray['channel_sku'] = $orderSingle['sku'];
                $skuArray['sku'] = $sku['erpSku'];
                $skuArray['currency'] = 'USD';
                $skuArray['price'] = $orderSingle['price'] / $allSkuNum;
                $skuArray['quantity'] = $orderSingle['quantity'] * $sku['qty'];
                $skuArray['orders_item_number'] = $orderSingle['product_id'];
                $skuArray['channel_order_id'] = $orderSingle['order_id'];
                $items[] = $skuArray;
            }
            $channel_ordernum[] = $orderSingle['order_id'];
            $amount = $amount + (int)$orderSingle['quantity'] * (float)$orderSingle['price'];
            $amount_shipping = $amount_shipping + (int)$orderSingle['quantity'] * (float)$orderSingle['shipping'];
        }
        if (!empty($items)) {
            $orderInfo['amount'] = $amount + $amount_shipping; //joom的总金额分两部分  要把运费加上去
            $orderInfo['amount_shipping'] = $amount_shipping;
            $orderInfo['channel_ordernum'] = join('+', $channel_ordernum);
            $orderInfo['items'] = $items;
            $orderInfo['transaction_number'] = $transaction_number;
        } else {
            return false;
        }
        return $orderInfo;

    }
    /**SKU 解析
     * @param $channel_sku
     * @param int $type
     * @return array
     */
    public function filter_sku($channel_sku, $type = 1)
    {

        $tmpSku = explode('+', $channel_sku);
        $skuNum = 0;
        $returnSku = array();
        foreach ($tmpSku as $k => $sku) {

            if (stripos($sku, '[') !== false) {
                $sku = preg_replace('/\[.*\]/', '', $sku);
            }
            if ($type == 2) {

                $prePart = substr($sku, 0, 1);
                $suffPart = substr($sku, 4);
                $sku = $prePart . $suffPart;
                $newSku = $sku;
            } else {

                $tmpErpSku = explode('*', $sku);
                $i = count($tmpErpSku) - 1;
                $newSku = $tmpErpSku[$i];
            }


            $qty = 1;
            if (strpos($newSku, '(') !== false) {
                $matches = array();
                preg_match_all("/(.*?)\([a-z]?([0-9]*)\)?/i", $newSku, $matches);
                $newSku = trim($matches[1][0]);
                $qty = trim($matches[2][0]) ? trim($matches[2][0]) : 1;
            }
            $skuArray = array();
            $skuArray['erpSku'] = $newSku;
            $skuArray['qty'] = $qty;

            $skuNum = $skuNum + $qty;
            $returnSku[] = $skuArray;
        }


        $returnSku['skuNum'] = $skuNum;

        return $returnSku;

    }
    /**付款时间转换
     * @param $time
     * @return bool|string
     */
    public function getPayTime($time)
    {
        return date('Y-m-d H:i:s', strtotime($time) + 8 * 3600);
    }
    /** get Joom online data
     * @param $start
     * @param int $perPage
     * @param string $startDate
     * @return array|bool
     */
    public function getOnlineProduct($start, $perPage = 200, $startDate = '')
    {
        $return = [];
        $WishSellerCode = new WishSellerCodeModel();
        $sellerIdInfo = $WishSellerCode->getAllWishCode();
        $url = "https://api-merchant.joom.it/api/v2/product/multi-get?";
        $apiArr = array();
        $apiArr['limit'] = urlencode($perPage);
        $apiArr['start'] = urlencode($start * $perPage);
        if ($startDate != '') {
            $apiArr['since'] = '2014-10-15';
        }
        $apiArr['access_token'] = urldecode($this->access_token);
        $apiString = http_build_query($apiArr);  //http_build_query + url
        $url = $url . $apiString;
        $productjson = $this->getCurlData($url);
        $productList = json_decode($productjson, true);
        if (isset($productList['code']) && ($productList['code'] == 0) && !empty($productList['data'])) {  //if success
            foreach ($productList['data'] as $num => $product) {
                $productInfo = [];
                if ($product['Product']['is_promoted'] = 'True') {
                    $is_promoted = 1;
                } else {
                    $is_promoted = 0;
                }
                $productInfo['is_promoted'] = $is_promoted;
                $productInfo['product_name'] = $product['Product']['name'];
                $tagInfo = [];
                foreach ($product['Product']['tags'] as $tags) {
                    $tagInfo[] = $tags['Tag']['name'];
                }
                $productInfo['tags'] = implode(',', $tagInfo);
                $productInfo['review_status'] = isset($product['Product']['review_status']) ? $product['Product']['review_status'] : '';
                $productInfo['extra_images'] = isset($product['Product']['extra_images']) ? $product['Product']['extra_images'] : '';
                $productInfo['number_saves'] = isset($product['Product']['number_saves']) ? $product['Product']['number_saves'] : '';
                $productInfo['number_sold'] = isset($product['Product']['number_sold']) ? $product['Product']['number_sold'] : '';
                $productInfo['parent_sku'] = isset($product['Product']['parent_sku']) ? $product['Product']['parent_sku'] : '';
                $productInfo['productID'] = $product['Product']['id'];
                $productInfo['product_type_status'] = 2;

                if (isset($product['Product']['date_uploaded'])) {
                    $publishedTime = $product['Product']['date_uploaded'];
                    $publishedTime = explode('-', $publishedTime);
                    $publishedTime = $publishedTime[2] . '-' . $publishedTime[0] . '-' . $publishedTime[1];
                    $productInfo['publishedTime'] = date('Y-m-d H:i:s', strtotime($publishedTime));
                }
                $productInfo['product_description'] = $product['Product']['description'];

                $variants = [];
                $i = 1;
                $j = 1;
                foreach ($product['Product']['variants'] as $key => $variant) {
                    $variants[$key]['sku'] = $variant['Variant']['sku'];
                    $variants[$key]['msrp'] = isset($variant['Variant']['msrp']) ?$variant['Variant']['msrp'] : 0;
                    $variants[$key]['inventory'] = $variant['Variant']['inventory'];
                    $variants[$key]['main_image'] = isset($variant['Variant']['main_image']) ? $variant['Variant']['main_image'] : "";
                    $variants[$key]['price'] = $variant['Variant']['price'];
                    $variants[$key]['shipping_time'] = $variant['Variant']['shipping_time'];

                    if ($variant['Variant']['enabled'] == 'True') {
                        $enabled = 1;
                    } else {
                        $enabled = 0;
                        $i++;
                    }
                    $variants[$key]['enabled'] = $enabled;
                    $variants[$key]['shipping'] = $variant['Variant']['shipping'];
                    $variants[$key]['product_sku_id'] = $variant['Variant']['id'];
                    $variants[$key]['productID'] = $variant['Variant']['product_id'];
                    $variants[$key]['color'] = isset($variant['Variant']['color']) ? $variant['Variant']['color'] : '';
                    $variants[$key]['size'] = isset($variant['Variant']['size']) ? $variant['Variant']['size'] : '';
                    $variants[$key]['erp_sku'] = $this->getErpSkuByJoomSku($variants[$key]['sku'], $this->joom_sku_resolve);
                    if($variants[$key]['sku']){
                        $skuarr = explode("*",$variants[$key]['sku']);  //sellerID sku * 前段
                    }
                    $variants[$key]['sellerID'] = isset($skuarr['0']) ? $skuarr['0'] : 0;

                    $j++;
                }

                if ($i == $j) {
                    $productInfo['status'] = 0;

                } else {
                    $productInfo['status'] = 1;
                }
                $return[$num]['productInfo'] = $productInfo;
                $return[$num]['variants'] = $variants;

                unset($variants);
                unset($productInfo);

            }
            return $return;


        } else {   //error
            var_dump($productList);
            return false;
        }


    }
    /**解析销售代码
     * @param $joomSku
     * @param $type
     * @return array|string
     */
    public function getSellCode($wishSku, $type)
    {
        if ($type == 2) {

            $tmpErpSku = substr($wishSku, 1, 3);

            return $tmpErpSku;
        } else {

            $tmpErpSku = explode('*', $wishSku);
            return $tmpErpSku[0];
        }


    }
    /** 获取对应的erp SKU
     * @param $joomSku
     * @param $type
     * @return string
     */
    public function getErpSkuByJoomSku($joomSku, $type)
    {

        $tmpSku = explode('+', $joomSku);
        $returnSku = array();
        foreach ($tmpSku as $k => $sku) {
            if (stripos($sku, '[') !== false) {
                $sku = preg_replace('/\[.*\]/', '', $sku);
            }
            if (stripos($sku, '(') !== false) {
                $sku = preg_replace('/\(.*\)/', '', $sku);
            }
            if ($type == 2) {

                $prePart = substr($sku, 0, 1);
                $suffPart = substr($sku, 4);
                $sku = $prePart . $suffPart;
                $newSku = $sku;
            } else {

                $tmpErpSku = explode('*', $sku);
                $i = count($tmpErpSku) - 1;
                $newSku = $tmpErpSku[$i];
            }
            $returnSku[] = $newSku;

        }

        return implode('+', $returnSku);

    }
    /* 创建新广告
     * data=[
     * 'name'=>'',
     * 'description'=>'',
     * 'tags'=>'',
     * 'sku'=>'',
     * 'color'=>'',
     * 'size'=>'',
     * 'inventory'=>'',
     * 'price'=>'',
     * 'shipping'=>'',
     * 'msrp'=>'',
     * 'shipping_time'=>'',
     * 'main_image'=>'',
     * 'parent_sku'=>'',
     * 'brand'=>'',
     * 'landing_page_url'=>'',
     * 'upc'=>'',
     * 'extra_images'=>'',
     *  ]
     */
    public function createProduct($addInfo)
    {
        $return = [];
        $addInfo['access_token'] = urldecode($this->access_token);
        $url = 'https://china-merchant.wish.com/api/v2/product/add';
        $resultJson = $this->postCurlHttpsData($url, $addInfo);
        $result = json_decode($resultJson, true);

        //$result['data']['Product']['id'] ='5764bbb162f84b657bee02143';
        //  var_dump($result);
        if (isset($result['data']['Product']['id'])) {
            $return['status'] = true;
            $return['info'] = $result['data']['Product']['id'];

        } else {
            $return['status'] = false;
            $return['info'] = isset($result['message']) ? $result['message'] : '未知错误';
        }
        return $return;
    }
    /**新增变量
     * @param $variant
     * @return bool
     */
    public function createVariation($variant)
    {
        $variant['access_token'] = urldecode($this->access_token);
        $url = 'https://china-merchant.wish.com/api/v2/variant/add';
        $resultJson = $this->postCurlHttpsData($url, $variant);
        $result = json_decode($resultJson, true);
        //$result['data']['Variant']['id'] ='213333333333';
        //   var_dump($result);
        if (isset($result['data']['Variant']['id'])) {
            return true;
        } else {
            //可以记录下日志
            return false;
        }
    }
    /** 更新子sku 信息
     * @param $variant
     * @param $url
     * @return array
     */
    public function updateProductVariation($variant, $url)
    {
        $return = [];
        $variant['access_token'] = urldecode($this->access_token);
        $resultJson = $this->postCurlHttpsData($url, $variant);
        $result = json_decode($resultJson, true);
        //$result['code']=0;
        if (isset($result['code']) && $result['code'] == 0) {
            $return['status'] = true;
            $return['info'] = 'success';
        } else {
            $return['status'] = false;
            $return['info'] = isset($result['message']) ? $result['message'] : '未知错误';
        }
        return $return;
    }
    /** 更新广告信息
     * @param $product
     * @return array
     */
    public function updateProduct($product)
    {
        $return = [];
        $product['access_token'] = urldecode($this->access_token);
        $url = 'https://china-merchant.wish.com/api/v2/product/update';
        $resultJson = $this->postCurlHttpsData($url, $product);
        $result = json_decode($resultJson, true);
        // $result['code']=0;
        if (isset($result['code']) && $result['code'] == 0) {
            $return['status'] = true;
            $return['info'] = 'success';
        } else {
            $return['status'] = false;
            $return['info'] = isset($result['message']) ? $result['message'] : '未知错误';
        }
        return $return;
    }

    public function getChangedOrders($startDate,$start,$limit){
        $url = "https://china-merchant.wish.com/api/v2/order/multi-get?";
        $apiArr = array();//api请求数组
        $apiArr['since'] = urlencode(date("Y-m-d", strtotime($startDate)));
        $apiArr['limit'] = urlencode($limit);
        $apiArr['start'] = urlencode($start * $limit);
        $apiArr['access_token'] = urldecode($this->access_token);
        $apiString = http_build_query($apiArr);
        $url = $url . $apiString;
        $orderjson = $this->getCurlData($url);
        $orderList = json_decode($orderjson, true);
        if (isset($orderList['code']) && ($orderList['code'] == 0) && !empty($orderList['data'])){

            return $orderList['data'];
        }else{
            return false;
        }

    }

    /*  $data=['id','tracking_provider','tracking_number','ship_note']
     *  $url = https://china-merchant.wish.com/api/v2/order/modify-tracking  //更新追踪号
     *  $url = https://china-merchant.wish.com/api/v2/order/fulfill-one // 可不传追踪号，
     */
    public function trackOperate($data, $url)
    {
        $product['access_token'] = urldecode($this->access_token);
        $return = [];
        $resultJson = $this->postCurlHttpsData($url, $data);
        $result = json_decode($resultJson, true);
        // $result['code']=0;
        if (isset($result['code']) && $result['code'] == 0) {
            $return['status'] = true;
            $return['info'] = 'success';
        } else {
            $return['status'] = false;
            $return['info'] = isset($result['message']) ? $result['message'] : '未知错误';
        }
        return $return;

    }

    public function isResetAccesstoken()
    {
        $now = date("Y-m-d H:i:s");
        $hours = (strtotime($now) - strtotime($this->expiry_time)) / 60 / 60;

        if ($hours > 10) {
            $json = $this->getAccessTokenByRefresh(); //获取最新的access_token
            $data = json_decode($json, true);

            if ($data['code'] == 0 && !empty($data['data'])) {
                DB::table('channel_accounts')->where('wish_client_id', $this->client_id)->update([
                    'wish_access_token' => $data['data']["access_token"],
                    'wish_refresh_token' => $data['data']['refresh_token'],
                    'wish_expiry_time' => date('Y-m-d H:i:s', $data['data']['expiry_time'])
                ]);
            } else {
                return false;
            }
            return $data['data']["access_token"];
        } else {
            return false;
        }
    }

    /**
     * 用refresh重新获取访问token
     * 并把wish的token重新更新
     */
    public function getAccessTokenByRefresh()
    {
        $getData = array();
        $getData['client_id'] = $this->client_id;
        $getData['client_secret'] = $this->client_secret;
        $getData['refresh_token'] = $this->refresh_token;
        $getData['grant_type'] = 'refresh_token';
        $apiString = http_build_query($getData);
        $url = 'https://merchant.wish.com/api/v2/oauth/refresh_token?';
        $url = $url . $apiString;
        $result = $this->getCurlData($url);
        return $result;
    }

    /**
     * Curl http Get 数据
     * 使用方法：
     * getCurlData
     */
    public function getCurlData($url, $time = '120')
    {

        $curl = curl_init(); // 启动一个CURL会话

        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, !empty($_SERVER ['HTTP_USER_AGENT']) ? $_SERVER ['HTTP_USER_AGENT'] : ''); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer

        if ($this->proxy_address != '') {
            curl_setopt($curl, CURLOPT_PROXY, $this->proxy_address);
            curl_setopt($curl, CURLOPT_PROXYPORT, '808');
        }
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $time);
        curl_setopt($curl, CURLOPT_TIMEOUT, $time); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回

        $tmpInfo = curl_exec($curl);
        if (curl_errno($curl)) {
            $error = curl_error($curl); //异常错误
            echo $error . '<br/>';
        }
        curl_close($curl); // 关闭CURL会话


        return $tmpInfo; // 返回数据
    }


    public function postCurlHttpsData($url, $data)
    { // 模拟提交数据函数

        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); // 从证书中检查SSL加密算法是否存在
        // curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER ['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer

        if ($this->proxy_address != '') {
            curl_setopt($curl, CURLOPT_PROXY, $this->proxy_address);
            curl_setopt($curl, CURLOPT_PROXYPORT, '808');
        }

        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 60); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {

            //die(curl_error ( $curl )); //异常错误
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据
    }

    public function getMessages()
    {
        $j = 0; //信息条数
        $initArray = [];
        $initArray['limit'] = 100; //每页数量
        $return_array = [];
        for($i=1; $i>0;$i++){
            $initArray['start'] = ($i-1)*$initArray['limit'];
            $initArray['access_token'] = $this->access_token;
            $url = 'https://merchant.wish.com/api/v2/ticket/get-action-required?'.http_build_query($initArray);
            $jsonData = $this->getCurlData($url);
            $apiReturn = json_decode($jsonData,true);
            if(empty($apiReturn['data'])){
                break;
            }
            foreach($apiReturn['data'] as $gd){

                $return_array[$j]['message_id']      = $gd['Ticket']['id']; //message_id
                $return_array[$j]['subject']		 = addslashes($gd['Ticket']['state']);//信息描述
                $return_array[$j]['date'] 	  	     = str_replace('T',' ',$gd['Ticket']['open_date']);//发件人发邮件的时间
                $return_array[$j]['from_name'] 	  	 = str_replace('T',' ',$gd['Ticket']['UserInfo']['name']);//用户名
                $return_array[$j]['from'] 	  	     = str_replace('T',' ',$gd['Ticket']['UserInfo']['id']);//用户Id

                $return_array[$j]['content'] 	  	 = base64_encode(serialize(['wish' => $gd['Ticket']['replies']]));   //信息内容
                //$return_array[$j]['order_info']      = serialize(['wish' => $gd['Ticket']['items']]);
                $return_array[$j]['to']         = 'wish账号';
                $return_array[$j]['labels']     = '';
                $return_array[$j]['label']      = 'Wish消息';
                $return_array[$j]['date']       = str_replace('T',' ',$gd['Ticket']['last_update_date']);//最后更新时间，邮件发送时间取该值
                $return_array[$j]['attachment'] = ''; //附件
                //$return_array[$j]['asdasd']   	 	 = addslashes($gd['Ticket']['label']);//邮件标题（英文）
                // $return_array[$j]['sublabel']	 	 = addslashes($gd['Ticket']['sublabel']);
                $return_array[$j]['state']  		 	 = $gd['Ticket']['state'];//wish邮件状态说明
                $return_array[$j]['stateID']		 	 = $gd['Ticket']['state_id'];//wish邮件状态ID
                // $return_array[$j]['orderInfo']	  	     = serialize($gd['Ticket']['items']);//wish订单信息
                //$return_array[$j]['last_update_date']    = str_replace('T',' ',$gd['Ticket']['last_update_date']);//最后更新时间，邮件发送时间取该值
                $return_array[$j]['photo_proof']		 = $gd['Ticket']['photo_proof'];//邮件是否包含图片


                $return_array[$j]['channel_message_fields'] = base64_encode(serialize(
                    [
                        'order_items'    => $gd['Ticket']['items'] ,   //订单信息
                        'locale'         => $gd['Ticket']['UserInfo']['locale'], // 区域
                    ]
                ));



                $j++;
            }
        }
        return (!empty($return_array)) ?  $return_array : false;
    }

    /**
     * 发送邮件
     * @param $replyMessage 回复记录
     */
    public function sendMessages($replyMessage)
    {

        $message_obj = $replyMessage->message;
        $param['id'] = $message_obj->message_id;
        $param['access_token'] = $this->access_token;
        $param['reply'] = $replyMessage->content;
        //print_r($param);exit;

        $result_json = $this->postCurlHttpsData('https://merchant.wish.com/api/v2/ticket/reply',$param);
        $result_ary = json_decode($result_json,true);

        if(!empty($result_ary['data']) && $result_ary['data']['success']==1){
            $replyMessage->status = 'SENT';
        }else{
            $replyMessage->status = 'FAIL';
        }
        return $replyMessage->status== 'SENT' ? true : false;
    }


    public function changeMessageState(){

    }

    /**
     * 请求wish support的邮件
     * @param $mailID
     * @return bool
     */
    public function ReplayWishSupport($mailID){
        $data['id']           =  $mailID;
        $data['access_token'] = $this->access_token;
        $result = json_decode($this->postCurlHttpsData('https://merchant.wish.com/api/v2/ticket/appeal-to-wish-support',$data),true);
        if(!empty($re['data']) && $re['data']['success']==1){
            return true;
        }else{
            return false;
        }
    }

    /*
     *更改joom广告在线数量API
     *
     */
    public function changejoomProductCount($sku,$count){
        $url = 'https://api-merchant.joom.it/api/v2/variant/update-inventory';
        $data = "sku=".$sku."&inventory=".$count."&access_token=".$this->access_token."";
        $result = $this->postCurlHttpsData($url,$data,5);
        return json_decode($result,true);
    }
    /**
     * 更改广告运费
     */
    public function changeProductShipping($original_sku,$shipping){
        $url = 'https://api-merchant.joom.it/api/v2/variant/update';
        $data = "sku=".$original_sku."&shipping=".$shipping."&access_token=".$this->access_token."";
        $result = $this->postCurlHttpsData($url,$data,5);
        return json_decode($result,true);
    }
    /**
     * 更改joom在线sku的价格
     */
    public function changejoomProductPrice($sku,$price){
        $url = 'https://api-merchant.joom.it/api/v2/variant/update';
        $data = "sku=".$sku."&price=".$price."&access_token=".$this->access_token."";
        $result = $this->postCurlHttpsData($url,$data,5);
        return json_decode($result,true);
    }
    /**
     * 更改joom线上广告的状态
     * 广告某个sku
     */
    public function changeProductStatusbySku($sku,$enabled){
        $url = 'https://api-merchant.joom.it/api/v2/variant/'.$enabled.'';
        $data = "sku=".$sku."&access_token=".$this->access_token."";
        $result = $this->postCurlHttpsData($url,$data,5);
        return json_decode($result,true);
    }
    /*
		* joom标记发货 API 请求
		*id 			   => table:erp_orders -> orderlineitemid
		*tracking_provider => $provider_arr
						   ex:USPS
		*tracking_number   => table:orders
		*key 			   => $accInfo[0]['wish_key']
	*/
    public function joomApiOrdersToShipping($joomId,$trackingProvider,$trackingNumber,$status){

        $url = "https://api-merchant.joom.it/api/v2/order/fulfill-one";
        if($trackingNumber && $status == 'SHIPPED'){     //需求 订单状态发货才上传追踪号 否则只标记发货  后续订单状态变发货了再更新追踪号
            $data = "tracking_provider=".$trackingProvider."&tracking_number=".$trackingNumber."&id=".$joomId."&access_token=".$this->access_token."&shipping_time=30-60";

        }else if($trackingNumber && $status == 'upload'){  //是需要更新追踪号订单
            $url = "https://api-merchant.joom.it/api/v2/order/modify-tracking";
            $data = "tracking_provider=".$trackingProvider."&tracking_number=".$trackingNumber."&id=".$joomId."&access_token=".$this->access_token."&shipping_time=30-60";

        }else{
            $data = "tracking_provider=".$trackingProvider."&id=".$joomId."&access_token=".$this->access_token."&shipping_time=30-60";

        }
        $time = 200;
        //joom请求API
        $jsonCodeToArr = $this->postCurlHttpsData($url, $data,$time);
        return json_decode($jsonCodeToArr,true);
    }

    /*Time:2016-10-15
	*从erp_joom_shipping表中找出需要joom请求的数据
	*拆单的，追踪号和erp_joom_shipping表中对应上一次请求完成的订单的追踪号不同的则为需要请求订单
	*@hejiancheng
	*/
    public function joomApiOrdersmodifytracking($logistics_name,$code,$joomID){

        $url = "https://api-merchant.joom.it/api/v2/order/modify-tracking";
        $data = "tracking_provider=".$logistics_name."&tracking_number=".$code."&id=".$joomID."&access_token=".$this->access_token."&shipping_time=25-60";
        $time = 200;
        //joom请求API
        $jsonCodeToArr = $this->postCurlHttpsData($url, $data,$time);
        return json_decode($jsonCodeToArr,true);
    }































}