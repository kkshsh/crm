<?php
namespace App\Modules\Channel\Adapter;

/**
 * Created by PhpStorm.
 * User: Vincent
 * Date: 16/5/17
 * Time: 下午2:54
 */

use Tool;

Class LazadaAdapter implements AdapterInterface
{
    private $serviceUrl;
    private $signatureVersion = '2';
    private $signatureMethod = 'HmacSHA256';
    private $version = '2013-09-01';
    private $config = [];
    private $perPage = 10;
    public $apiResponse;


    public function __construct($config)
    {
        $this->serviceUrl = $config['lazada_api_host'];
        unset($config['serviceUrl']);
        $this->config = array_merge($config);
        $this->config['SignatureVersion'] = $this->signatureVersion;
        $this->config['SignatureMethod'] = $this->signatureMethod;
        $this->config['Version'] = $this->version;
    }



    /**
     * 获取订单列表
     * @param $startDate
     * @param $endDate
     * @param array $status
     * @param int $perPage
     * @return array
     */
    public function listOrders($startDate, $endDate, $status = [], $perPage = 0, $nextToken = '')
    {
        $result_orders = [];
        if (empty($nextToken)) {
            $nextToken = 1;
        }

        $offset = ((int)$nextToken - 1) * $perPage;


        $orders_data = $this->getLazadaOrder($startDate, $endDate, $status, $offset);

        if (!isset($orders_data['Body']['Orders']['Order']) || empty($orders_data['Body']['Orders']['Order'])) {

            if(isset($orders_data['Head']['ErrorCode'])){
                return  [
                    'error' => [
                        'code' => $orders_data['Head']['ErrorCode'],
                        'message' =>isset($orders_data['Head']['ErrorMessage'])?$orders_data['Head']['ErrorMessage']:''
                    ]
                ];
            }
            $nextToken = '';
        } else {
            //单订单情况
            if (!isset($orders_data['Body']['Orders']['Order'][0])) {
                $orders = [$orders_data['Body']['Orders']['Order']];
            } else {
                $orders = $orders_data['Body']['Orders']['Order'];//获取订单信息数组
            }

            foreach ($orders as $key => $order_info) {
                if ($order_info['OrderId'] == 0) //OrderId为0 的订单会导致获取产品的时候出问题
                {
                    continue;
                }
                $items = $this->getLazadaOrderItems($order_info['OrderId']);
                //$items = $itemarr['Body']['OrderItems']['OrderItem'];

                $tmp_order = $this->parseOrder($order_info, $items);

                $result_orders[] = $tmp_order;
            }
            $nextToken++;
        }


        return ['orders' => $result_orders, 'nextToken' => $nextToken];

    }
    
    public function getOrder($orderID){}
        
    


    public function getLazadaOrder($startDate, $endDate, $status, $offset)
    {
        $api_key = $this->config['lazada_access_key'];
        $lazada_api_host = $this->config['lazada_api_host'];
        $lazada_user_id = $this->config['lazada_user_id'];

        $now = new \DateTime($endDate);
        $after = new \DateTime($startDate);


        $parameters = array(
            //'UserID' => 'lazada.api@moonar.com',
            'UserID' => $lazada_user_id,
            'Version' => '1.0',
            'Action' => 'GetOrders',
            'Timestamp' => $now->format(\DateTime::ISO8601),
            //  'UpdatedAfter' => $after->format(DateTime::ISO8601),
            //'Status' => 'pending', // 只抓pending 状态下的订单

            'CreatedAfter' => $after->format(\DateTime::ISO8601),
            'Limit' => $this->perPage,
            'Offset' => $offset,
        );

        if ($status) {
            $parameters['Status'] = $status[0];
        }

        ksort($parameters);

        $params = array();

        foreach ($parameters as $name => $value) {
            $params[] = rawurlencode($name) . '=' . rawurlencode($value);
        }
        $strToSign = implode('&', $params);

        $parameters['Signature'] = rawurlencode(hash_hmac('sha256', $strToSign, $api_key, false));

        $request = http_build_query($parameters);

        $url = $lazada_api_host . '/?' . $request;

        $xml_orders = $this->setRequest($url);
        $orders = $this->XmlToArray($xml_orders);


        return $orders;
        //echo 'orders:<pre>';print_r($orders);exit;

    }


    public function getLazadaOrderItems($order_id)
    {
        $api_key = $this->config['lazada_access_key'];
        $lazada_api_host = $this->config['lazada_api_host'];
        $lazada_user_id = $this->config['lazada_user_id'];

        $now = new \DateTime();

        $parameters = array(
            //'UserID' => 'lazada.api@moonar.com',
            'UserID' => $lazada_user_id,
            'Version' => '1.0',
            'Action' => 'GetOrderItems',
            'Timestamp' => $now->format(\DateTime::ISO8601),
            //'OrderId' => '989325',
            'OrderId' => $order_id,
        );
        ksort($parameters);

        $params = array();

        foreach ($parameters as $name => $value) {
            $params[] = rawurlencode($name) . '=' . rawurlencode($value);
        }
        $strToSign = implode('&', $params);

        $parameters['Signature'] = rawurlencode(hash_hmac('sha256', $strToSign, $api_key, false));

        $request = http_build_query($parameters);

        $xml_order_item = $this->setRequest($lazada_api_host . '/?' . $request);

        $itemarr = $this->XmlToArray($xml_order_item);

        if (isset($itemarr['Head']['ErrorCode'])) {
            if ($itemarr['Head']['ErrorMessage'] == 'Too many API requests') {
                //need to write log info
                return false;
            } else {
                //不是请求频繁，保存下错误原因
                $op = $order_info['OrderId'] . "获取订单产品失败，信息为 " . $itemarr['Head']['ErrorMessage'];
                //need to write log info
                //$stillHasOrder = false;
                return false;

            }
        }


        $items = $itemarr['Body']['OrderItems']['OrderItem'];
        if (isset($items['OrderItemId'])) {
            $temp = $items;
            $items = [];
            $items[0] = $temp;
        }

        return $items;

    }

    public function parseOrder($order_info, $item_info)
    {
        $sku_data = $this->getSkuInfo($item_info);
        $total = $sku_data['total'];//订单总价格
        $orders_ship_fee = $sku_data['orders_ship_fee'];

        $lazada_currency_type = $this->config['lazada_currency_type'];

        $data = array();

        $buyer_address_1 = (!empty($order_info['AddressShipping']['Address1'])) ? $order_info['AddressShipping']['Address1'] : '';

        if (!empty($order_info['AddressShipping']['Address2'])) {
            $buyer_address_1 .= ' ' . $order_info['AddressShipping']['Address2'];
        }

        //多地址
        $address = array();
        if (!empty($order_info['AddressShipping']['Address3'])) {
            $address[] = $order_info['AddressShipping']['Address3'] ? $order_info['AddressShipping']['Address3'] : '';
        }

        if (!empty($order_info['AddressShipping']['Address4'])) {
            $address[] = $order_info['AddressShipping']['Address4'] ? $order_info['AddressShipping']['Address4'] : '';
        }

        if (!empty($order_info['AddressShipping']['Address5'])) {
            $address[] = $order_info['AddressShipping']['Address5'] ? $order_info['AddressShipping']['Address5'] : '';
        }

        $buyer_address_2 = "";

        if (!empty($address)) {
            $buyer_address_2 = join(' ', $address);
        }

        $phone = (!empty($order_info['AddressShipping']['Phone'])) ? $order_info['AddressShipping']['Phone'] : $order_info['AddressShipping']['Phone2'];


        $result = [
            'channel_ordernum' => $order_info['OrderNumber'],
            'channel_listnum' =>$order_info['OrderId'],
            //'email' => $order->BuyerEmail,
            'amount' => $total,
            'amount_shipping' => $orders_ship_fee,
            'currency' => $lazada_currency_type,
            'payment' => $order_info['PaymentMethod'],
            'shipping' => "dropshipping",
            'shipping_firstname' => isset($order_info['AddressShipping']['FirstName']) ? $order_info['AddressShipping']['FirstName'] : '',
            'shipping_lastname' => ($order_info['AddressShipping']['LastName']) ? $order_info['AddressShipping']['LastName'] : '',
            'shipping_address' => $buyer_address_1,
            'shipping_address1' => $buyer_address_2,
            'shipping_city' => $order_info['AddressShipping']['City'],
            'shipping_state' => $order_info['AddressShipping']['Region'] ? $order_info['AddressShipping']['Region'] : '',
            'shipping_country' => $order_info['AddressShipping']['Country'],
            'shipping_zipcode' => $order_info['AddressShipping']['PostCode'],
            'shipping_phone' => $phone,
            'payment_date' => $order_info['CreatedAt'],
            'create_time' => $order_info['CreatedAt'],
            'fulfill_by' => "",
            'status' => 'PAID',
//            'remark' => $order_info['Remarks'] ? $order_info['Remarks'] : '',
            'items' => []
        ];

        $items = [];

        foreach ($sku_data as $k => $v) {

            if (empty($v['orders_sku'])) {
                continue;
            }

            $items[] = [
                'sku' => $v['orders_sku'],
                'channel_sku' => $v['channel_sku'],
                'quantity' => $v['item_count'],
                'price' => $v['item_price'],
                'orders_item_number' => $v['OrderItemId'],
                'currency' => $lazada_currency_type, 
                'channel_order_id' => $result['channel_ordernum'],
                'transaction_id' => $v['comment_text']
            ];
        }

        $result['items'] = $items;


        return $result;

    }

    public function getSkuInfo($item_info)
    {
        //整理数据
        $sku_data = array();

        $total = 0;//订单总价格

        $orders_ship_fee = 0;

        foreach ($item_info as $item) {

            $sku_info = array(
                0 => array(
                    'sku' => $item['Sku'],
                    'count' => 1,
                    'price' => $item['ItemPrice'],
                )
            );

            $data = $this->resetTransactionDetail($sku_info);

            foreach ($data as $v) {

                $total += $item['ItemPrice'];

                $orders_ship_fee += $item['ShippingAmount'];


                if (isset($sku_data[$v['sku']])) {
                    $sku_data[$v['sku']]['item_count'] += 1;
                    $sku_data[$v['sku']]['comment_text'] = $item['OrderItemId'] . '@' . $v['sku'] . ',' . $sku_data[$v['sku']]['comment_text'];
                } else {
                    $sku_data[$v['sku']]['OrderItemId'] = $item['OrderItemId'];
                    $sku_data[$v['sku']]['comment_text'] = $item['OrderItemId'] . '@' . $v['sku'];;
                    $sku_data[$v['sku']]['item_count'] = $v['count'];
                    $sku_data[$v['sku']]['item_price'] = $v['price'];
                    $sku_data[$v['sku']]['orders_sku'] = $v['sku'];
                    $sku_data[$v['sku']]['channel_sku'] = $v['channel_sku'];
                }


            }
        }
        $sku_data['total'] = $total;
//     	$sku_data['orders_ship_fee'] = $orders_ship_fee;

        $sku_data['orders_ship_fee'] = 0;    //lazada不需要运费


        //  var_dump($sku_data);exit;
        return $sku_data;
    }

    // 解析ebaySKU信息
    function resetTransactionDetail($array)
    {
        $newArray = array();
        if ($array) {
            foreach ($array as $row) {
                //1.先去掉'+'
                $tmpSkuArray = explode('+', $row['sku']);
                $tmpCount = count($tmpSkuArray); //SKU种类总数
                foreach ($tmpSkuArray as $tmpSku) {
                    //先用一个数组保存最原始的一维数组信息
                    $data = $row;
                    $data['sku'] = $tmpSku; //SKU信息暂时已变更，重新赋值下就行
                    $data['price'] = round($data['price'] / $tmpCount, 2); //组合SKU的单价平均处理
                    $data['channel_sku'] = $row['sku'];
                    //2.再去掉‘*’,可以直接取星号之后的部分
                    $tmp = explode('*', $tmpSku);
                    $tmpSku = trim(array_pop($tmp));

                    //3.忽略中括号内的信息
                    if (stripos($tmpSku, '[') !== false) {
                        $tmpSku = preg_replace('/\[.*\]/', '', $tmpSku);
                    }

                    //4.处理小括号及其单价数量
                    if (stripos($tmpSku, '(') !== false) {
                        $sku = trim($this->getStringBetween($tmpSku, '', '('));
                        $qty = trim($this->getStringBetween($tmpSku, '(', ')'));
                        $data['sku'] = $sku;
                        $data['count'] = $qty * $data['count'];
                        $data['price'] = round($data['price'] / $qty, 2);
                        $newArray[] = $data;
                    } else {
                        $data['sku'] = trim($tmpSku);
                        $newArray[] = $data;
                    }
                }
            }
        }
        return $newArray;
    }

    function getStringBetween($string, $start = '', $end = '') //取从某个字符首次出现的位置开始到另一字符首次出现的位置之间的字符串
    {
        //$s = ($start != '') ? stripos($string,$start)+1 : 0 ;$e = ($end != '' ) ? stripos($string,$end) : strlen($string) ;
        //if($s <= $e){return substr($string,$s,$e-$s);}else{return false;}
        $s = ($start != '') ? stripos($string, $start) : 0;
        $e = ($end != '') ? stripos($string, $end) : strlen($string);
        if ($s <= $e) {
            $string = substr($string, $s, $e - $s);
            return str_replace($start, '', $string);
        } else {
            return false;
        }
    }


    public function XmlToArray($xml)
    {
        $array = (array)($xml);
        foreach ($array as $key => $item) {

            $array[$key] = $this->struct_to_array((array)$item);
        }
        return $array;
    }

    public function struct_to_array($item)
    {
        if (!is_string($item)) {

            $item = (array)$item;
            foreach ($item as $key => $val) {

                $item[$key] = $this->struct_to_array($val);//wudequan:此处一定要注意XBug的最大嵌套数，可以修改配置文件加大最大嵌套数
            }
        }
        return $item;
    }


    /**
     * 发送请求
     * @param $type
     * @param $request
     * @return \SimpleXMLElement
     */
    public function setRequest($requestUrl)
    {
        return simplexml_load_string(Tool::curl($requestUrl));
    }

    /** api 共用
     * @param $parameters  api参数
     * @return array api返回结果
     */
    public function commonLazada($parameters){

        $api_key = $this->config['lazada_access_key'];
        $lazada_api_host = $this->config['lazada_api_host'];
        ksort($parameters);
        $params = array();

        foreach ($parameters as $name => $value) {

            $params[] = rawurlencode($name) . '=' . rawurlencode($value);

        }
        $strToSign = implode('&', $params);

        $parameters['Signature'] = rawurlencode(hash_hmac('sha256', $strToSign, $api_key, false));

        $request = http_build_query($parameters);

        $info =$this->setRequest($lazada_api_host.'/?'.$request);

        $result  = $this->XmlToArray($info);
        return $result;
    }

    /** 标记发货
     * @param $tracking_info
     * @return array
     */
    public function returnTrack($tracking_info)
    {
        $return = [];
        $now = new \DateTime();
        $parameters = array(
            'UserID' => $this->config['lazada_user_id'],
            'Action' => 'SetStatusToReadyToShip',
            'OrderItemIds' => '[' . $tracking_info['OrderItemIds'] . ']',
            'DeliveryType' => 'dropship',
            'ShippingProvider' => $tracking_info['ShippingProvider'],
            'TrackingNumber' => $tracking_info['TrackingNumber'],
            'Timestamp' => $now->format(\DateTime::ISO8601),
            'Version' => '1.0',

        );
        $result = $this->commonLazada($parameters);

        if (isset($result['Body']['OrderItems']['OrderItem'])) {
            $return['status'] = true;
            $return['info'] = 'Success';
        } else {
            $return['status'] = false;
            $return['info'] = isset($result['Head']['ErrorMessage']) ? $result['Head']['ErrorMessage'] : 'Error';
        }


        return $return;
    }

    public function getPackageId($OrderIdList){
        $return=[];
        $now = new \DateTime();
        $parameters = array(
            'UserID' => $this->config['lazada_user_id'],
            'Action' => 'GetMultipleOrderItems',
            'OrderIdList'=>'['.$OrderIdList.']',
            'Timestamp' => $now->format(\DateTime::ISO8601),
            'Version' => '1.0',
        );
        $result = $this->commonLazada($parameters);
        $this->apiResponse = $result;
        if (isset($result['Body']['Orders']['Order'])) {
            if(isset($result['Body']['Orders']['Order']['OrderItems']['OrderItem'][0])){
                foreach($result['Body']['Orders']['Order']['OrderItems']['OrderItem'] as $item){
                    $return[$item['OrderItemId']]['PackageId'] = $item['PackageId'];
                    $return[$item['OrderItemId']]['TrackingCode'] = $item['TrackingCode'];
                }
            }else{
                $return[$result['Body']['Orders']['Order']['OrderItems']['OrderItem']['OrderItemId']]['PackageId'] = $result['Body']['Orders']['Order']['OrderItems']['OrderItem']['PackageId'];
                $return[$result['Body']['Orders']['Order']['OrderItems']['OrderItem']['OrderItemId']]['TrackingCode'] = $result['Body']['Orders']['Order']['OrderItems']['OrderItem']['TrackingCode'];
            }
            return $return;

        }else{
            return false;
        }


    }
    
    /**
     * 获取lazada线上数据
     * @param unknown $Offset
     */
    public function getLazadaProducts($Offset){
        $return=[];
        $now = new \DateTime();
        $parameters = array(
            'UserID' => $this->config['lazada_user_id'],
            'Action' => 'GetProducts',
            'Timestamp' => $now->format(\DateTime::ISO8601),
            'Version' => '1.0',
            'Limit' => 100, // 限制返回的条数
            'Offset' => $Offset
            
        );
        return $this->commonLazada($parameters);
    }
    
    /**
     * 更新sku信息
     * @param unknown $sellersku  
     * @param unknown $type  1:调整数量,2:修改价格,3:调整状态,4:修改特殊价格
     * @param unknown $infoarr
     * @param string $pricearr
     * @return array
     */
    public function operateProduct($sellersku,$type,$infoarr,$pricearr=' '){
        $now = new \DateTime();
        $parameters = array(
            'UserID' => $this->config['lazada_user_id'],
            'Action' => 'ProductUpdate',
            'Timestamp' => $now->format(\DateTime::ISO8601),
            'Version' => '1.0',        
        );
        $xml =  '<?xml version="1.0" encoding="UTF-8" ?>
                <Request>';
        if(!is_array($sellersku)){
            $sellersku = array($sellersku);
        }
        
        foreach($sellersku as $sku)
        {
            if($type==1)
            {
                $xml.='<Product>';
                $xml.= "<SellerSku>$sku</SellerSku>";
                $xml.='<Quantity>'.$infoarr['Quantity'].'</Quantity>';
                $xml.='</Product>';
            }
            if($type==2)
            {
                $xml.='<Product>';
                $xml.= "<SellerSku>$sku</SellerSku>";
                $xml.='<Price>'.$infoarr['Price'].'</Price>';
                $xml.='</Product>';
            }
            if($type==3)
            {
                $xml.='<Product>';
                $xml.= "<SellerSku>$sku</SellerSku>";
                $xml.='<Status>'.$infoarr['Status'].'</Status>';
                $xml.='</Product>';
            }
            if($type==4)
            {
                $xml.='<Product>';
                $xml.= "<SellerSku>$sku</SellerSku>";
                $xml.='<Price>'.$infoarr['Price'].'</Price>';
                $xml.='<SalePrice>'.$infoarr['SalePrice'].'</SalePrice>';
                $xml.='<SaleStartDate>'.$infoarr['StartDate'].'</SaleStartDate>';
                $xml.='<SaleEndDate>'.$infoarr['EndDate'].'</SaleEndDate>';
                $xml.='</Product>';
            }
            if($type==5)
            {
                $xml.='<Product>';
                $xml.= "<SellerSku>$sku</SellerSku>";
                $xml.='<Price>'.$pricearr[$sku]['Price'].'</Price>';
                $xml.='<SalePrice>'.$infoarr['SalePrice'].'</SalePrice>';
                $xml.='<SaleStartDate>'.$pricearr[$sku]['saleStartDate'].'</SaleStartDate>';
                $xml.='<SaleEndDate>'.$pricearr[$sku]['saleEndDate'].'</SaleEndDate>';
                $xml.='</Product>';
            }
            if($type==6)
            {
                // 批量修改数量
                $xml .=$infoarr;
            }
        }
        $xml.='</Request>';

        $returninfo = $this->commonLazadaParameters($parameters,$xml);
        return $returninfo;
        
    }
    
    public function getCurlData($queryString,$xml='')
    {    
        $curl = curl_init();    
        curl_setopt($curl, CURLOPT_URL, $this->config['lazada_api_host']."/?".$queryString);    
        curl_setopt($curl, CURLOPT_HEADER, 0);    
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);    
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);    
        curl_setopt ( $curl, CURLOPT_POST, 1 ); // 发送一个常规的Post请求    
        curl_setopt($curl,CURLOPT_POSTFIELDS,$xml);    
        $content = curl_exec($curl);    
        curl_close($curl);    
        return $content;
    }
    
    public function commonLazadaParameters($parameters,$xml=''){
        $api_key = $this->config['lazada_access_key'];
        $lazada_api_host = $this->config['lazada_api_host'];
        ksort($parameters);
        $params = array();
        
        foreach ($parameters as $name => $value) {        
            $params[] = rawurlencode($name) . '=' . rawurlencode($value);        
        }
        $strToSign = implode('&', $params);        
        $parameters['Signature'] = rawurlencode(hash_hmac('sha256', $strToSign, $api_key, false));
        
        $request = http_build_query($parameters);
        
        $info =simplexml_load_string($this->getCurlData($request,$xml));
        $result  = $this->XmlToArray($info);
        
        return $result;
    }
    
    public function getFeedInfo(){
        $now = new \DateTime();
        $parameters = array(
            'UserID' => $this->config['lazada_user_id'],
            'Version' => '1.0',
            'Action' => 'FeedList',
            'Timestamp' => $now->format(\DateTime::ISO8601),
        );
        return $this->commonLazada($parameters);
    }











    public function getMessages()
    {
        return false;
    }

    public function sendMessages($replyMessage)
    {

    }

}