<?php
namespace App\Modules\Channel\Adapter;

/**
 * Created by PhpStorm.
 * User: Vincent
 * Date: 16/5/17
 * Time: 下午2:54
 * modify:Norton 2016-6-27 11:13:39 增加获取亚马逊平台邮件 function
 */

use App\Models\Message\AccountModel;
use Google_Service_Gmail_Message;
use Google_Client;
use Google_Service_Gmail;
use Tool;
use App\Models\PackageModel;
use Google_Service_Gmail_ModifyMessageRequest;

Class AmazonAdapter implements AdapterInterface
{
    private $serviceUrl;
    private $signatureVersion = '2';
    private $signatureMethod = 'HmacSHA256';
    private $version = '2013-09-01';
    private $config = [];
    private $messageConfig = [];
    private $accountInfo = [];

    public function __construct($config)
    {
        $this->serviceUrl = $config['serviceUrl'];
        unset($config['serviceUrl']);
        $this->config = array_merge($config);
        $this->config['SignatureVersion'] = $this->signatureVersion;
        $this->config['SignatureMethod'] = $this->signatureMethod;
        $this->config['Version'] = $this->version;
        $this->messageConfig['GmailSecret'] = $config['GmailSecret'];
        $this->messageConfig['GmailToken'] = $config['GmailToken'];
        $this->messageConfig['account_id'] = $config['account_id'];
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

    /*****************************************************/

    public function requestReport()
    {
        $this->_config['Action'] = 'RequestReport';
        $this->_config['Version'] = '2009-01-01';
        $this->_config['ReportType'] = '_GET_FBA_MYI_ALL_INVENTORY_DATA_';
        $this->_config['MarketplaceId.Id.1'] = 'ATVPDKIKX0DER';
        $this->_config['SellerId'] = $this->config['SellerId'];
        $this->_config['AWSAccessKeyId'] = $this->config['AWSAccessKeyId'];
        $this->_config['SignatureVersion'] = '2';
        $this->_config['SignatureMethod'] = 'HmacSHA256';
        $this->_config['Timestamp'] = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
        $sign  = 'GET' . "\n";
        $sign .= "mws.amazonservices.com" . "\n";
        $sign .= "/" . "\n";
        $tmp_sigtoString = $this->signArrToString();
        $sign .= $tmp_sigtoString;
        $signature = hash_hmac("sha256", $sign, $this->config['AWS_SECRET_ACCESS_KEY'], true);
        $signature = urlencode(base64_encode($signature));
        $string = $tmp_sigtoString.'&Signature='.$signature;
        $string1 = $this->serviceUrl."/?".$string;

        $ch = curl_init($string1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
        $res = curl_exec($ch);
        curl_close($ch);
        $result_string = simplexml_load_string($res);
        $reportRequestId = $result_string->RequestReportResult->ReportRequestInfo->ReportRequestId;

        return (string)$reportRequestId;
    }

    public function getReportRequestList($id)
    {
        $this->_config['Action'] = 'GetReportRequestList';
        $this->_config['ReportRequestIdList.Id.1'] = $id;
        $this->_config['Version'] = '2009-01-01';
        $this->_config['MarketplaceId.Id.1'] = 'ATVPDKIKX0DER';
        $this->_config['SellerId'] = $this->config['SellerId'];
        $this->_config['AWSAccessKeyId'] = $this->config['AWSAccessKeyId'];
        $this->_config['SignatureVersion'] = '2';
        $this->_config['SignatureMethod'] = 'HmacSHA256';
        $this->_config['Timestamp'] = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
        $sign  = 'GET' . "\n";
        $sign .= "mws.amazonservices.com" . "\n";
        $sign .= "/" . "\n";
        $tmp_sigtoString = $this->signArrToString();
        $sign .= $tmp_sigtoString;
        $signature = hash_hmac("sha256", $sign, $this->config['AWS_SECRET_ACCESS_KEY'], true);
        $signature = urlencode(base64_encode($signature));
        $string = $tmp_sigtoString.'&Signature='.$signature;
        $string1 = $this->serviceUrl."/?".$string;

        $ch = curl_init($string1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
        $res = curl_exec($ch);
        curl_close($ch);
        $result_string = simplexml_load_string($res);
        $reportId = (string)$result_string->GetReportRequestListResult->ReportRequestInfo->GeneratedReportId;

        return $reportId;
    }

    public function getReport($id) 
    {
        $this->_config['Action'] = 'GetReport';
        $this->_config['ReportId'] = $id;
        $this->_config['Version'] = '2009-01-01';
        $this->_config['MarketplaceId.Id.1'] = 'ATVPDKIKX0DER';
        $this->_config['SellerId'] = $this->config['SellerId'];
        $this->_config['AWSAccessKeyId'] = $this->config['AWSAccessKeyId'];
        $this->_config['SignatureVersion'] = '2';
        $this->_config['SignatureMethod'] = 'HmacSHA256';
        $this->_config['Timestamp'] = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
        $sign  = 'GET' . "\n";
        $sign .= "mws.amazonservices.com" . "\n";
        $sign .= "/" . "\n";
        $tmp_sigtoString = $this->signArrToString();
        $sign .= $tmp_sigtoString;
        $signature = hash_hmac("sha256", $sign, $this->config['AWS_SECRET_ACCESS_KEY'], true);
        $signature = urlencode(base64_encode($signature));
        $string = $tmp_sigtoString.'&Signature='.$signature;
        $string1 = $this->serviceUrl."/?".$string;
        $ch = curl_init($string1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        $res = curl_exec($ch);
        curl_close($ch);
        
        return $res;
    }

    public function listInShipment($shipmentID)
    {
        $this->_config['Action'] = 'ListInboundShipments';
        $this->_config['AWSAccessKeyId'] = $this->config['AWSAccessKeyId'];
        $this->_config['SignatureVersion'] = '2';
        $this->_config['SignatureMethod'] = 'HmacSHA256';
        $this->_config['SellerId'] = $this->config['SellerId'];
        $this->_config['Timestamp'] = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
        $this->_config['ShipmentStatusList.member.1'] = 'CHECK_IN';
        $this->_config['ShipmentIdList.member.1'] = $shipmentID;
        $this->_config['Version'] = '2010-10-01';
        $this->serviceUrl = "https://mws.amazonservices.com/FulfillmentInboundShipment/2010-10-01?";
        $tmp_arr = parse_url($this->serviceUrl);
        $sign  = 'GET' . "\n";
        $sign .= $tmp_arr['host'] . "\n";
        $sign .= $tmp_arr['path'] . "\n";
        $tmp_sigtoString = $this->signArrToString();
        $sign .= $tmp_sigtoString;
        $signature = hash_hmac("sha256", $sign, $this->config['AWS_SECRET_ACCESS_KEY'], true);
        $signature = urlencode(base64_encode($signature));
        $string = $tmp_sigtoString.'&Signature='.$signature;
        $string1 = $this->serviceUrl.$string;
        $ch = curl_init($string1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
        $res = curl_exec($ch);
        curl_close($ch);
        $result_string = simplexml_load_string($res);

        return $result_string->ListInboundShipmentsResult->ShipmentData;
    }



    /*******************************************************/


    /**
     * [['package_id', 'trackingNum']]
     */
    public function returnTrack($tracking_info = '')
    {
        $productXml = $this->getXML($tracking_info, $this->config['SellerId']);
        $this->_config['Action'] = 'SubmitFeed';
        $this->_config['FeedType'] = '_POST_ORDER_FULFILLMENT_DATA_';
        $this->_config['Version'] = '2009-01-01';
        $this->_config['MarketplaceIdList.Id.1'] = 'ATVPDKIKX0DER';
        $this->_config['PurgeAndReplace'] = 'false';
        $this->_config['Merchant'] = $this->config['SellerId'];
        $this->_config['AWSAccessKeyId'] = $this->config['AWSAccessKeyId'];
        $this->_config['SignatureVersion'] = '2';
        $this->_config['SignatureMethod'] = 'HmacSHA256';
        $this->_config['Timestamp'] = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
        $tmp_arr = parse_url($this->serviceUrl);
        $sign  = 'POST' . "\n";
        $sign .= $tmp_arr['host'] . "\n";
        $sign .= "/" . "\n";
        $config = $this->_config;
        $url = array();
        foreach ($config as $key => $val) {
            $key = str_replace("%7E", "~", rawurlencode($key));
            $val = str_replace("%7E", "~", rawurlencode($val));
            $url[] = "{$key}={$val}";
        }
        sort($url);
        $tmp_sigtoString = implode('&', $url);
        $sign .= $tmp_sigtoString;
        $signature = hash_hmac("sha256", $sign, $this->config['AWS_SECRET_ACCESS_KEY'], true);
        $signature = urlencode(base64_encode($signature));
        $string = $tmp_sigtoString.'&Signature='.$signature;
        $tmp_header = ["Content-Type: text/xml", "Host: mws.amazonservices.com", "Content-MD5:".base64_encode(md5($this->testXML(), true))];
        $string1 = $tmp_url."/?".$string;
        $ch = curl_init($string1);
        curl_setopt($ch,CURLOPT_HEADER,true);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch,CURLOPT_POSTFIELDS,$productXml);
        curl_setopt($ch,CURLOPT_HTTPHEADER, $tmp_header);
        $res = curl_exec($ch);
        curl_close($ch);
        var_dump($res);
    }

    public function getXML($arr, $sellerId)
    {
        $str = "<?xml version='1.0' encoding='UTF-8'?>
    <AmazonEnvelope xsi:noNamespaceSchemaLocation='amzn-envelope.xsd' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'>
        <Header>
            <DocumentVersion>1.01</DocumentVersion>
            <MerchantIdentifier>".$sellerId."</MerchantIdentifier>
        </Header>
        <MessageType>OrderFulfillment</MessageType>";
        foreach($arr as $key => $value)
        {
            $package = PackageModel::where('id', $value[0])->first();
            if(!$package) {
                continue;
            }
            $CarrierName = $package->logistics->channelName()->where('channel_id', $package->channel_id)->first();
            if(!$CarrierName) {
                continue;
            }
            $CarrierName = $CarrierName->logistics_key;
            $amazonOrderId = $package->order->channel_ordernum;
            $object = $this->getOrderItems($amazonOrderId);
            $str .= $this->getSingleXML(($key+1),$object, $value[1], $amazonOrderId, $CarrierName);
        }
    
        $str .= "</AmazonEnvelope>";
        return $str;
    }

    //测试下就OK,大体框架出来了  CarriName   ShippingMethod两个值需要赋值
    public function getSingleXML($i, $object, $tracking_num, $amazonOrderId, $CarrierName)
    {
        $str = "<Message>
            <MessageID>".$i."</MessageID>
            <OrderFulfillment>
                <AmazonOrderID>".$amazonOrderId."</AmazonOrderID>
                <FulfillmentDate>".gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time())."</FulfillmentDate>
                <FulfillmentData>
                    <CarrierName>".$CarrierName."</CarrierName>
                    <ShippingMethod>e-package</ShippingMethod>
                    <ShipperTrackingNumber>".$tracking_num."</ShipperTrackingNumber>
                </FulfillmentData>";
        foreach($object as $key => $value) {
            $str .= "<Item>
                    <AmazonOrderItemCode>".$value['orderItemId']."</AmazonOrderItemCode>
                    <Quantity>".$value['quantity']."</Quantity>
                </Item>";
        }
        $str .= "</OrderFulfillment>
            </Message>";

        return $str;
    }

    /**
     * 将url参数数组转成url格式 
     *
     * @param none
     * @return string
     *
     */
    private function signArrToString()
    {
        $config = $this->_config;
        $url = array();
        foreach ($config as $key => $val) {
            $key = str_replace("%7E", "~", rawurlencode($key));
            $val = str_replace("%7E", "~", rawurlencode($val));
            $url[] = "{$key}={$val}";
        }
        sort($url);
        $string = implode('&', $url);
        return $string;
    }

    /**
     * 获取订单列表
     * @param $startDate
     * @param $endDate
     * @param array $status
     * @param int $perPage
     * @return array
     */
    public function listOrders($startDate, $endDate, $status = [], $perPage = 10, $nextToken = null)
    {
        $orders = [];
        $request = [];
        if ($nextToken) {
            $request['Action'] = 'ListOrdersByNextToken';
            $request['NextToken'] = $nextToken;
        } else {
            $request['Action'] = 'ListOrders';
            foreach ($status as $key => $value) {
                $request['OrderStatus.Status.' . ($key + 1)] = $value;
            }
            $request['MaxResultsPerPage'] = $perPage;
            $request['LastUpdatedAfter'] = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", strtotime($startDate));
            if ($endDate) {
                $request['LastUpdatedBefore'] = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", strtotime($endDate));
            }
        }
        $response = $this->setRequest('Orders', $request);
        if (isset($response->Error)) {
            return [
                'error' => [
                    'code' => (string)$response->Error->Code,
                    'message' => (string)$response->Error->Message
                ]
            ];
        } else {
            $responseOrders = $nextToken ? $response->ListOrdersByNextTokenResult : $response->ListOrdersResult;
            foreach ($responseOrders->Orders->Order as $order) {
                $orderItems = $this->getOrderItems($order->AmazonOrderId); //抓取订单行
                $orders[] = $this->parseOrder($order, $orderItems);
            }
            $nextToken = $responseOrders->NextToken;
            return ['orders' => $orders, 'nextToken' => $nextToken];
        }
    }

    /**
     * 获取订单行数据
     * @param $orderId
     * @return array
     */
    public function getOrderItems($orderId)
    {
        $items = [];
        $nextToken = null;
        do {
            if ($nextToken) {
                $request['Action'] = 'ListOrderItemsByNextToken';
                $request['NextToken'] = $nextToken;
            } else {
                $request['Action'] = 'ListOrderItems';
                $request['AmazonOrderId'] = $orderId;
            }
            $response = $this->setRequest('Orders', $request);
            if (isset($response->Error)) {
                Tool::show($response);
            }
            $responseOrderItems = $nextToken ? $response->ListOrderItemsByNextTokenResult : $response->ListOrderItemsResult;
            foreach ($responseOrderItems->OrderItems->OrderItem as $orderItem) {
                $items = array_merge($items, $this->parseOrderItem($orderItem));
            }
            $nextToken = $responseOrderItems->NextToken;
        } while ($nextToken);
        return $items;
    }

    /**
     * 解析返回订单
     * @param $order
     * @param $orderItems
     * @return array
     */
    public function parseOrder($order, $orderItems)
    {
        $shippingName = explode(' ', $order->ShippingAddress->Name);
        if ((string)$order->FulfillmentChannel == 'AFN') {
            $status = 'COMPLETE';
        } else {
            if ((string)$order->OrderStatus == 'Shipped') {
                $status = 'COMPLETE';
            } else {
                $status = 'PAID';
            }
        }
        $result = [
            'channel_ordernum' => (string)$order->AmazonOrderId,
            'email' => (string)$order->BuyerEmail,
            'amount' => (float)$order->OrderTotal->Amount,
            'currency' => (string)$order->OrderTotal->CurrencyCode,
            'status' => $status,
            'payment' => (string)$order->PaymentMethod,
            'shipping' => (string)$order->ShipmentServiceLevelCategory,
            'shipping_firstname' => isset($shippingName[0]) ? $shippingName[0] : '',
            'shipping_lastname' => isset($shippingName[1]) ? $shippingName[1] : '',
            'shipping_address' => (string)$order->ShippingAddress->AddressLine1,
            'shipping_address1' => (string)$order->ShippingAddress->AddressLine2,
            'shipping_city' => (string)$order->ShippingAddress->City,
            'shipping_state' => (string)$order->ShippingAddress->StateOrRegion,
            'shipping_country' => (string)$order->ShippingAddress->CountryCode,
            'shipping_zipcode' => (string)$order->ShippingAddress->PostalCode,
            'shipping_phone' => (string)$order->ShippingAddress->Phone,
            'payment_date' => (string)$order->PurchaseDate,
            'create_time' => (string)$order->PurchaseDate,
            'fulfill_by' => (string)$order->FulfillmentChannel,
            'items' => $orderItems
        ];
        return $result;
    }

    /**
     * 解析返回订单行
     * @param $orderItem
     * @return array
     */
    public function parseOrderItem($orderItem)
    {
        $items = [];
        $skus = Tool::filter_sku((string)$orderItem->SellerSKU); //根据账号的sku解析设定
        $total = $skus['skuNum'];
        unset($skus['skuNum']);
        foreach ($skus as $sku) {
            $item = [];
            $item['sku'] = $sku['erpSku'];
            $item['channel_sku'] = (string)$orderItem->SellerSKU;
            $item['price'] = (float)$orderItem->ItemPrice->Amount / $total;
            $item['quantity'] = (int)$orderItem->QuantityOrdered * $sku['qty'];
            $item['currency'] = (string)$orderItem->ItemPrice->CurrencyCode;
            $item['orderItemId'] = (string)$orderItem->OrderItemId;
            $items[] = $item;
        }
        return $items;
    }

    /**
     * 发送请求
     * @param $type
     * @param $request
     * @return \SimpleXMLElement
     */
    public function setRequest($type, $request)
    {
        $request['Timestamp'] = gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", time());
        $requestUrl = $this->setRequestUrl($type, $request);
        return simplexml_load_string(Tool::curl($requestUrl));
    }

    /**
     * 获取请求URL
     * @param $type
     * @param $request
     * @return string
     */
    public function setRequestUrl($type, $request)
    {
        $baseUrl = $this->serviceUrl . '/' . $type . '/' . $this->version;
        $requests = array_merge($this->config, $request);
        $requestParams = [];
        foreach ($requests as $key => $value) {
            $key = str_replace("%7E", "~", rawurlencode($key));
            $value = str_replace("%7E", "~", rawurlencode($value));
            $requestParams[] = "{$key}={$value}";
        }
        sort($requestParams);
        $paramUrl = implode('&', $requestParams);
        $signature = $this->getSignature($baseUrl, $paramUrl);
        return $baseUrl . '?' . $paramUrl . '&Signature=' . $signature;
    }

    /**
     * 获取签名
     * @param $baseUrl
     * @param $paramUrl
     * @return string
     */
    public function getSignature($baseUrl, $paramUrl)
    {
        $signatureArray = parse_url($baseUrl);
        $signatureString = 'GET' . "\n";
        $signatureString .= $signatureArray['host'] . "\n";
        $signatureString .= $signatureArray['path'] . "\n";
        $signatureString .= $paramUrl;
        $signature = hash_hmac("sha256", $signatureString, $this->config['AWS_SECRET_ACCESS_KEY'], true);
        $signature = urlencode(base64_encode($signature));
        return $signature;
    }

    public function getMessages()
    {
        // TODO: Implement getMessages() method.
        if (empty($this->messageConfig['GmailSecret']))
            return false;
        $get_labels = AccountModel::find($this->messageConfig['account_id'])->accountLabels->where('is_get_mail', 'get');
        if($get_labels->isEmpty())
            return false;
        $client = $this->getClient($this->messageConfig);
        $service = new Google_Service_Gmail($client);
        $user = 'me';
        $i = 0;
        $j = 0; //统计信息条数
        $nextPageToken = null;
        $returnAry = [];
        $count = 1;

        foreach($get_labels as $get_label){
            if($count >5)
                break;

            do {
                $count +=1;
                $i += 1;
                $lalel_name = $get_label->name;
                $messages = $service->users_messages->listUsersMessages($user,
                    [
                        'labelIds' => [$get_label->label_id, 'UNREAD'],
                        'pageToken' => $nextPageToken,
                        'maxResults' => 20,
                    ]
                );

//                $nextPageToken = $messages->nextPageToken;
                $nextPageToken = '';
                foreach ($messages as $key => $message) {
                    $j += 1;
                    //1 获取邮件信息
                    $messageContent = $service->users_messages->get($user, $message->id);
                    $messagePayload = $messageContent->getPayload();
                    $messageHeader = $this->parseMessageHeader($messagePayload->getHeaders());
                    $messageLabels = $messageContent->getLabelIds();

                    //2修改邮件账户的此邮件为已读状态
                    /*                $modify = new Google_Service_Gmail_ModifyMessageRequest();
                                    $modify->setRemoveLabelIds(['UNREAD']);
                                    $service->users_messages->modify($user, $message->id, $modify);*/

                    $returnAry[$j]['message_id'] = $messageContent->getId();
                    $returnAry[$j]['labels'] = serialize($messageLabels);
                    $returnAry[$j]['label'] = $lalel_name;
                    $returnAry[$j]['body'] = $messageLabels[0];

                    if (isset($messageHeader['From'])) {
                        $messageFrom = explode(' <', $messageHeader['From']);
                        if (count($messageFrom) > 1) {
                            $returnAry[$j]['from'] = $this->clearEmail(str_replace('>', '', $messageFrom[1]));
                            $returnAry[$j]['from_name'] = str_replace('"', '', $messageFrom[0]);
                        } else {
                            $returnAry[$j]['from'] = $this->clearEmail($messageHeader['From']);
                        }
                    }
                    if (isset($messageHeader['To'])) {
                        $messageTo = explode(' <', $messageHeader['To']);
                        if (count($messageTo) > 1) {
                            $returnAry[$j]['to'] = $this->clearEmail(str_replace('>', '', $messageTo[1]));
                        } else {
                            $returnAry[$j]['to'] = $this->clearEmail($messageHeader['To']);
                        }
                    }
                    $returnAry[$j]['date'] = isset($messageHeader['Date']) ? $messageHeader['Date'] : '';
                    $returnAry[$j]['subject'] = isset($messageHeader['Subject']) ? $messageHeader['Subject'] : '';
                    /**
                     * 处理附件并获取content
                     */
                    $tempPayLoad = '';
                    $tempAttachment = '';
                    $this->getPayloadNew($tempPayLoad, $tempAttachment, $messagePayload, $service, $message);
                    $returnAry[$j]['content'] =  $this->getMaillContent($tempPayLoad);
                    $returnAry[$j]['attachment'] = $tempAttachment;
                    $returnAry[$j]['channel_message_fields'] = '';
                }
            } while ($nextPageToken != '');
        }

        return $returnAry;
    }

    /**
     * 获取附件，上传附件
     * @param $data
     * @param $attachment
     * @param $payload
     * @param $service
     * @param $message
     */
    public function getPayloadNew(&$data, &$attachment, $payload, $service, $message)
    {

        if ($fileName = $payload->getFilename()) {
            $extraFile = $service->users_messages_attachments->get('me', $message->id,
                $payload->getBody()->getAttachmentId());

            if (!is_dir(config('message.attachmentPath') . '/' . $message->id)) {
                mkdir(config('message.attachmentPath') . '/' . $message->id, 0777);
            }

            $FileAry = explode('.', $fileName); //拆分文件名
            if(isset($FileAry[1])){
            $countSize = file_put_contents(config('message.attachmentPath') . $message->id . '/' . Tool::base64Encode($FileAry[0]) . '.' . $FileAry[1],
                Tool::base64Decode($extraFile->data));
            if ($countSize > 0) {
                $attachmentInfo = [
                    'file_name' => Tool::base64Encode($FileAry[0]) . '.' . $FileAry[1],
                    'file_path' => $message->id . '/' . Tool::base64Encode($FileAry[0]) . '.' . $FileAry[1], //图片目录
                ];
            }
            }
        } else {
            $attachmentInfo = '';
        }
        $data[] = [
            'mime_type' => $payload->getMimeType(),
            'body' => $payload->getBody()->getData(),
        ];
        if (!empty($attachmentInfo)) {
            $attachment [] = $attachmentInfo;
        }
        $mimeType = explode('/', $payload->getMimeType());
        if ($mimeType[0] == 'multipart') {
            foreach ($payload->getParts() as $part) {
                $this->getPayloadNew($data, $attachment, $part, $service, $message);
            }
        }
    }

    /**
     * 获取邮件内容
     * @param $parts
     * @return mixed|string
     */
    public function getMaillContent($parts)
    {
        $plainBody = '';
        foreach ($parts as $part) {
            if ($part['mime_type'] == 'text/html') {
                $htmlBody = Tool::base64Decode($part['body']);
                $htmlBody = preg_replace("/<(\/?body.*?)>/si", "", $htmlBody);
            }
            if ($part['mime_type'] == 'text/plain') {
                $plainBody .= nl2br(Tool::base64Decode($part['body']));
            }
        }
        $body = isset($htmlBody) && $htmlBody != '' ? $htmlBody : $plainBody;
        return base64_encode(serialize(['amazon' => $body]));
    }

    public function getClient($account)
    {
        $client = new Google_Client();
        $client->setScopes(implode(' ', array(
            Google_Service_Gmail::GMAIL_READONLY
        )));
        $client->setAuthConfig($account['GmailSecret']);
        $client->setAccessType('offline');
        $client->setAccessToken($account['GmailToken']);

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $client->refreshToken($client->getRefreshToken());
            $thisAccount = AccountModel::where('secret', $account['GmailSecret'])->first();
            $thisAccount->token = $client->getAccessToken();
            $thisAccount->save();
        }
        return $client;
    }

    public function parseMessageHeader($headers)
    {
        $result = [];

        foreach ($headers as $header) {
            $result[$header->getName()] = $header->getValue();
        }

        return $result;
    }

    public function clearEmail($email)
    {
        $email = str_replace('<', '', $email);
        $email = str_replace('>', '', $email);
        return $email;
    }

    public function sendMessages($replyMessage)
    {
        // TODO: Implement sendMessages() method.

        $account = AccountModel::find($this->messageConfig['account_id']);
        $client = $this->getSendClient($account);
        $service = new Google_Service_Gmail($client);
        $user = 'me';
        $from = $account->name;
        $fromEmail = $account->account;
        $to = $replyMessage->to ? $replyMessage->to : $replyMessage->message->from_name;
        $toEmail = $replyMessage->to_email ? $replyMessage->to_email : $replyMessage->message->from;
        $subject = $replyMessage->title;
        $content = nl2br($replyMessage->content);
        $message = new Google_Service_Gmail_Message();
        $message->setRaw($this->message($from, $fromEmail, $to, $toEmail, $subject, $content));
        $result = $service->users_messages->send($user, $message);
        $replyMessage->status = $result->id ? 'SENT' : 'FAIL';
        $replyMessage->save();
        if ($result->id) {
            //修改邮件账户的此邮件为已读状态
/*            $msg = $this->reply->message;
            if(! empty($msg)){
                $modify = new Google_Service_Gmail_ModifyMessageRequest();
                $modify->setRemoveLabelIds(['UNREAD']);
                $service->users_messages->modify($user, $msg->id, $modify);
            }*/
            return true;
        } else {
            return false;
        }
    }

    public function message($from, $fromEmail, $to, $toEmail, $subject, $content)
    {
        $message = 'From: =?utf-8?B?' . base64_encode($from) . '?= <' . $fromEmail . ">\r\n";
        $message .= 'To: =?utf-8?B?' . base64_encode($to) . '?= <' . $toEmail . ">\r\n";
        $message .= 'Subject: =?utf-8?B?' . base64_encode($subject) . "?=\r\n";
        $message .= "MIME-Version: 1.0\r\n";
        $message .= "Content-Type: text/html; charset=utf-8\r\n";
        $message .= 'Content-Transfer-Encoding: quoted-printable' . "\r\n\r\n";
        //$content=htmlspecialchars($content);
        $message .= $content . "\r\n";
        echo $message . "\r\n";
        return Tool::base64Encode($message);
    }

    public function getSendClient($account)
    {
        $client = new Google_Client();
        $client->setScopes(implode(' ', array(
            Google_Service_Gmail::GMAIL_MODIFY,
            Google_Service_Gmail::GMAIL_COMPOSE,
            Google_Service_Gmail::GMAIL_SEND
        )));
        $client->setAuthConfig($account['secret']);
        $client->setAccessType('offline');
        $client->setAccessToken($account['token']);

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $client->refreshToken($client->getRefreshToken());
            $account->token = $client->getAccessToken();
            $account->save();
        }
        return $client;
    }

    /**
     * 修改邮件账户的此邮件为已读状态
     * @param null $accountId
     * @param null $messageId
     * @return Google_Service_Gmail_Message
     */
    public function changeMessageStatus($accountId=null,$messageId=null){
        $account = AccountModel::find($accountId);
        $client = $this->getSendClient($account);
        $service = new Google_Service_Gmail($client);
        $user = 'me';

        $modify = new Google_Service_Gmail_ModifyMessageRequest();
        $modify->setRemoveLabelIds(['UNREAD']);
        $result = $service->users_messages->modify($user, $messageId, $modify);

        return $result;

    }

}