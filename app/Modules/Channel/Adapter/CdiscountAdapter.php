<?php
namespace App\Modules\Channel\Adapter;

/**
 * Created by PhpStorm.
 * User: Vincent
 * Date: 16/5/17
 * Time: 下午2:54
 */

use Tool;

Class CdiscountAdapter implements AdapterInterface
{
    private $serviceUrl;
    private $signatureVersion = '2';
    private $signatureMethod = 'HmacSHA256';
    private $version = '2013-09-01';
    private $config = [];
    private $perPage = 10;


    public function __construct($config)
    {

        unset($config['serviceUrl']);
        $this->config = array_merge($config);
        $this->config['SignatureVersion'] = $this->signatureVersion;
        $this->config['SignatureMethod'] = $this->signatureMethod;
        $this->config['Version'] = $this->version;
    }

    /**
     * 获取单个订单内容
     * @param $orderID
     * @return array|bool
     */
    public function getOrder($orderID)
    {
        $request['Action'] = 'GetOrder';
        $request['AmazonOrderId.Id.1'] = $orderID;
        $response = $this->setRequest('Orders', $request);
        $order = $response->GetOrderResult->Orders->Order;
        if ($order) {
            $orderItems = $this->getOrderItems($order->AmazonOrderId);
            return $this->parseOrder($order, $orderItems);
        }
        return false;
    }

    /**
     * 获取订单列表
     * @param $startDate
     * @param $endDate
     * @param array $status
     * @param int $perPage
     * @return array
     */
    public function listOrders($startDate, $endDate, $status = [], $perPage = 0,$nextToken='')
    {
        $result_orders = [];

        $OrderList = $this->getPlatformOrder($startDate, $endDate, $status);

        if(isset($OrderList[0]->GetOrderListResponse->GetOrderListResult->OrderList->Order)
            && !is_array($OrderList[0]->GetOrderListResponse->GetOrderListResult->OrderList->Order)){


            foreach($OrderList[0]->GetOrderListResponse->GetOrderListResult->OrderList->Order as $Order){

                if($Order->OrderState!='WaitingForShipmentAcceptation'){
                    continue;
                }

                $tmp_orders = $this->parseOrder($Order);
                $result_orders[] = $tmp_orders;

            }
        }

        return ['orders' => $result_orders, 'nextToken' => $nextToken];
    }




    public function parseOrder($Order){

        $orderNumberArr = '';
        $orderNumber    = '';
        $currency_type = $this->config['cd_currency_type'];
        $orderNumberArr =  (array)$Order->OrderNumber;
        $orderNumber    = $orderNumberArr[0];

        $paidTimeArr = (array)$Order->CreationDate;
        $paidTime = $paidTimeArr[0];

        $buyer_phone = (string)$Order->Customer->MobilePhone;
        if(empty($buyer_phone)){
            $buyer_phone = (string)$Order->Customer->Phone;
        }

        $buyer_address_1 = "";
        if(!empty($Order->ShippingAddress->Street)){
            $buyer_address_1         = (string)$Order->ShippingAddress->Street;//发货地址1
        }
        if(!empty($Order->ShippingAddress->ApartmentNumber)){
            $buyer_address_1    .= ' '.(string)$Order->ShippingAddress->ApartmentNumber;
        }

        $result = [
            'channel_ordernum' => $orderNumber,
            'email' => (string)$Order->Customer->EncryptedEmail,
            'amount' => (string)$Order->ValidatedTotalAmount,
            'amount_shipping'=> (string)$Order->ValidatedTotalShippingCharges,
            'currency' => $currency_type,
            //'payment' =>  $order_info['PaymentMethod'],
            'shipping' => (string)$Order->ShippingCode,
            'shipping_firstname' =>(string)$Order->ShippingAddress->FirstName,
            'shipping_lastname' => (string)$Order->ShippingAddress->LastName,
            'shipping_address' => $buyer_address_1,
            //'shipping_address1' => $buyer_address_2,
            'shipping_city' =>  (string)$Order->ShippingAddress->City,
            'shipping_state' => (string)$Order->ShippingAddress->County,
            'shipping_country' => (string)$Order->ShippingAddress->Country,
            'shipping_zipcode' => (string)$Order->ShippingAddress->ZipCode,
            'shipping_phone' => (string)$Order->Customer->MobilePhone,
            'payment_date' => $paidTime,
            'create_time' =>$paidTime,
            'fulfill_by' => "",
            'remark' =>'',
            'status' =>'PAID',
            'items' => []
        ];

        $items = [];

        foreach($Order->OrderLineList->OrderLine  as $k => $OrderLine){

            if(empty($OrderLine->SellerProductId)){
                continue;
            }

            $totalPrice = (string)$OrderLine->PurchasePrice;
            $quantity   = (string)$OrderLine->Quantity;
            $skuCode   = (string)$OrderLine->SellerProductId;


            list($sku, $newskudata) = $this->parseSkuInfo($skuCode);

            if(is_array($newskudata) && !empty($newskudata)){
                $sku=$newskudata['str'];
                $item_count = $newskudata['numb'];
            }


            $items[] = [
                'sku' => $sku,
                'channel_sku' => $skuCode,
                'quantity' =>$quantity,
                'price' => number_format($totalPrice/$quantity,3),
                'orders_item_number' => (string)$OrderLine->ProductId,
                'currency' =>$currency_type ,
            ];
        }

        $result['items'] = $items;


        return $result;

    }

    public function parseSkuInfo($string){

        $str = $string;
        $result = [];

        $stringHead = substr(trim($string), 0, 3);
        $stringThree = substr(trim($string), 3);
        if ((int) $stringHead > 0) {
            $result[] = trim($stringThree);
        } else {
            $result[] =  trim($string);
        }

        //处理MHM033b(2)类型,如果是则返回数组
        if(preg_match_all("/\([\d]+\)/",$str,$kh)){
            $str = str_replace($kh[0][0],'',$str);
            preg_match_all("/[\d]+/",$kh[0][0],$numb);
            $numb = $numb[0][0];//数量
            $arr = array('str'=>$str,'numb'=>$numb);
            $result[] = $arr;
        }else{
            $result[] = [];
        }

        return $result;

    }


    public function getPlatformOrder($startDate, $endDate,$status){
        $cd_token_id = $this->config['cd_token_id'];



        $BeginCreationDate = date('Y-m-d\TH:i:s', strtotime($startDate));
        $BeginModificationDate = date('Y-m-d\TH:i:s', strtotime($startDate));

        if(empty($endDate)){
            $EndCreationDate = date('Y-m-d\TH:i:s');
            $EndModificationDate = date('Y-m-d\TH:i:s');
        }else{
            $EndCreationDate = date('Y-m-d\TH:i:s', strtotime($endDate));
            $EndModificationDate = date('Y-m-d\TH:i:s', strtotime($endDate));
        }


        $data = '<?xml version="1.0" encoding="UTF-8"?>';
        $data .= '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">';
        $data .= '<s:Body>';
        $data .= '<GetOrderList xmlns="http://www.cdiscount.com">';
        $data .= '<headerMessage xmlns:a="http://schemas.datacontract.org/2004/07/Cdiscount.Framework.Core.Communication.Messages" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">';
        $data .= '<a:Context>';
        $data .= '<a:CatalogID>1</a:CatalogID>';
        $data .= '<a:CustomerPoolID>1</a:CustomerPoolID>';
        $data .= '<a:SiteID>100</a:SiteID>';
        $data .= '</a:Context>';
        $data .= '<a:Localization>';
        $data .= '<a:Country>Fr</a:Country>';
        $data .= '<a:Currency>Eur</a:Currency>';
        $data .= '<a:DecimalPosition>2</a:DecimalPosition>';
        $data .= '<a:Language>Fr</a:Language>';
        $data .= '</a:Localization>';
        $data .= '<a:Security>';
        $data .= '<a:DomainRightsList i:nil="true" />';
        $data .= '<a:IssuerID i:nil="true" />';
        $data .= '<a:SessionID i:nil="true" />';
        $data .= '<a:SubjectLocality i:nil="true" />';
        $data .= '<a:TokenId>' . $cd_token_id . '</a:TokenId>';
        $data .= '<a:UserName i:nil="true" />';
        $data .= '</a:Security>';
        $data .= '<a:Version>1.0</a:Version>';
        $data .= '</headerMessage>';
        $data .= '<orderFilter xmlns:i="http://www.w3.org/2001/XMLSchema-instance">';
        $data .= '<BeginCreationDate>' . $BeginCreationDate . '</BeginCreationDate>';
        $data .= '<BeginModificationDate>' . $BeginModificationDate . '</BeginModificationDate>';
        $data .= '<EndCreationDate>' . $EndCreationDate . '</EndCreationDate>';
        $data .= '<EndModificationDate>' . $EndModificationDate . '</EndModificationDate>';
        $data .= '<FetchOrderLines>true</FetchOrderLines>';
        $data .= '<States>';

        if($status){
            //$data .= '<OrderStateEnum>WaitingForShipmentAcceptation</OrderStateEnum>'; //待发货的订单
            $data .= '<OrderStateEnum>'.$status[0].'</OrderStateEnum>'; //待发货的订单
        }

        $data .= '</States>';
        $data .= '</orderFilter>';
        $data .= '</GetOrderList>';
        $data .= '</s:Body>';
        $data .= '</s:Envelope>';




        $xml_orders = $this->setRequest($data);
        //$orders = $this->XmlToArray($xml_orders);
//echo '============<pre>';print_r($xml_orders);exit;
        return $xml_orders;
        //echo 'orders:<pre>';print_r($orders);exit;

    }



    // 解析ebaySKU信息
    function resetTransactionDetail($array) {
        $newArray = array();
        if ($array) {
            foreach ($array as $row) {
                //1.先去掉'+'
                $tmpSkuArray = explode('+', $row['sku']);
                $tmpCount    = count($tmpSkuArray); //SKU种类总数
                foreach ($tmpSkuArray as $tmpSku) {
                    //先用一个数组保存最原始的一维数组信息
                    $data = $row;
                    $data['sku'] = $tmpSku; //SKU信息暂时已变更，重新赋值下就行
                    $data['price'] = round($data['price'] / $tmpCount, 2); //组合SKU的单价平均处理

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
                    }else {
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
    public function setRequest($data)
    {
        $callHeaderHttp = array('Content-Type: text/xml;charset=UTF-8', 'SOAPAction: ' . '"http://www.cdiscount.com/IMarketplaceAPIService/GetOrderList"');
        $tuCurl = curl_init();
        curl_setopt($tuCurl, CURLOPT_URL, "https://wsvc.cdiscount.com/MarketplaceAPIService.svc");
        curl_setopt($tuCurl, CURLOPT_VERBOSE, false);
        curl_setopt($tuCurl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($tuCurl, CURLOPT_HEADER, false);
        curl_setopt($tuCurl, CURLOPT_POST, true);
        curl_setopt($tuCurl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($tuCurl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($tuCurl, CURLOPT_HTTPHEADER, $callHeaderHttp);
        $tuData = curl_exec($tuCurl);

        curl_close($tuCurl);
        $xml = simplexml_load_string($tuData);

        return $xml->xpath('s:Body');;
    }

    public function returnTrack($tracking_info)
    {
        $return=[];

        $cd_token_id = $this->config['cd_token_id'];
        $data = '<?xml version="1.0" encoding="UTF-8"?>';
        $data .= '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">';
        $data .='<s:Body>';
        $data .='<ValidateOrderList xmlns="http://www.cdiscount.com">';
        $data .='<headerMessage xmlns:a="http://schemas.datacontract.org/2004/07/Cdiscount.Framework.Core.Communication.Messages" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">';

        $data .='<a:Context>';
        $data .='<a:CatalogID>1</a:CatalogID>';
        $data .='<a:CustomerPoolID>1</a:CustomerPoolID>';
        $data .='<a:SiteID>100</a:SiteID>';
        $data .='</a:Context>';

        $data .='<a:Localization>';
        $data .='<a:Country>Fr</a:Country>';
        $data .='<a:Currency>Eur</a:Currency>';
        $data .='<a:DecimalPosition>2</a:DecimalPosition>';
        $data .='<a:Language>Fr</a:Language>';
        $data .='</a:Localization>';

        $data .='<a:Security>';
        $data .='<a:DomainRightsList i:nil="true" />';
        $data .='<a:IssuerID i:nil="true" />';
        $data .='<a:SessionID i:nil="true" />';
        $data .='<a:SubjectLocality i:nil="true" />';
        $data .='<a:TokenId>'.$cd_token_id. '</a:TokenId>';
        $data .='<a:UserName i:nil="true" />';
        $data .='</a:Security>';

        $data .='<a:Version>1.0</a:Version>';
        $data .='</headerMessage>';

        $data .='<validateOrderListMessage xmlns:i="http://www.w3.org/2001/XMLSchema-instance">';
        $data .='<OrderList>';
        $data .='<ValidateOrder>';
        $data .='<CarrierName>' . $tracking_info['CarrierName'] . '</CarrierName>';
        $data .='<OrderLineList>';
        foreach ($tracking_info['products_info'] as $val) {
            $data .='<ValidateOrderLine>';
            $data .='<AcceptationState>ShippedBySeller</AcceptationState>';
            $data .='<ProductCondition>New</ProductCondition>';
            $data .='<SellerProductId>'.$val.'</SellerProductId>';
            $data .='</ValidateOrderLine>';
        }
        $data .='</OrderLineList>';
        $data .='<OrderNumber>' . $tracking_info['OrderNumber'] . '</OrderNumber>';
        $data .='<OrderState>Shipped</OrderState>';
        $data .='<TrackingNumber>' . $tracking_info['TrackingNumber'] . '</TrackingNumber>';
        $data .='<TrackingUrl>' . $tracking_info['TrackingUrl'] . '</TrackingUrl>';
        $data .='</ValidateOrder>';
        $data .='</OrderList>';
        $data .='</validateOrderListMessage>';

        $data .='</ValidateOrderList>';
        $data .='</s:Body>';
        $data .='</s:Envelope>';
        $callHeaderHttp = array('Content-Type: text/xml;charset=UTF-8', 'SOAPAction: ' . '"http://www.cdiscount.com/IMarketplaceAPIService/ValidateOrderList"');
        $tuCurl = curl_init();
        curl_setopt($tuCurl, CURLOPT_URL, "https://wsvc.cdiscount.com/MarketplaceAPIService.svc");
        curl_setopt($tuCurl, CURLOPT_VERBOSE, false);
        curl_setopt($tuCurl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($tuCurl, CURLOPT_HEADER, false);
        curl_setopt($tuCurl, CURLOPT_POST, true);
        curl_setopt($tuCurl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($tuCurl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($tuCurl, CURLOPT_HTTPHEADER, $callHeaderHttp);
        $tuData = curl_exec($tuCurl);
        curl_close($tuCurl);
        $xml = simplexml_load_string($tuData);
        $flag = 'false';
        if ($xml !=null ) {
            $body = $xml->xpath('s:Body');
            $flag = $body[0]->ValidateOrderListResponse->ValidateOrderListResult->ValidateOrderResults->ValidateOrderResult->Validated;
        }
        if((string) $flag){
            $return['status'] = true;
            $return['info'] = 'Success';
        }else{
            $return['status'] = false;
            $return['info'] = 'Error';
        }
        return $return;
    }

    public function getMessages(){
        return false;
    }
    public function sendMessages($replyMessage){
        
    }

}