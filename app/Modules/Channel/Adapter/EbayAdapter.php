<?php
/**
 * Created by PhpStorm.
 * User: lilifeng
 * Date: 2016-06-01
 * Time: 10:53
 */

namespace App\Modules\Channel\Adapter;

use Tool;
use App\Models\OrderModel;
use App\Models\Message\Issues\EbayCasesListsModel;
use App\Models\Message\Issues\EbayCasesDetailsModel;
use App\Models\Publish\Ebay\EbayPublishProductModel;
use App\Models\CurrencyModel;


class EbayAdapter implements AdapterInterface
{

    private $requestToken;
    private $devID;
    private $appID;
    private $certID;
    private $serverUrl;
    private $compatLevel;
    private $siteID;
    private $verb;


    public function __construct($config)
    {
        $this->requestToken = $config["requestToken"];
        $this->devID = $config["devID"];
        $this->appID = $config["appID"];
        $this->certID = $config["certID"];
        $this->serverUrl = 'https://api.ebay.com/ws/api.dll';
        $this->compatLevel = '745';
        $this->accountID   = $config['accountID'];
        $this->accountName = $config['accountName'];

    }


    public function listOrders($startDate, $endDate, $status = [], $perPage = 10, $nextToken = '')
    {
        $returnOrders = [];
        $this->siteID = 0;
        $this->verb = 'GetOrders';
        $OrderStatus = $status;
        if (empty($nextToken)) {
            $nextToken = 1;
        }
        $requestXmlBody = $this->getListOrdersXml($startDate, $endDate, $OrderStatus, $perPage, $nextToken);
        $result = $this->sendHttpRequest($requestXmlBody);
        $response = simplexml_load_string($result);

        if (isset($response->OrderArray->Order) && !empty($response->OrderArray->Order)) {
            $orders = $response->OrderArray->Order;
            foreach ($orders as $order) {
                $reurnOrder = $this->parseOrder($order);
                if ($reurnOrder) {
                    $returnOrders[] = $reurnOrder;
                }
            }
            $nextToken++;
        } else {
            if(isset($response->Errors)){
                return  [
                    'error' => [
                        'code' => isset($response->Errors->ErrorCode)?(string)$response->Errors->ErrorCode:'',
                        'message' =>isset($response->Errors->ShortMessage)?(string)$response->Errors->ShortMessage:''
                    ]
                ];
            }
            $nextToken = '';
        }
        return ['orders' => $returnOrders, 'nextToken' => $nextToken];
    }


    public function getListOrdersXml($startDate, $endDate, $OrderStatus, $pageSize, $page)
    {
        $returnMustBe = 'OrderArray.Order.OrderID,';
        $returnMustBe .= 'OrderArray.Order.ShippingAddress.Name,';
        $returnMustBe .= 'OrderArray.Order.ShippingAddress.Street1,';
        $returnMustBe .= 'OrderArray.Order.ShippingAddress.Street2,';
        $returnMustBe .= 'OrderArray.Order.ShippingAddress.CityName,';
        $returnMustBe .= 'OrderArray.Order.ShippingAddress.StateOrProvince,';
        $returnMustBe .= 'OrderArray.Order.ShippingAddress.Country,';
        $returnMustBe .= 'OrderArray.Order.ShippingAddress.CountryName,';
        $returnMustBe .= 'OrderArray.Order.ShippingAddress.Phone,';
        $returnMustBe .= 'OrderArray.Order.ShippingAddress.PostalCode,';
        $returnMustBe .= 'OrderArray.Order.CheckoutStatus.LastModifiedTime,';
        $returnMustBe .= 'OrderArray.Order.CheckoutStatus.Status,';
        $returnMustBe .= 'OrderArray.Order.CheckoutStatus.eBayPaymentStatus,';
        $returnMustBe .= 'OrderArray.Order.BuyerCheckoutMessage,';
        $returnMustBe .= 'OrderArray.Order.ExternalTransaction.ExternalTransactionID,';
        $returnMustBe .= 'OrderArray.Order.ShippingDetails.SellingManagerSalesRecordNumber,';
        $returnMustBe .= 'OrderArray.Order.Total,';
        $returnMustBe .= 'OrderArray.Order.OrderStatus,';
        $returnMustBe .= 'OrderArray.Order.PaymentMethods,';
        $returnMustBe .= 'OrderArray.Order.CreatedTime,';
        $returnMustBe .= 'OrderArray.Order.BuyerUserID,';
        $returnMustBe .= 'OrderArray.Order.PaidTime,';
        $returnMustBe .= 'OrderArray.Order.ShippedTime,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.Buyer.Email,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.Item.ItemID,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.Item.SKU,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.Item.Site,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.Item.Title,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.Variation.SKU,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.Variation.VariationTitle,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.Variation.VariationViewItemURL,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.QuantityPurchased,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.Variation.VariationSpecifics.NameValueList,';//广告属性
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.Variation.VariationViewItemURL,';//广告地址
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.ShippingDetails.SellingManagerSalesRecordNumber,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.TransactionID,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.TransactionPrice,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.FinalValueFee,';
        $returnMustBe .= 'OrderArray.Order.ShippingServiceSelected.ShippingService,';
        $returnMustBe .= 'OrderArray.Order.ShippingServiceSelected.ShippingServiceCost,';
        $returnMustBe .= 'PageNumber,';
        $returnMustBe .= 'PaginationResult.TotalNumberOfEntries,';
        $returnMustBe .= 'PaginationResult.TotalNumberOfPages';
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8">' . "\n";
        $requestXmlBody .= '<GetOrdersRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
        $requestXmlBody .= '<DetailLevel>ReturnAll</DetailLevel>';
        $requestXmlBody .= '<RequesterCredentials>';
        $requestXmlBody .= '<eBayAuthToken>' . $this->requestToken . '</eBayAuthToken>';
        $requestXmlBody .= '</RequesterCredentials>';
        $requestXmlBody .= '<ErrorLanguage>zh_CN</ErrorLanguage>';
        //$requestXmlBody.= '<MessageID>' . $MessageID . '</MessageID>';
        $requestXmlBody .= '<OutputSelector>' . $returnMustBe . '</OutputSelector>';
        $requestXmlBody .= '<Version>745</Version>';
        $requestXmlBody .= '<WarningLevel>High</WarningLevel>';
        $requestXmlBody .= '<IncludeFinalValueFee>true</IncludeFinalValueFee>';
        $requestXmlBody .= '<ModTimeFrom>' . $startDate . '</ModTimeFrom>';
        $requestXmlBody .= '<ModTimeTo>' . $endDate . '</ModTimeTo>';
        $requestXmlBody .= '<OrderRole>Seller</OrderRole>';
        $requestXmlBody .= '<OrderStatus>' . $OrderStatus . '</OrderStatus>';
        $requestXmlBody .= '<Pagination>';
        $requestXmlBody .= '<EntriesPerPage>100</EntriesPerPage>';
        $requestXmlBody .= '<PageNumber>' . $page . '</PageNumber>';
        $requestXmlBody .= '</Pagination>';
        $requestXmlBody .= '</GetOrdersRequest>';
        return $requestXmlBody;
    }

    public function testBuHuo(){
        $this->siteID = 0;
        $this->verb = 'GetOrders';
        $returnMustBe = 'OrderArray.Order.OrderID,';
        $returnMustBe .= 'OrderArray.Order.ShippingAddress.Name,';
        $returnMustBe .= 'OrderArray.Order.ShippingAddress.Street1,';
        $returnMustBe .= 'OrderArray.Order.ShippingAddress.Street2,';
        $returnMustBe .= 'OrderArray.Order.ShippingAddress.CityName,';
        $returnMustBe .= 'OrderArray.Order.ShippingAddress.StateOrProvince,';
        $returnMustBe .= 'OrderArray.Order.ShippingAddress.Country,';
        $returnMustBe .= 'OrderArray.Order.ShippingAddress.CountryName,';
        $returnMustBe .= 'OrderArray.Order.ShippingAddress.Phone,';
        $returnMustBe .= 'OrderArray.Order.ShippingAddress.PostalCode,';
        $returnMustBe .= 'OrderArray.Order.CheckoutStatus.LastModifiedTime,';
        $returnMustBe .= 'OrderArray.Order.CheckoutStatus.Status,';
        $returnMustBe .= 'OrderArray.Order.CheckoutStatus.eBayPaymentStatus,';
        $returnMustBe .= 'OrderArray.Order.BuyerCheckoutMessage,';
        $returnMustBe .= 'OrderArray.Order.ExternalTransaction.ExternalTransactionID,';
        $returnMustBe .= 'OrderArray.Order.ShippingDetails.SellingManagerSalesRecordNumber,';
        $returnMustBe .= 'OrderArray.Order.Total,';
        $returnMustBe .= 'OrderArray.Order.OrderStatus,';
        $returnMustBe .= 'OrderArray.Order.PaymentMethods,';
        $returnMustBe .= 'OrderArray.Order.CreatedTime,';
        $returnMustBe .= 'OrderArray.Order.BuyerUserID,';
        $returnMustBe .= 'OrderArray.Order.PaidTime,';
        $returnMustBe .= 'OrderArray.Order.ShippedTime,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.Buyer.Email,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.Item.ItemID,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.Item.SKU,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.Item.Site,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.Item.Title,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.Variation.SKU,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.Variation.VariationTitle,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.Variation.VariationViewItemURL,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.QuantityPurchased,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.Variation.VariationSpecifics.NameValueList,';//广告属性
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.Variation.VariationViewItemURL,';//广告地址
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.ShippingDetails.SellingManagerSalesRecordNumber,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.TransactionID,';
        $returnMustBe .= 'OrderArray.Order.TransactionArray.Transaction.TransactionPrice,';
        $returnMustBe .= 'OrderArray.Order.ShippingServiceSelected.ShippingService,';
        $returnMustBe .= 'OrderArray.Order.ShippingServiceSelected.ShippingServiceCost,';
        $returnMustBe .= 'PageNumber,';
        $returnMustBe .= 'PaginationResult.TotalNumberOfEntries,';
        $returnMustBe .= 'PaginationResult.TotalNumberOfPages';
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8">' . "\n";
        $requestXmlBody .= '<GetOrdersRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
        $requestXmlBody .= '<DetailLevel>ReturnAll</DetailLevel>';
        $requestXmlBody .= '<RequesterCredentials>';
        $requestXmlBody .= '<eBayAuthToken>' . $this->requestToken . '</eBayAuthToken>';
        $requestXmlBody .= '</RequesterCredentials>';
        $requestXmlBody .= '<ErrorLanguage>zh_CN</ErrorLanguage>';
        //$requestXmlBody.= '<MessageID>' . $MessageID . '</MessageID>';
        $requestXmlBody .= '<OutputSelector>' . $returnMustBe . '</OutputSelector>';
        $requestXmlBody .= '<Version>745</Version>';
        $requestXmlBody .= '<WarningLevel>High</WarningLevel>';
        $requestXmlBody .= '<IncludeFinalValueFee>true</IncludeFinalValueFee>';
        $requestXmlBody .= '<CreateTimeFrom>2016-10-17 0:00:00</CreateTimeFrom>';
        $requestXmlBody .= '<CreateTimeTo>2016-10-17 10:00:00</CreateTimeTo>';
        $requestXmlBody .= '<OrderRole>Seller</OrderRole>';
        $requestXmlBody .= '<OrderStatus>All</OrderStatus>';
        $requestXmlBody .= '<Pagination>';
        $requestXmlBody .= '<EntriesPerPage>100</EntriesPerPage>';
        $requestXmlBody .= '<PageNumber>1</PageNumber>';
        $requestXmlBody .= '</Pagination>';
        $requestXmlBody .= '</GetOrdersRequest>';
        $result = $this->sendHttpRequest($requestXmlBody);
        $response = simplexml_load_string($result);

        var_dump($response);
        exit;
    }

    public function getOrder($orderID)
    {
        return $orderID;
    }

    /**
     * @param $tracking_info =[
     *              'IsUploadTrackingNumber' =>'' //true or false
     *              'ShipmentTrackingNumber'=>'' //追踪号
     *              'ShippingCarrierUsed'=>''//承运商
     *              'ShippedTime' =>'' //发货时间 date('Y-m-d\TH:i:s\Z')
     *              'ItemID' =>'' //商品id
     *              'TransactionID' =>'交易号'，
     * ]
     *
     * @return string
     */
    public function returnTrack($tracking_info)
    {
        $return = [];
        $xml = '';
        if ($tracking_info['IsUploadTrackingNumber']) { //需要上传追踪号
            $xml .= '<Shipment>';
            $xml .= '<ShipmentTrackingDetails>';
            $xml .= '<ShipmentTrackingNumber>' . $tracking_info['ShipmentTrackingNumber'] . '</ShipmentTrackingNumber>';
            $xml .= '<ShippingCarrierUsed>' . $tracking_info['ShippingCarrierUsed'] . '</ShippingCarrierUsed>';
            $xml .= '</ShipmentTrackingDetails>';
            $xml .= '<ShippedTime>' . $tracking_info['ShippedTime'] . '</ShippedTime>';
            $xml .= '</Shipment>';
        }
        $xml .= '<ItemID>' . $tracking_info['ItemID'] . '</ItemID>';
        $xml .= '<Shipped>true</Shipped>';
        $xml .= '<TransactionID>' . $tracking_info['TransactionID'] . '</TransactionID>';
        $result = $this->buildEbayBody($xml, 'CompleteSale');
        if ((string)$result->Ack == 'Success') {
            $return['status'] = true;
            $return['info'] = 'Success';
        } else {
            $return['status'] = false;
            $return['info'] = isset($result->LongMessage) ? (string)$result->LongMessage : 'error';
            //$return['info'] = '模拟标记失败';
        }
        return $return;
    }


    public function parseOrder($order)
    {


        $reurnOrder = array();
        $attr = $order->Total->attributes();
        $currencyID = (string)$attr['currencyID'];
        $isOrderStatus = $order->OrderStatus;
        $payMentStatus = $order->CheckoutStatus->eBayPaymentStatus;
        $paidTime = (string)$order->PaidTime;
        $ShippedTime = (string)$order->ShippedTime;
        $CreatedTime = date('Y-m-d H:i:s', strtotime((string)$order->CreatedTime));
        if (!empty($ShippedTime)) {
            return false; //这个已经发货了吧
        }
        if (empty($paidTime)) {
            $paidTime = '';
        } else {
            $paidTime = date('Y-m-d H:i:s', strtotime($paidTime));
        }
        if($CreatedTime<'2016-12-19 15:00:00'){
            return false; //跳过
        }

        //121864765676-1639850594002
        /*   $thisOrder = orderModel::where(['channel_ordernum' => (string)$order->OrderID])->where('status', '!=', 'UNPAID')->first();     //获取详情之前 进行判断是否存在 状态是未付款还是的继续

           if ($thisOrder) {
               return false;
           }*/
        /*  if((string)$order->OrderID=='121864765676-1639850594002'){
              $paidTime ='2016-06-02 09:00:00';
              echo '121864765676-1639850594002';
          }*/

        if (($isOrderStatus == 'Completed' && $payMentStatus == 'NoPaymentFailure') || !empty($paidTime)) {
            //正常订单
            $reurnOrder['status'] = 'PAID';
        } else {
            //未付款订单
            $reurnOrder['status'] = 'UNPAID';//没有付款的
        }

        $reurnOrder['currency'] = (string)$currencyID;
        $reurnOrder['channel_ordernum'] = (string)$order->OrderID;
        $reurnOrder['channel_listnum'] = isset($order->ShippingDetails->SellingManagerSalesRecordNumber) ? (string)$order->ShippingDetails->SellingManagerSalesRecordNumber : '';
        $reurnOrder['amount'] = (float)$order->Total;
        $reurnOrder['amount_shipping'] = (float)$order->ShippingServiceSelected->ShippingServiceCost;
        $reurnOrder['email'] = '';
        $reurnOrder['payment'] = (string)$order->PaymentMethods;
        $reurnOrder['shipping'] = (string)$order->ShippingServiceSelected->ShippingService;
        $reurnOrder['shipping_firstname'] = (string)$order->ShippingAddress->Name;
        $reurnOrder['shipping_lastname'] = '';
        $reurnOrder['shipping_address'] = (string)$order->ShippingAddress->Street1;
        $reurnOrder['shipping_address1'] = (string)$order->ShippingAddress->Street2;
        $reurnOrder['shipping_city'] = (string)$order->ShippingAddress->CityName;
        $reurnOrder['shipping_state'] = (string)$order->ShippingAddress->StateOrProvince;
        $reurnOrder['shipping_country'] = (string)$order->ShippingAddress->Country;
        //$reurnOrder['shipping_country_name'] =$order->ShippingAddress->CountryName;  //国家名字
        $reurnOrder['shipping_zipcode'] = (string)$order->ShippingAddress->PostalCode;
        $reurnOrder['shipping_phone'] = (string)$order->ShippingAddress->Phone;
        $reurnOrder['transaction_number'] = (string)$order->ExternalTransaction->ExternalTransactionID;
        $reurnOrder['payment_date'] = $paidTime;//支付时间
        $reurnOrder['aliexpress_loginId'] = (string)$order->BuyerUserID;
        $reurnOrder['by_id'] = (string)$order->BuyerUserID;
        $reurnOrder['customer_remark'] = isset($order->BuyerCheckoutMessage) ? (string)$order->BuyerCheckoutMessage : '';
        if (isset($order->TransactionArray->Transaction[0])) {
            foreach ($order->TransactionArray->Transaction as $sku) {
                $reurnOrder['email'] = (string)$sku->Buyer->Email == 'Invalid Request' ? '' : (string)$sku->Buyer->Email;
                $items = $this->parseItem($sku, $reurnOrder['currency'], $reurnOrder['channel_ordernum']);
                foreach ($items as $item) {
                    $item['currency'] = $reurnOrder['currency'];
                    $item['channel_order_id'] = $reurnOrder['channel_ordernum'];
                    $is_chinese = EbayPublishProductModel::where(['item_id'=>$item['orders_item_number'],'listing_type'=>'Chinese'])->first();
                    if(isset($is_chinese->id)){
                        $reurnOrder['is_chinese'] = 1;
                    }
                    $reurnOrder['items'][] = $item;
                }

            }
        } else {
            $reurnOrder['email'] = (string)$order->TransactionArray->Transaction->Buyer->Email == 'Invalid Request' ? '' : (string)$order->TransactionArray->Transaction->Buyer->Email;
            $items = $this->parseItem($order->TransactionArray->Transaction, $reurnOrder['currency'], $reurnOrder['channel_ordernum']);
            foreach ($items as $item) {
                $item['currency'] = $reurnOrder['currency'];
                $item['channel_order_id'] = $reurnOrder['channel_ordernum'];
                $is_chinese = EbayPublishProductModel::where(['item_id'=>$item['orders_item_number'],'listing_type'=>'Chinese'])->first();
                if(isset($is_chinese->id)){
                    $reurnOrder['is_chinese'] = 1;
                }
                $reurnOrder['items'][] = $item;
            }

        }
        return $reurnOrder;
    }


    public function parseItem($Transaction)
    {
        $items = [];
        $remark = '';
        if (isset($Transaction->Variation->SKU)) {
            $channel_sku = $Transaction->Variation->SKU;

            if (isset($Transaction->Variation->VariationSpecifics->NameValueList[0])) {

                foreach ($Transaction->Variation->VariationSpecifics->NameValueList as $NameValueList) {
                    $remark = $NameValueList->Name . ':' . $NameValueList->Value . ' |' . $remark;
                }
            } else {
                $remark = $Transaction->Variation->VariationSpecifics->NameValueList->Name . ':' . $Transaction->Variation->VariationSpecifics->NameValueList->Value;
            }
        } else {
            $channel_sku = $Transaction->Item->SKU;
        }
        $attr = $Transaction->FinalValueFee->attributes();
        $currency = CurrencyModel::where('code', (string)$attr['currencyID'])->first();
        $finalValueFee = (float)$Transaction->FinalValueFee/$currency->rate; //成交费转化成美元
        $erpSku = Tool::filter_sku((string)$channel_sku, 1); //根据账号的sku解析设定
        $allSkuNum = $erpSku['skuNum'];
        unset($erpSku['skuNum']);
        foreach ($erpSku as $sku) {
            $skuArray = [];
            $skuArray['channel_sku'] = (string)$channel_sku;
            $skuArray['sku'] = $sku['erpSku'];
            $skuArray['price'] = floatval($Transaction->TransactionPrice) / $allSkuNum;
            $skuArray['quantity'] = intval($Transaction->QuantityPurchased) * $sku['qty'];
            $skuArray['orders_item_number'] = (string)$Transaction->Item->ItemID;
            $skuArray['transaction_id'] = (string)$Transaction->TransactionID;
            $skuArray['remark'] = (string)$remark;
            $skuArray['final_value_fee'] = $finalValueFee*($sku['qty']/ $allSkuNum) ;
            $items[] = $skuArray;
        }
        return $items;


    }

    /**获取Ebay可用站点
     * @return array|bool
     */
    public function getEbaySite()
    {
        $return = [];
        $xml = '<DetailName>SiteDetails</DetailName>';
        $response = (array)$this->buildEbayBody($xml, 'GeteBayDetails', 0);
        if (isset($response['SiteDetails'])) {
            foreach ($response['SiteDetails'] as $key => $Site) {
                $return[$key]['site'] = (string)$Site->Site;
                $return[$key]['site_id'] = (int)$Site->SiteID;
                $return[$key]['detail_version'] = (int)$Site->DetailVersion;
            }

        } else {
            return false;
        }
        return $return;

    }

    /** 获取ebay站点的退货政策
     * @param $site 站点
     * @return array|bool
     */
    public function getEbayReturnPolicy($site)
    {
        $return = [];
        $xml = '<DetailName>ReturnPolicyDetails</DetailName>';
        $response = $this->buildEbayBody($xml, 'GeteBayDetails', $site);
        if ($response->Ack == 'Success') {
            if (isset($response->ReturnPolicyDetails->ReturnsWithin)) {
                $returnwishin_arr = [];
                foreach ($response->ReturnPolicyDetails->ReturnsWithin as $key => $returnwishin) {
                    $returnwishin_arr[] = (string)$returnwishin->ReturnsWithinOption;
                }
                $return['returns_with_in'] = json_encode($returnwishin_arr);
            }
            if (isset($response->ReturnPolicyDetails->ReturnsAccepted)) {
                $returnaccept_arr = [];
                foreach ($response->ReturnPolicyDetails->ReturnsAccepted as $key => $returnaccept) {
                    $returnaccept_arr[] = (string)$returnaccept->ReturnsAcceptedOption;
                }
                $return['returns_accepted'] = json_encode($returnaccept_arr);
            }


            if (isset($response->ReturnPolicyDetails->ShippingCostPaidBy)) {
                $shipcost_arr = [];
                foreach ($response->ReturnPolicyDetails->ShippingCostPaidBy as $shipcost) {
                    $shipcost_arr[] = (string)$shipcost->ShippingCostPaidByOption;
                }
                $return['shipping_costpaid_by'] = json_encode($shipcost_arr);
            }

            if (isset($response->ReturnPolicyDetails->Refund)) {
                $refund_arr = [];
                foreach ($response->ReturnPolicyDetails->Refund as $refund) {
                    $refund_arr[] = (string)$refund->RefundOption;
                }
                $return['refund'] = json_encode($refund_arr);
            }

            return $return;


        } else {
            return false;
        }

    }

    /**获取ebay对应站点国内国际运输方式
     * @param $site
     * @return array
     */
    public function getEbayShipping($site)
    {
        $return = [];
        $xml = '<DetailName>ShippingServiceDetails</DetailName>';
        $response = $this->buildEbayBody($xml, 'GeteBayDetails', $site);
        if ($response->Ack == 'Success') {
            $i = 0;
            foreach ($response->ShippingServiceDetails as $shipping) {
                $return[$i]['description'] = (string)$shipping->Description;
                $return[$i]['international_service'] = ((string)$shipping->InternationalService == 'true') ? 1 : 2; //1为国际 2为国内
                $return[$i]['shipping_service'] = (string)$shipping->ShippingService;
                $return[$i]['shipping_service_id'] = (int)$shipping->ShippingServiceID;
                $return[$i]['shipping_time_max'] = (int)$shipping->ShippingTimeMax;
                $return[$i]['shipping_time_min'] = (int)$shipping->ShippingTimeMin;
                $return[$i]['valid_for_selling_flow'] = ((string)$shipping->ValidForSellingFlow == 'true') ? 1 : 2; //1 api可以使用 2 api不可使用
                $return[$i]['shipping_category'] = (string)$shipping->ShippingCategory;
                $return[$i]['shipping_carrier'] = isset($shipping->ShippingCarrier) ? (string)$shipping->ShippingCarrier : '';
                $i++;
            }
        } else {
            return false;
        }

        return $return;

    }

    /** 获取对应站点分类
     * @param $level 分类级别
     * @param string $categoryParent 上级分类
     * @param int $site 站点
     * @return array|bool
     */
    public function getEbayCategoryList($level, $categoryParent = '', $site = 0)
    {
        $return = [];
        $xml = '<DetailLevel>ReturnAll</DetailLevel>';
        $xml .= '<LevelLimit>' . $level . '</LevelLimit>';
        if (!empty($categoryParent)) {
            $xml .= '<CategoryParent>' . $categoryParent . '</CategoryParent>';
        }
        $xml .= '<CategorySiteID>' . $site . '</CategorySiteID>';
        $response = $this->buildEbayBody($xml, 'GetCategories', $site);
        if ($response->Ack == 'Success') {
            foreach ($response->CategoryArray->Category as $category) {
                $data = [];
                $data['category_id'] = (int)$category->CategoryID;
                $data['best_offer'] = isset($category->BestOfferEnabled) ? (string)$category->BestOfferEnabled : '';
                $data['auto_pay'] = isset($category->AutoPayEnabled) ? (string)$category->AutoPayEnabled : '';
                $data['category_level'] = (int)$category->CategoryLevel;
                $data['category_name'] = (string)$category->CategoryName;
                $data['category_parent_id'] = (int)$category->CategoryParentID;
                $data['leaf_category'] = isset($category->LeafCategory) ? (string)$category->LeafCategory : '';
                $data['site'] = $site;
                $return[] = $data;
            }
        } else {
            return false;
        }
        return $return;

    }

    public function getStoreCategory($site=0){
        $return = [];
        $xml ='<LevelLimit>3</LevelLimit>';
        $response = $this->buildEbayBody($xml, 'GetStore', $site);
        if ($response->Ack == 'Success') {
            $category = $response->Store->CustomCategories->CustomCategory;
           // var_dump($response);exit;
            //先删除以前的
            foreach ($category as $v1) {

                $add_data=array();
                $add_data['store_category'] = (string)$v1->CategoryID;
                $add_data['store_category_name'] = (string)$v1->Name;
                $add_data['level'] = 1;
                $add_data['category_parent'] = '';
                $return[] = $add_data;
                if(isset($v1->ChildCategory)){
                    foreach($v1->ChildCategory as $v2){
                        $add_data=array();
                        $add_data['store_category'] = (string)$v2->CategoryID;
                        $add_data['store_category_name'] = (string)$v2->Name;
                        $add_data['level'] = 2;
                        $add_data['category_parent'] = (string)$v1->CategoryID;
                        $return[] = $add_data;
                        if(isset($v2->ChildCategory)){
                            foreach($v2->ChildCategory as $v3){
                                $add_data=array();
                                $add_data['store_category'] = (string)$v3->CategoryID;
                                $add_data['store_category_name'] = (string)$v3->Name;
                                $add_data['level'] = 3;
                                $add_data['category_parent'] = (string)$v2->CategoryID;
                                $return[] = $add_data;
                            }
                        }
                    }
                }
            }

        }else{
            return false;
        }
        return $return;
    }
    public function getSuggestedCategories($query,$site)
    {
        $return = [];
        $xml = '<Query>' . $query . '</Query>';
        $response = $this->buildEbayBody($xml, 'GetSuggestedCategories', $site);
        if ($response->Ack == 'Success') {
            $suggestedCategory = $response->SuggestedCategoryArray->SuggestedCategory;
            $i = 0;
            foreach($suggestedCategory as $key=> $suggest){
                $return[$i]['CategoryID']= (string)$suggest->Category->CategoryID;
                $return[$i]['Percent']= (string)$suggest->PercentItemFound;
                $i++;
            }

        }else{
            return false;
        }
        return $return;
    }

    public function getEbayCondition($category_id, $site)
    {
        $return = [];
        $xml = '<DetailLevel>ReturnAll</DetailLevel>
                <FeatureID>ConditionEnabled</FeatureID>
                <FeatureID>ConditionValues</FeatureID>
                <FeatureID>ItemSpecificsEnabled</FeatureID>
                <FeatureID>VariationsEnabled</FeatureID>
                <FeatureID>UPCEnabled</FeatureID>
                <FeatureID>EANEnabled</FeatureID>
                <FeatureID>ISBNEnabled</FeatureID>';
        $xml .= '<CategoryID>' . $category_id . '</CategoryID>';
        $xml .= '<CategorySiteID>' . $site . '</CategorySiteID>';
        $response = $this->buildEbayBody($xml, 'GetCategoryFeatures', $site);
        if ($response->Ack == 'Success') {
            $conditions = $response->Category->ConditionValues->Condition;
            foreach ($conditions as $condition) {
                $data = [];
                $data['condition_id'] = (int)$condition->ID;
                $data['condition_name'] = (string)$condition->DisplayName;
                $data['category_id'] = $category_id;
                $data['site'] = $site;
                $data['is_variations'] = isset($response->Category->VariationsEnabled) ? (string)$response->Category->VariationsEnabled : '';
                $data['is_condition'] = isset($response->Category->ConditionEnabled) ? (string)$response->Category->ConditionEnabled : '';
                $data['is_upc'] = isset($response->Category->UPCEnabled) ? (string)$response->Category->UPCEnabled : '';
                $data['is_ean'] = isset($response->Category->EANEnabled) ? (string)$response->Category->EANEnabled : '';
                $data['is_isbn'] = isset($response->Category->ISBNEnabled) ? (string)$response->Category->ISBNEnabled : '';
                $data['last_update_time'] = date('Y-m-d H:i:s', time());
                $return[] = $data;
            }
        } else {
            return false;
        }

        return $return;

    }

    public function getEbayCategorySpecifics($category_id, $site)
    {
        $return = [];
        $xml = '<CategorySpecific><CategoryID>' . $category_id . '</CategoryID></CategorySpecific>';
        $response = $this->buildEbayBody($xml, 'GetCategorySpecifics', $site);
        if ($response->Ack == 'Success') {
            foreach ($response->Recommendations->NameRecommendation as $v) {
                $data = [];
                $data['name'] = (string)$v->Name;
                $data['value_type'] = isset($v->ValidationRules->ValueType) ? (string)$v->ValidationRules->ValueType : '';
                $data['min_values'] = isset($v->ValidationRules->MinValues) ? (string)$v->ValidationRules->MinValues : '';
                $data['max_values'] = isset($v->ValidationRules->MaxValues) ? (string)$v->ValidationRules->MaxValues : '';
                $data['selection_mode'] = isset($v->ValidationRules->SelectionMode) ? (string)$v->ValidationRules->SelectionMode : '';
                $data['variation_specifics'] = isset($v->ValidationRules->VariationSpecifics) ? (string)$v->ValidationRules->VariationSpecifics : '';
                $data['last_update_time'] = date('Y-m-d H:i:s', time());
                $specific_values = array();
                foreach ($v->ValueRecommendation as $i_v) {
                    $specific_values[] = (string)$i_v->Value;
                }
                $data['specific_values'] = json_encode($specific_values);
                $data['category_id'] = $category_id;
                $data['site'] = $site;
                $return[] = $data;
            }
        } else {
            return false;
        }
        return $return;
    }

    /**验证和上架
     * @param $api
     * @param $data
     * @param $site
     */
    public function publish($api,$data,$site){
        $return = [];
        $xml = '<Item>';
        $xml .= '<Title>' . htmlspecialchars($data['title']) . '</Title>';
        if(!empty($data['sub_title'])){
            $xml .= '<SubTitle>'.htmlspecialchars($data['sub_title']) .'</SubTitle>';
        }
        $xml .= '<Site>'.$data['site_name'].'</Site>';
        $xml .= '<Currency>'.$data['currency'].'</Currency>';
        $xml .= '<SKU>'.$data['sku'].'</SKU>';
        $xml .= '<ListingDuration>'.$data['listing_duration'].'</ListingDuration>';
        $xml .= '<CategoryMappingAllowed>true</CategoryMappingAllowed>'; //是否允许多分类
        $xml .='<PrimaryCategory><CategoryID>'.$data['primary_category'].'</CategoryID></PrimaryCategory>';
        if(!empty( $data['secondary_category'])){
            $xml .= '<SecondaryCategory><CategoryID>' . $data['secondary_category'] . '</CategoryID></SecondaryCategory>';
        }
        $xml .= '<ConditionID>'.$data['condition_id'].'</ConditionID>';
        if(!empty($data['condition_description'])){
            $xml .= '<ConditionDescription>'.trim($data['condition_description']).'</ConditionDescription>';
        }
        if($data['private_listing']){
            $xml .= '<PrivateListing>ture</PrivateListing>';
        }
        $xml .= '<PaymentMethods>PayPal</PaymentMethods>';  // 付款方式 - 暂时只支持paypal
        $xml .= '<PayPalEmailAddress>'.$data['paypal_email_address'] .'</PayPalEmailAddress>';
        if (!empty($data['picture_details'])) //ebay图片
        {
            $xml .= '<PictureDetails>';
            $picture = json_decode($data['picture_details'], true);
            $i=0;
            foreach ($picture as $value) {
                if($i==12){break;}
                if($i==1&&($data['site_name']=='Italy'||$data['site_name']=='France')){//意大利和法国只上传一张主图片 09.08
                    break;
                }
                $value =  str_replace(" ", "%20",$value);
                $xml .= '<PictureURL>'.($value).'</PictureURL>';
                $i++;
            }
            $xml .= '</PictureDetails>';
        }
        if (!empty($data['country'])) //产品所在的国家
        {
            $xml .= '<Country>'.$data['country'].'</Country>';
        }
        if (!empty($data['location']))  //地点
        {
            $xml .= '<Location>' . $data['location'] . '</Location>';
        }
        if (!empty($data['postal_code']))  //邮编
        {
            $xml .= '<PostalCode>' . $data['postal_code'] . '</PostalCode>';
        }
        $productListingDetails = '';
        if(!empty($data['item_specifics'])){
            $xml .= '<ItemSpecifics>';
            $item_specifics = json_decode($data['item_specifics'],true);
            foreach($item_specifics as $key=>$value){
                if(!empty($value)){
                    if((strtoupper($key)=='UPC')||(strtoupper($key)=='EAN')||(strtoupper($key)=='ISBN')){
                        $productListingDetails .= '<'.strtoupper($key).'>'.$value.'</'.strtoupper($key).'>';
                        continue;
                    }
                    $xml .= '<NameValueList><Name>'.htmlspecialchars($key).'</Name><Value>'.htmlspecialchars($value).'</Value></NameValueList>';
                }
            }
            $xml .= '</ItemSpecifics>';
        }
        if(!empty($productListingDetails)){ //UPC EAN ISBN
            $xml .='<ProductListingDetails>'.$productListingDetails.'</ProductListingDetails>';
        }

        $buyerRequirementDetails = '';
        $buyer_requirement = json_decode($data['buyer_requirement'], true);

        //买家要求
        if (($buyer_requirement['LinkedPayPalAccount'])&&isset($buyer_requirement['LinkedPayPalAccount'])) {
            $buyerRequirementDetails .= '<LinkedPayPalAccount>true</LinkedPayPalAccount>'; //只支持PAYPAL付款
        }
        if (($buyer_requirement['ShipToRegistrationCountry'])&&isset($buyer_requirement['ShipToRegistrationCountry'])) {
            $buyerRequirementDetails .= '<ShipToRegistrationCountry>true</ShipToRegistrationCountry>'; //排除运输范围之外的国家
        }
        if (isset($buyer_requirement['unpaid_on'])&&$buyer_requirement['unpaid_on']) {
            $buyerRequirementDetails .= '<MaximumUnpaidItemStrikesInfo><Count>' . $buyer_requirement['MaximumUnpaidItemStrikesInfo']['Count'] . '</Count><Period>' . $buyer_requirement['MaximumUnpaidItemStrikesInfo']['Period'] . '</Period></MaximumUnpaidItemStrikesInfo>';
        }
        if (isset($buyer_requirement['policy_on'])&&$buyer_requirement['policy_on']) {
            $buyerRequirementDetails .= '<MaximumBuyerPolicyViolations><Count>' . $buyer_requirement['MaximumBuyerPolicyViolations']['Count'] . '</Count><Period>' . $buyer_requirement['MaximumBuyerPolicyViolations']['Period'] . '</Period></MaximumBuyerPolicyViolations>';
        }
        if (isset($buyer_requirement['feedback_on'])&&$buyer_requirement['feedback_on']) {
            $buyerRequirementDetails .= '<MinimumFeedbackScore>' . $buyer_requirement['MinimumFeedbackScore']. '</MinimumFeedbackScore>';  //信用低于
        }
        if (isset($buyer_requirement['item_count_on'])&&$buyer_requirement['item_count_on']) {
            $buyerRequirementDetails .= '<MaximumItemRequirements><MaximumItemCount>' . $buyer_requirement['MaximumItemRequirements']['MaximumItemCount'].'</MaximumItemCount><MinimumFeedbackScore>' . $buyer_requirement['MaximumItemRequirements']['MinimumFeedbackScore'].'</MinimumFeedbackScore></MaximumItemRequirements>';
        }

        if(!empty($buyerRequirementDetails)){
            $xml .='<BuyerRequirementDetails>'.$buyerRequirementDetails.'</BuyerRequirementDetails>';
        }

        $returnPolicy = '';
        $return_policy = json_decode($data['return_policy'],true);
        //退货政策
        if ($return_policy['ReturnsAcceptedOption'] == 'ReturnsAccepted')   //退货政策  接受的情况下，
        {
            $returnPolicy .= '<ReturnsAcceptedOption>'.$return_policy['ReturnsAcceptedOption'].'</ReturnsAcceptedOption>';
            if (!empty($return_policy['ReturnsWithinOption'])) {
                $returnPolicy .= ' <ReturnsWithinOption>' . $return_policy['ReturnsWithinOption'] . '</ReturnsWithinOption>'; //退货天数
            }
            if (!empty($return_policy['RefundOption'])) {
                $returnPolicy .= '<RefundOption>' . $return_policy['RefundOption'] . '</RefundOption>';  //退货方式  一些站点没有这个标签
            }
            if (!empty($return_policy['ShippingCostPaidByOption'])) {
                $returnPolicy .= '<ShippingCostPaidByOption>'.$return_policy['ShippingCostPaidByOption'] . '</ShippingCostPaidByOption>'; // 退货费用谁承担
            }

            if ($return_policy['ExtendedHolidayReturns']) {
                $returnPolicy .= '<ExtendedHolidayReturns>true</ExtendedHolidayReturns>'; //节假日延迟
            }
            if (!empty($returnPolicy['Description'])) {
                $returnPolicy .= '<Description>' . trim($return_policy['Description']) . '</Description>'; // 退货详情
            }
        }
        if(!empty($returnPolicy)){
            $xml .='<ReturnPolicy>'.$returnPolicy.'</ReturnPolicy>';
        }

        $xml .= '<DispatchTimeMax>'.$data['dispatch_time_max'].'</DispatchTimeMax>'; //  对应的发货天数

        $xml .= '<ShippingDetails>';
        $shippingServiceOptions = json_decode($data['shipping_details'],true);
        foreach($shippingServiceOptions['Shipping'] as $key=>$value){
            if( empty($value['ShippingService'])){
                continue;
            }
            $xml .= '<ShippingServiceOptions>';
            $xml .= '<ShippingServicePriority>'.$key.'</ShippingServicePriority>';
            $xml .= ' <ShippingService>' .$value['ShippingService'] . '</ShippingService>';  //国内运输方式
            if(empty($value['ShippingServiceCost'])&&(empty($value['ShippingServiceAdditionalCost']))){
                $xml .= '<FreeShipping>true</FreeShipping>'; //是否免费
            }else{
                if (!empty($value['ShippingServiceCost'])) {
                    $xml .= '<ShippingServiceCost crenccuyID="'.$data['currency'].'">' .$value['ShippingServiceCost']. '</ShippingServiceCost>';//基本运费
                }
                if (!empty($value['ShippingServiceAdditionalCost'])) {
                    $xml .= '<ShippingServiceAdditionalCost crenccuyID="'.$data['currency'].'">' .$value['ShippingServiceAdditionalCost']. '</ShippingServiceAdditionalCost>'; //额外加收
                }
            }
            $xml .= '</ShippingServiceOptions>';
        }

        foreach($shippingServiceOptions['InternationalShipping'] as $key=>$value){
            if( empty($value['ShippingService'])){
                continue;
            }
            $xml .= '<InternationalShippingServiceOption>';
            $xml .= '<ShippingServicePriority>'.$key.'</ShippingServicePriority>'; //国际运输 顺序
            $xml .= '<ShippingService>'.$value['ShippingService'].'</ShippingService>'; //国际运输方式
            $xml .= '<ShippingServiceCost crenccuyID="' . $data['currency'] . '" >'.$value['ShippingServiceCost'].'</ShippingServiceCost>'; // 费用
            $xml .= '<ShippingServiceAdditionalCost crenccuyID="'.$data['currency']. '" >'.$value['ShippingServiceAdditionalCost'].'</ShippingServiceAdditionalCost>';// 额外加收
            if(!empty($value['ShipToLocation'])){
                foreach($value['ShipToLocation'] as $v){
                    if(!empty($v)){
                        $xml .= '<ShipToLocation>'.$v.'</ShipToLocation>';

                    }
                }
            }
            $xml .= '</InternationalShippingServiceOption>';
        }
        if(!empty($shippingServiceOptions['ExcludeShipToLocation'])){
            foreach($shippingServiceOptions['ExcludeShipToLocation'] as $v){
                $xml .= '<ExcludeShipToLocation>' . $v . '</ExcludeShipToLocation>';
            }
        }
        $xml .= '</ShippingDetails>';

        if(!empty($data['store_category_id'])){
            $xml .='<Storefront>';
            $xml .='<StoreCategoryID>'.$data['store_category_id'].'</StoreCategoryID>';
            $xml .='</Storefront>';
        }

        if ($data['listing_type'] == 'Chinese') {
            $xml .= '<ListingType>Chinese</ListingType>';
            $xml .= '<StartPrice currencyID="' . $data['currency'].'">'.$data['start_price'].'</StartPrice>';
            $xml .= '<ReservePrice  currencyID="' . $data['currency'] . '">0.00</ReservePrice>';
            $xml .= '<BuyItNowPrice currencyID="' . $data['currency'] . '">0.00</BuyItNowPrice>';
            $xml .= '<Quantity>'.(int)$data['quantity'].'</Quantity>';
        }
        if ($data['listing_type'] == 'FixedPriceItem'&&$data['multi_attribute']==0) {
            $xml .= '<ListingType>FixedPriceItem</ListingType>';
            $xml .= '<StartPrice currencyID="' .$data['currency']. '">'.$data['start_price'].'</StartPrice>';
            $xml .= '<Quantity>' . $data['quantity'] . '</Quantity>';
        }
        if ($data['listing_type'] == 'FixedPriceItem'&&$data['multi_attribute']==1) {
            $xml .= '<ListingType>FixedPriceItem</ListingType>';
            $variation_specifics = json_decode($data['variation_specifics'],true);
            if(!empty($variation_specifics)){
                $xml .= '<Variations>';
                $variationSpecificsSet = '';
                foreach($variation_specifics as $key => $value){
                    if((strtoupper($key)!='UPC')&&(strtoupper($key)!='EAN')&&(strtoupper($key)!='ISBN')) {
                        $variationSpecificsSet .= '<NameValueList><Name>' . $key . '</Name>';
                        foreach($value as $v){
                            $variationSpecificsSet .= '<Value>'.$v .'</Value>';
                        }
                        $variationSpecificsSet .= '</NameValueList>';
                    }
                }
                if(!empty($variationSpecificsSet))
                $xml .= '<VariationSpecificsSet>'.$variationSpecificsSet.'</VariationSpecificsSet>';
            }

            foreach($data['sku_detail'] as $key => $value){
                $xml .= '<Variation>';
                $xml .= '<SKU>' . $value['sku'] . '</SKU>';
                $xml .= '<StartPrice >' . $value['start_price'] . '</StartPrice>';
                $xml .= '<Quantity>' . $value['quantity']. '</Quantity>';
                $variationSpecifics =  '';
                if(!empty($variation_specifics)) {
                    foreach($variation_specifics as $k => $v) {
                        if((strtoupper($k)=='UPC')||(strtoupper($k)=='EAN')||(strtoupper($k)=='ISBN')) {
                            $xml .= '<VariationProductListingDetails>';
                            $xml .= '<'.strtoupper($k).'>' . $variation_specifics[strtoupper($k)][$key] . '</'.strtoupper($k).'>';
                            $xml .= '</VariationProductListingDetails>';
                        }else{
                            $variationSpecifics .= '<NameValueList>';
                            $variationSpecifics .= '<Name>' . $k . '</Name>';
                            $variationSpecifics .= '<Value>'.$variation_specifics[$k][$key].'</Value>';
                            $variationSpecifics .= '</NameValueList>';
                        }
                    }
                }
                if(!empty($variationSpecifics)){
                    $xml .='<VariationSpecifics>'.$variationSpecifics.'</VariationSpecifics>';
                }
                $xml .= '</Variation>';
            }
            $variation_picture = json_decode($data['variation_picture'],true);
            if(!empty($variation_picture)){
                foreach($variation_picture as $key=>$value){
                    $xml .= '<Pictures>';
                    $xml .= '<VariationSpecificName>' .$key . '</VariationSpecificName>';
                    foreach($value as $k=>$v){
                        $xml .= '<VariationSpecificPictureSet><VariationSpecificValue>' . $k . '</VariationSpecificValue>';
                        $v =  str_replace(" ", "%20",$v);
                        $xml .= ' <PictureURL>' . ($v) . '</PictureURL>';
                        $xml .= '</VariationSpecificPictureSet>';
                    }
                    $xml .= '</Pictures>';

                }
            }
            $xml .= '</Variations>';
        }
        $xml .= '<Description><![CDATA[' . (trim(($data['description']))) .']]></Description>';  //将描述部分 设置完了再传进来
        $xml .= '<OutOfStockControl>true</OutOfStockControl>'; //无货在线
        $xml .= '</Item>';
        $response = $this->buildEbayBody($xml, $api, $site);
        if(isset($response->ItemID)){
            $return['is_success'] =true;
            if($api=='VerifyAddItem'||$api=='VerifyAddFixedPriceItem'){
                $fee = 0;
                foreach($response->Fees->Fee as $value){
                    $fee = $fee + (float)$value->Fee;
                }
                $return['info'] =$fee;
            }else{
                $return['info'] =(string)$response->ItemID;
            }
        }else{
            $return['is_success'] =false;
            $errorInfo = [];
            foreach($response->Errors as $error){
                if((string)$error->SeverityCode =='Error'){
                    $info_String = (string)$error->LongMessage;
                    if(isset($error->ErrorParameters->Value)){
                        $info_String = $info_String.((string)$error->ErrorParameters->Value);
                    }
                    $errorInfo[]=$info_String;
                }
            }
            $errorInfo=  array_unique($errorInfo);
            $errorInfo =implode(',',$errorInfo);
            $return['info'] =strip_tags($errorInfo);
            //$return['info'] ='测试错误情况下';
        }
        $return['info_all'] =var_export($response,true);
        return $return;
    }


    public function ReviseItem($api,$param,$data,$site){
        $return = [];
        $xml ='<Item>';
        $xml .= '<ItemID>'.$data['item_id'].'</ItemID>';
        if($param=='changeSku'){
            if(isset($data['sku_detail'])){
                $xml .= '<Variations>';
                foreach($data['delete'] as $sku){
                    $xml .='<Variation>';
                    $xml .='<Delete>true</Delete>';
                    $xml .='<SKU>'.$sku.'</SKU>';
                    $xml .='</Variation>';
                }
                $variation_specifics = json_decode($data['variation_specifics'],true);
                /*      if(!empty($variation_specifics)){

                          $variationSpecificsSet = '';
                          foreach($variation_specifics as $key => $value){
                              if((strtoupper($key)!='UPC')&&(strtoupper($key)!='EAN')&&(strtoupper($key)!='ISBN')) {
                                  $variationSpecificsSet .= '<NameValueList><Name>' . $key . '</Name>';
                                  foreach($value as $v){
                                      $variationSpecificsSet .= '<Value>'.$v .'</Value>';
                                  }
                                  $variationSpecificsSet .= '</NameValueList>';
                              }
                          }
                          if(!empty($variationSpecificsSet))
                              $xml .= '<VariationSpecificsSet>'.$variationSpecificsSet.'</VariationSpecificsSet>';
                      }*/

                foreach($data['sku_detail'] as $key => $value){
                    $xml .= '<Variation>';
                    $xml .= '<SKU>' . $value['sku'] . '</SKU>';
                    $xml .= '<StartPrice >' . $value['start_price'] . '</StartPrice>';
                    $xml .= '<Quantity>' . $value['quantity']. '</Quantity>';
                    $variationSpecifics =  '';
                    if(!empty($variation_specifics)) {
                        foreach($variation_specifics as $k => $v) {
                            if((strtoupper($k)=='UPC')||(strtoupper($k)=='EAN')||(strtoupper($k)=='ISBN')) {
                                $xml .= '<VariationProductListingDetails>';
                                $xml .= '<'.strtoupper($k).'>' . $variation_specifics[strtoupper($k)][$key] . '</'.strtoupper($k).'>';
                                $xml .= '</VariationProductListingDetails>';
                            }else{
                                $variationSpecifics .= '<NameValueList>';
                                $variationSpecifics .= '<Name>' . $k . '</Name>';
                                $variationSpecifics .= '<Value>'.$variation_specifics[$k][$key].'</Value>';
                                $variationSpecifics .= '</NameValueList>';
                            }
                        }
                    }
                    if(!empty($variationSpecifics)){
                        $xml .='<VariationSpecifics>'.$variationSpecifics.'</VariationSpecifics>';
                    }
                    $xml .= '</Variation>';
                }
                $variation_picture = json_decode($data['variation_picture'],true);
                if(!empty($variation_picture)){
                    foreach($variation_picture as $key=>$value){
                        $xml .= '<Pictures>';
                        $xml .= '<VariationSpecificName>' .$key . '</VariationSpecificName>';
                        foreach($value as $k=>$v){
                            $xml .= '<VariationSpecificPictureSet><VariationSpecificValue>' . $k . '</VariationSpecificValue>';
                            $v =  str_replace(" ", "%20",$v);
                            $xml .= ' <PictureURL>' . ($v) . '</PictureURL>';
                            $xml .= '</VariationSpecificPictureSet>';
                        }
                        $xml .= '</Pictures>';

                    }
                }
                $xml .= '</Variations>';
            }else{
                $xml .= '<SKU>'.$data['sku'].'</SKU>';
                $xml .= '<StartPrice currencyID="' .$data['currency']. '">'.$data['start_price'].'</StartPrice>';
                $xml .= '<Quantity>' . $data['quantity'] . '</Quantity>';
            }
        }
        if($param=='changeTitle'){
            $xml .= '<Title>' . htmlspecialchars($data['title']) . '</Title>';
            if(!empty($data['sub_title'])){
                $xml .= '<SubTitle>'.htmlspecialchars($data['sub_title']) .'</SubTitle>';
            }
        }
        if($param=='changeDescription'){
            $xml .= '<Description><![CDATA['.trim($data['description']).']]></Description><DescriptionReviseMode>Replace</DescriptionReviseMode>';
        }
        if($param=='changeShipping'){
            $xml .= '<ShippingDetails>';
            $shippingServiceOptions = json_decode($data['shipping_details'],true);
            foreach($shippingServiceOptions['Shipping'] as $key=>$value){
                if( empty($value['ShippingService'])){
                    continue;
                }
                $xml .= '<ShippingServiceOptions>';
                $xml .= '<ShippingServicePriority>'.$key.'</ShippingServicePriority>';
                $xml .= ' <ShippingService>' .$value['ShippingService'] . '</ShippingService>';  //国内运输方式
                if(empty($value['ShippingServiceCost'])&&(empty($value['ShippingServiceAdditionalCost']))){
                    $xml .= '<FreeShipping>true</FreeShipping>'; //是否免费
                }else{
                    if (!empty($value['ShippingServiceCost'])) {
                        $xml .= '<ShippingServiceCost crenccuyID="'.$data['currency'].'">' .$value['ShippingServiceCost']. '</ShippingServiceCost>';//基本运费
                    }
                    if (!empty($value['ShippingServiceAdditionalCost'])) {
                        $xml .= '<ShippingServiceAdditionalCost crenccuyID="'.$data['currency'].'">' .$value['ShippingServiceAdditionalCost']. '</ShippingServiceAdditionalCost>'; //额外加收
                    }
                }
                $xml .= '</ShippingServiceOptions>';
            }

            foreach($shippingServiceOptions['InternationalShipping'] as $key=>$value){
                if( empty($value['ShippingService'])){
                    continue;
                }
                $xml .= '<InternationalShippingServiceOption>';
                $xml .= '<ShippingServicePriority>'.$key.'</ShippingServicePriority>'; //国际运输 顺序
                $xml .= '<ShippingService>'.$value['ShippingService'].'</ShippingService>'; //国际运输方式
                $xml .= '<ShippingServiceCost crenccuyID="' . $data['currency'] . '" >'.$value['ShippingServiceCost'].'</ShippingServiceCost>'; // 费用
                $xml .= '<ShippingServiceAdditionalCost crenccuyID="'.$data['currency']. '" >'.$value['ShippingServiceAdditionalCost'].'</ShippingServiceAdditionalCost>';// 额外加收
                if(!empty($value['ShipToLocation'])){
                    foreach($value['ShipToLocation'] as $v){
                        if(!empty($v)){
                            $xml .= '<ShipToLocation>'.$v.'</ShipToLocation>';

                        }
                    }
                }
                $xml .= '</InternationalShippingServiceOption>';
            }
            if(!empty($shippingServiceOptions['ExcludeShipToLocation'])){
                foreach($shippingServiceOptions['ExcludeShipToLocation'] as $v){
                    $xml .= '<ExcludeShipToLocation>' . $v . '</ExcludeShipToLocation>';
                }
            }
            $xml .= '</ShippingDetails>';

        }
        if($param=='changePicture'){
            if (!empty($data['picture_details'])) //ebay图片
            {
                $xml .= '<PictureDetails>';
                $picture = json_decode($data['picture_details'], true);
                $i=0;
                foreach ($picture as $value) {
                    if($i==12){break;}
                    if($i==1&&($data['site_name']=='Italy'||$data['site_name']=='France')){//意大利和法国只上传一张主图片 09.08
                        break;
                    }
                    $value =  str_replace(" ", "%20",$value);
                    $xml .= '<PictureURL>'.($value).'</PictureURL>';
                    $i++;
                }
                $xml .= '</PictureDetails>';
            }
        }
        if($param=='changeSpecifics'){


            $xml .= '<ConditionID>'.$data['condition_id'].'</ConditionID>';
            if(!empty($data['condition_description'])){
                $xml .= '<ConditionDescription>'.trim($data['condition_description']).'</ConditionDescription>';
            }

            $productListingDetails = '';
            if(!empty($data['item_specifics'])){
                $xml .= '<ItemSpecifics>';
                $item_specifics = json_decode($data['item_specifics'],true);
                foreach($item_specifics as $key=>$value){
                    if(!empty($value)){
                        if((strtoupper($key)=='UPC')||(strtoupper($key)=='EAN')||(strtoupper($key)=='ISBN')){
                            $productListingDetails .= '<'.strtoupper($key).'>'.$value.'</'.strtoupper($key).'>';
                            continue;
                        }
                        $xml .= '<NameValueList><Name>'.htmlspecialchars($key).'</Name><Value>'.htmlspecialchars($value).'</Value></NameValueList>';
                    }
                }
                $xml .= '</ItemSpecifics>';
            }
            if(!empty($productListingDetails)){ //UPC EAN ISBN
                $xml .='<ProductListingDetails>'.$productListingDetails.'</ProductListingDetails>';
            }
        }

        $xml .='</Item>';
        $response = $this->buildEbayBody($xml, $api, $site);
        if(isset($response->ItemID)){
            $return['is_success'] =true;
            $return['info'] ='修改成功';
        }else{
            $errorInfo = [];
            $return['is_success'] =false;
            if(isset($response->Errors)){
                foreach($response->Errors as $error){
                    if((string)$error->SeverityCode =='Error'){
                        $info_String = (string)$error->LongMessage;
                        if(isset($error->ErrorParameters->Value)){
                            $info_String = $info_String.((string)$error->ErrorParameters->Value);
                        }
                        $errorInfo[]=$info_String;
                    }
                }
                $errorInfo=  array_unique($errorInfo);
                $errorInfo =implode(',',$errorInfo);
            }

            $return['info'] =empty($errorInfo)?'未知错误':$errorInfo;
        }
        return $return;

    }

    public function endItem($item_id,$site=0){
        $return = [];
        $xml = '<ItemID>'.$item_id.'</ItemID>';
        $xml .= '<EndingReason>NotAvailable</EndingReason>';
        $response = $this->buildEbayBody($xml, 'EndItem', $site);
        if((string)$response->Ack=='Success'){
            $return['is_success'] = true;
            $return['info'] = '下架成功';
        }else{
            $return['is_success'] = false;
            $return['info'] = '下架失败';
        }
        return $return;
    }



    /** 获取该账号近一个月的Feedback
     * @param int $site
     */
    public function GetFeedback($site = 0)
    {
        $return = [];
        $pageSize = 100;
        $end = 10;
        $is_break = false;
        for ($page = 1; $page < $end; $page++) {
            $xml = '<DetailLevel>ReturnAll</DetailLevel>
			  <FeedbackType>FeedbackReceivedAsSeller</FeedbackType>
              <CommentType>Positive</CommentType>
			  <CommentType>Negative</CommentType>
			  <CommentType>Neutral</CommentType>
			  <OutputSelector>FeedbackDetailArray</OutputSelector>
			  <OutputSelector>PaginationResult</OutputSelector>
			  <WarningLevel>High</WarningLevel>
			  <Pagination><EntriesPerPage>' . $pageSize . '</EntriesPerPage>
		      <PageNumber>' . $page . '</PageNumber></Pagination>';
            $response = $this->buildEbayBody($xml, 'GetFeedback', $site);
            if ($response->Ack == 'Success') {
                $end = (int)$response->PaginationResult->TotalNumberOfPages;
                foreach ($response->FeedbackDetailArray->FeedbackDetail as $FeedbackDetail) {
                    $detail = [];
                    $detail['feedback_id'] = (string)$FeedbackDetail->FeedbackID;
                    $detail['commenting_user'] = (string)$FeedbackDetail->CommentingUser;
                    $detail['commenting_user_score'] = (int)$FeedbackDetail->CommentingUserScore;
                    $detail['comment_text'] = (string)$FeedbackDetail->CommentText;
                    $detail['comment_type'] = (string)$FeedbackDetail->CommentType;
                    $detail['ebay_item_id'] = (string)$FeedbackDetail->ItemID;
                    $detail['transaction_id'] = (string)$FeedbackDetail->TransactionID;
                    $detail['comment_time'] = date('Y-m-d H:i:s', strtotime($FeedbackDetail->CommentTime));
                    $return[] = $detail;
                    if (time() - strtotime($detail['comment_time']) > 30 * 24 * 60 * 60) { //超过30
                        $is_break = true;
                        break;
                    }
                }
            } else {
                break;
            }
            if ($is_break) {
                break;
            }
        }
        return $return;

    }

    /** 评价订单API
     * @param $data
     * @param string $type Positive 好评 Neutral中评 Negative 差评
     * @param string $text 评价内容
     * @param int $site 站点
     * @return bool
     */
    public function LeaveFeedback($data, $type = 'Positive', $text = 'Good buyer,prompt payment,nice to deal with!', $site = 0)
    {
        $xml = '<CommentText>' . $text . '</CommentText>';
        $xml .= '<CommentType>' . $type . '</CommentType>';
        $xml .= '<ItemID>' . $data['item_id'] . '</ItemID>';
        $xml .= '<TransactionID>' . $data['transaction_id'] . '</TransactionID>';
        $xml .= '<TargetUser>' . $data['target_user'] . '</TargetUser>';
        $response = $this->buildEbayBody($xml, 'LeaveFeedback', $site);
        if ($response->Ack == 'Success') {
            return true;
        } else {
            return false;
        }

    }

    /** 获取在线的listting
     * @param int $page
     * @param int $pageSize
     * @param int $site
     * @return array|bool
     */
    public function getOnlineProduct($page = 1, $pageSize = 200, $site = 0)
    {
        $return = [];
        $xml = '';
        //  $xml .= '<DetailLevel>ReturnAll</DetailLevel>';
        $xml .= '<ErrorLanguage>en_US</ErrorLanguage>';
        $xml .= '<MessageID>1000</MessageID>';
        $xml .= '<OutputSelector>ActiveList</OutputSelector>';
        $xml .= '<OutputSelector>ShippingDetails</OutputSelector>';
        $xml .= '<Version>' . $this->compatLevel . '</Version>';
        $xml .= '<ActiveList>';
        $xml .= '<Pagination>';
        $xml .= '<EntriesPerPage>' . $pageSize . '</EntriesPerPage>';
        $xml .= '<PageNumber>' . $page . '</PageNumber>';
        $xml .= '</Pagination>';
        $xml .= '</ActiveList>';
        $xml .= '<HideVariations>false</HideVariations>';
        $response = $this->buildEbayBody($xml, 'GetMyeBaySelling', $site);
        if (isset($response->ActiveList->PaginationResult->TotalNumberOfPages) && ($page > (int)$response->ActiveList->PaginationResult->TotalNumberOfPages)) {
            return false;
        }
        if (isset($response->ActiveList->ItemArray->Item) && !empty($response->ActiveList->ItemArray->Item)) {
            foreach ($response->ActiveList->ItemArray->Item as $item) {
                $return[] = (string)$item->ItemID;
            }
            return $return;
        } else {
            return false;
        }
    }

    public function getSellerEvents($start_time = '', $end_time = '', $site = 0)
    {
        $return = [];
        $xml = '';
        $xml .= ' <EndTimeFrom>' . $start_time . 'T00:00:00.000Z</EndTimeFrom><EndTimeTo>' . $end_time . 'T00:00:00.000Z</EndTimeTo>';
        $response = $this->buildEbayBody($xml, 'GetSellerEvents', $site);
        if (isset($response->ItemArray) && !empty($response->ItemArray) && $response->Ack == 'Success') {
            foreach ($response->ItemArray->Item as $item) {
                $return[] = $item->ItemID;
            }
            return $return;
        } else {
            return false;
        }
    }

    /** 获取item 详情
     * @param $itemId
     * @param int $site
     * @return array|bool
     */
    public function getProductDetail($itemId, $site = 0)
    {
        $return = [];
        //$itemId = 122059295599;//122077657433	;
        $xml = '';
        $xml .= '<ItemID>' . $itemId . '</ItemID>';
        $xml .= '<DetailLevel>ReturnAll</DetailLevel>';
        $xml .= '<IncludeItemSpecifics>true</IncludeItemSpecifics>';
        $response = $this->buildEbayBody($xml, 'GetItem', $site);
        if ($response->Ack == 'Success') {
            $list_info = [];
            $sku_info = [];
            $list_info['item_id'] = (string)$response->Item->ItemID;
            $list_info['currency'] = (string)$response->Item->Currency;
            $list_info['country'] = (string)$response->Item->Country;
            $list_info['start_time'] = date('Y-m-d H:i:s', strtotime((string)$response->Item->ListingDetails->StartTime));
            $list_info['view_item_url'] = (string)$response->Item->ListingDetails->ViewItemURL;
            $list_info['listing_duration'] = (string)$response->Item->ListingDuration;
            $list_info['listing_type'] = (string)$response->Item->ListingType;
            $list_info['location'] = (string)$response->Item->Location;
            $list_info['postal_code'] = (string)$response->Item->PostalCode;
            $list_info['payment_methods'] = (string)$response->Item->PaymentMethods;
            $list_info['paypal_email_address'] = (string)$response->Item->PayPalEmailAddress;
            $list_info['primary_category'] = (string)$response->Item->PrimaryCategory->CategoryID;
            $list_info['secondary_category'] = (string)$response->Item->SecondaryCategory->CategoryID;
            $list_info['private_listing'] = (string)$response->Item->PrivateListing;
            $list_info['dispatch_time_max'] = (string)$response->Item->DispatchTimeMax;
            $list_info['start_price'] = (float)$response->Item->StartPrice;
            $list_info['quantity'] = (int)$response->Item->Quantity - (int)$response->Item->SellingStatus->QuantitySold;
            $list_info['reserve_price'] = (float)$response->Item->ReservePrice;
            $list_info['buy_it_now_price'] = (float)$response->Item->BuyItNowPrice;
            $list_info['title'] = (string)$response->Item->Title;
            $list_info['sub_title'] = (string)$response->Item->SubTitle;
            $list_info['sku'] = (string)$response->Item->SKU;
            $list_info['site_name'] = (string)$response->Item->Site;
            $list_info['site'] = config('ebaysite.site_name_id')[$list_info['site_name']];
            $list_info['quantity_sold'] = (int)$response->Item->SellingStatus->QuantitySold;
            $list_info['store_category_id'] = (string)$response->Item->Storefront->StoreCategoryID;
            $list_info['description_id'] = 1;
            $list_info['country'] = isset($response->Item->Country) ? (string)$response->Item->Country: '';
            $list_info['warehouse'] = 1;
            $list_info['description_picture'] = '';
            $list_info['note'] = '';
            $list_info['description'] =isset($response->Item->Description) ? htmlspecialchars($response->Item->Description) :'';



            //ConditionID
            $list_info['condition_id'] = (string)$response->Item->ConditionID;
            $list_info['condition_description'] = (string)$response->Item->ConditionDescription;

            $list_info['picture_details'] = isset($response->Item->PictureDetails->PictureURL) ? json_encode((array)$response->Item->PictureDetails->PictureURL) : '';
            //ItemSpecifics
            $ItemSpecifics = isset($response->Item->ItemSpecifics->NameValueList) ? $response->Item->ItemSpecifics->NameValueList : '';
            $item_specifics = [];
            if (!empty($ItemSpecifics)) {
                foreach ($ItemSpecifics as $specifics) {
                    $item_specifics[(string)$specifics->Name] = (string)$specifics->Value;
                }
            }
            unset($ItemSpecifics);


            $list_info['item_specifics'] = json_encode($item_specifics);
            unset($item_specifics);


            $Variations = isset($response->Item->Variations->Variation) ? $response->Item->Variations->Variation : '';
            if (!empty($Variations)) {
                $i = 0;
                foreach ($Variations as $variation) {
                    $sku_info[$i]['sku'] = (string)$variation->SKU;
                    $sku_info[$i]['start_price'] = (float)$variation->StartPrice;
                    $sku_info[$i]['quantity'] = (int)$variation->Quantity - (int)$variation->SellingStatus->QuantitySold;
                    $sku_info[$i]['erp_sku'] = (string)$variation->SKU;
                    $sku_info[$i]['quantity_sold'] = isset($variation->SellingStatus->QuantitySold) ? (int)$variation->SellingStatus->QuantitySold : 0;
                    $sku_info[$i]['item_id'] = (string)$response->Item->ItemID;
                    $sku_info[$i]['start_time'] = date('Y-m-d H:i:s', strtotime((string)$response->Item->ListingDetails->StartTime));
                    $variation_specifics = [];
                    if (isset($variation->VariationSpecifics)) {
                        if (isset($variation->VariationSpecifics->NameValueList[0])) {
                            foreach ($variation->VariationSpecifics->NameValueList as $nameList) {
                                $variation_specifics[(string)$nameList->Name] = (string)$nameList->Value;
                            }
                        } else {
                            $variation_specifics[(string)$variation->VariationSpecifics->NameValueList->Name] = (string)$variation->VariationSpecifics->NameValueList->Value;
                        }
                    }
                    if (isset($variation->VariationProductListingDetails)) {
                        $VariationProductListingDetails = (array)$variation->VariationProductListingDetails;
                        foreach ($VariationProductListingDetails as $key => $value) {
                            $variation_specifics[(string)$key] = (string)$value;
                        }
                    }
                    $sku_info[$i]['variation_specifics'] = $variation_specifics;
                    $i++;
                }
            } else {
                $sku_info[0]['sku'] = (string)$response->Item->SKU;
                $sku_info[0]['start_price'] = (float)$response->Item->StartPrice;;
                $sku_info[0]['quantity'] = (int)$response->Item->Quantity - (int)$response->Item->SellingStatus->QuantitySold;
                $sku_info[0]['erp_sku'] = (string)$response->Item->SKU;;
                $sku_info[0]['quantity_sold'] = (int)$response->Item->SellingStatus->QuantitySold;
                $sku_info[0]['item_id'] = (string)$response->Item->ItemID;
                $sku_info[0]['start_time'] = date('Y-m-d H:i:s', strtotime((string)$response->Item->ListingDetails->StartTime));
            }
            $VariationPicture = isset($response->Item->Variations->Pictures) ? $response->Item->Variations->Pictures : '';
            $variation_picture = [];
            if (!empty($VariationPicture)) {
                $key = (string)$VariationPicture->VariationSpecificName;
                foreach ($VariationPicture->VariationSpecificPictureSet as $Variation) {
                    $variation_picture[$key][(string)$Variation->VariationSpecificValue] = (string)$Variation->PictureURL;
                }
            }
            unset($VariationPicture);
            $list_info['variation_picture'] = json_encode($variation_picture);
            unset($variation_picture);
            $VariationSpecificsSet = isset($response->Item->Variations->VariationSpecificsSet) ? $response->Item->Variations->VariationSpecificsSet : '';
            $variation_specifics = [];
            if (!empty($VariationSpecificsSet)) {

                if (isset($VariationSpecificsSet->NameValueList[0])) {
                    foreach ($VariationSpecificsSet->NameValueList as $nameList) {
                        $key = (string)$nameList->Name;
                        foreach ($nameList->Value as $value) {
                            $variation_specifics[$key][] = (string)$value;
                        }
                    }
                } else {
                    $key = (string)$VariationSpecificsSet->NameValueList->Name;
                    foreach ($VariationSpecificsSet->NameValueList->Value as $value) {
                        $variation_specifics[$key][] = (string)$value;
                    }
                }
            }
            unset($VariationSpecificsSet);
            $list_info['variation_specifics'] = json_encode($variation_specifics);
            unset($variation_specifics);
            $return_policy = [];
            $return_policy['ReturnsAcceptedOption'] = isset($response->Item->ReturnPolicy->ReturnsAcceptedOption) ? (string)$response->Item->ReturnPolicy->ReturnsAcceptedOption : '';
            $return_policy['ReturnsWithinOption'] = isset($response->Item->ReturnPolicy->ReturnsWithinOption) ? (string)$response->Item->ReturnPolicy->ReturnsWithinOption : '';
            $return_policy['RefundOption'] = isset($response->Item->ReturnPolicy->RefundOption) ? (string)$response->Item->ReturnPolicy->RefundOption : '';
            $return_policy['ShippingCostPaidByOption'] = isset($response->Item->ReturnPolicy->ShippingCostPaidByOption) ? (string)$response->Item->ReturnPolicy->ShippingCostPaidByOption : '';
            $return_policy['Description'] = isset($response->Item->ReturnPolicy->Description) ? (string)$response->Item->ReturnPolicy->Description : '';
            $return_policy['ExtendedHolidayReturns'] = isset($response->Item->ReturnPolicy->ExtendedHolidayReturns) ? (string)$response->Item->ReturnPolicy->ExtendedHolidayReturns : '';
            $list_info['return_policy'] = json_encode($return_policy);
            $shipping_details = [];
            if (isset($response->Item->ShippingDetails->ShippingServiceOptions[0])) { //多个国内运输选项
                foreach ($response->Item->ShippingDetails->ShippingServiceOptions as $ShippingServiceOptions) {
                    $key = (int)$ShippingServiceOptions->ShippingServicePriority;
                    $shipping_details['Shipping'][$key]['ShippingService'] = (string)$ShippingServiceOptions->ShippingService;
                    $shipping_details['Shipping'][$key]['ShippingServiceCost'] = (float)$ShippingServiceOptions->ShippingServiceCost;
                    $shipping_details['Shipping'][$key]['ShippingServiceAdditionalCost'] = (float)$ShippingServiceOptions->ShippingServiceAdditionalCost;
                }
            } else {
                $ShippingServiceOptions = $response->Item->ShippingDetails->ShippingServiceOptions;
                $shipping_details['Shipping'][1]['ShippingService'] = (string)$ShippingServiceOptions->ShippingService;
                $shipping_details['Shipping'][1]['ShippingServiceCost'] = (float)$ShippingServiceOptions->ShippingServiceCost;
                $shipping_details['Shipping'][1]['ShippingServiceAdditionalCost'] = (float)$ShippingServiceOptions->ShippingServiceAdditionalCost;
            }
            if (isset($response->Item->ShippingDetails->InternationalShippingServiceOption[0])) { //多个国际运输选项
                foreach ($response->Item->ShippingDetails->InternationalShippingServiceOption as $InternationalShippingServiceOption) {
                    $key = (int)$InternationalShippingServiceOption->ShippingServicePriority;
                    $shipping_details['InternationalShipping'][$key]['ShippingService'] = (string)$InternationalShippingServiceOption->ShippingService;
                    $shipping_details['InternationalShipping'][$key]['ShippingServiceCost'] = (float)$InternationalShippingServiceOption->ShippingServiceCost;
                    $shipping_details['InternationalShipping'][$key]['ShippingServiceAdditionalCost'] = (float)$InternationalShippingServiceOption->ShippingServiceAdditionalCost;
                    $shipToLocation = [];
                    if (isset($InternationalShippingServiceOption->ShipToLocation[0])) {
                        foreach ($InternationalShippingServiceOption->ShipToLocation as $location) {
                            $shipToLocation[] = (string)$location;
                        }
                    } else {
                        $shipToLocation[] = (string)$InternationalShippingServiceOption->ShipToLocation;
                    }


                    $shipping_details['InternationalShipping'][$key]['ShipToLocation'] = $shipToLocation;
                }
            } else {
                $InternationalShippingServiceOption = $response->Item->ShippingDetails->InternationalShippingServiceOption;
                $shipping_details['InternationalShipping'][1]['ShippingService'] = (string)$InternationalShippingServiceOption->ShippingService;
                $shipping_details['InternationalShipping'][1]['ShippingServiceCost'] = (float)$InternationalShippingServiceOption->ShippingServiceCost;
                $shipping_details['InternationalShipping'][1]['ShippingServiceAdditionalCost'] = (float)$InternationalShippingServiceOption->ShippingServiceAdditionalCost;
                $shipping_details['InternationalShipping'][1]['ShipToLocation'] = (string)$InternationalShippingServiceOption->ShipToLocation;


            }
            $shipping_details['ExcludeShipToLocation'] = (array)$response->Item->ShippingDetails->ExcludeShipToLocation;
            $list_info['shipping_details'] = json_encode($shipping_details);
            if (isset($response->Item->OutOfStockControl) && ((string)$response->Item->OutOfStockControl == 'true')) {
                $list_info['is_out_control'] = 1;
            }
            unset($shipping_details);
            $return['sku_info'] = $sku_info;
            $return['list_info'] = $list_info;
            return $return;
        } else {
            return false;
        }
    }

    /** 开启无货在线
     * @param $itemId
     * @param $is_out_stock
     * @param int $site
     * @return mixed
     */
    public function changeOutOfStock($itemId, $is_out_stock, $site = 0)
    {
        $xml = '';
        $xml .= '<Item><ItemID>' . $itemId . '</ItemID><OutOfStockControl>' . $is_out_stock . '</OutOfStockControl></Item>';
        $response = $this->buildEbayBody($xml, 'ReviseFixedPriceItem', $site);
        if ($response->Ack == 'Success') {
            $return['status'] = true;
            $return['info'] = 'Success';
        } else {
            $return['status'] = false;
            $return['info'] = isset($response->Errors->LongMessage) ? (string)$response->Errors->LongMessage : '未知错误';
        }
        return $return;
    }

    /** 处理天数
     * @param $itemId
     * @param $day
     * @param int $site
     * @return mixed
     */
    public function changeProcessingDays($itemId, $day, $site = 0)
    {
        $xml = '';
        $xml .= '<Item><ItemID>' . $itemId . '</ItemID><DispatchTimeMax>' . $day . '</DispatchTimeMax></Item>';
        $response = $this->buildEbayBody($xml, 'ReviseFixedPriceItem', $site);
        if ($response->Ack == 'Success') {
            $return['status'] = true;
            $return['info'] = 'Success';
        } else {
            $return['status'] = false;
            $return['info'] = isset($response->Errors->LongMessage) ? (string)$response->Errors->LongMessage : '未知错误';
        }
        return $return;
    }

    /** 改PAYPAL
     * @param $itemId
     * @param $paypal
     * @param int $site
     * @return mixed
     */
    public function changePayPal($itemId, $paypal, $site = 0)
    {
        $xml = '';
        $xml .= '<Item><ItemID>' . $itemId . '</ItemID><PayPalEmailAddress>' . $paypal . '</PayPalEmailAddress></Item>';
        $response = $this->buildEbayBody($xml, 'ReviseFixedPriceItem', $site);
        if ($response->Ack == 'Success') {
            $return['status'] = true;
            $return['info'] = 'Success';
        } else {
            $return['status'] = false;
            $return['info'] = isset($response->Errors->LongMessage) ? (string)$response->Errors->LongMessage : '未知错误';
        }
        return $return;
    }

    /** 修改在线广告的价格
     * @param $itemId
     * @param $sku
     * @param int $is_mul
     * @param int $site
     * @return array
     */
    public function changePrice($itemId, $sku, $is_mul = 0, $site = 0)
    {
        $return = [];
        $xml = '';
        if ($is_mul) {
            foreach ($sku as $key => $v) {
                $xml .= '<InventoryStatus>';
                $xml .= '<ItemID>' . $itemId . '</ItemID>';
                $xml .= '<SKU>' . $key . '</SKU>';
                $xml .= '<StartPrice>' . $v . '</StartPrice>';
                $xml .= '</InventoryStatus>';
            }
        } else {
            foreach ($sku as $key => $v) {
                $xml .= '<InventoryStatus>';
                $xml .= '<ItemID>' . $itemId . '</ItemID>';
                $xml .= '<StartPrice>' . $v . '</StartPrice>';
                $xml .= '</InventoryStatus>';
            }
        }
        $response = $this->buildEbayBody($xml, 'ReviseInventoryStatus', $site);
        if ($response->Ack == 'Success' || $response->Ack == 'Warning') {
            $return['status'] = true;
            $return['info'] = isset($response->Errors->LongMessage) ? 'Success' . (string)$response->Errors->LongMessage : 'Success';
        } else {
            $return['status'] = false;
            $return['info'] = isset($response->Errors->LongMessage) ? (string)$response->Errors->LongMessage : '未知错误';
        }
        return $return;
    }

    /** 修改在线广告的数量 ReviseInventoryStatus
     * @param $itemId
     * @param $sku
     * @param int $is_mul
     * @param int $site
     * @return array
     */
    public function changeQuantity($itemId, $sku, $is_mul = 0, $site = 0)
    {
        $return = [];
        $xml = '';
        if ($is_mul) {
            foreach ($sku as $key => $v) {
                $xml .= '<InventoryStatus>';
                $xml .= '<ItemID>' . $itemId . '</ItemID>';
                $xml .= '<SKU>' . $key . '</SKU>';
                $xml .= '<Quantity>' . $v . '</Quantity>';
                $xml .= '</InventoryStatus>';
            }
        } else {
            foreach ($sku as $key => $v) {
                $xml .= '<InventoryStatus>';
                $xml .= '<ItemID>' . $itemId . '</ItemID>';
                $xml .= '<Quantity>' . $v . '</Quantity>';
                $xml .= '</InventoryStatus>';
            }
        }
        $response = $this->buildEbayBody($xml, 'ReviseInventoryStatus', $site);
        if ($response->Ack == 'Success' || $response->Ack == 'Warning') {
            $return['status'] = true;
            $return['info'] = isset($response->Errors->LongMessage) ? 'Success' . (string)$response->Errors->LongMessage : 'Success';
        } else {
            $return['status'] = false;
            $return['info'] = isset($response->Errors->LongMessage) ? (string)$response->Errors->LongMessage : '未知错误';
        }
        return $return;
    }

    /** 修改在线广告的国内第一运费 和 国际第一运输运费
     * @param $itemId
     * @param $ship_info
     * @param int $site
     * @return array
     */
    public function changeShippingFee($itemId, $ship_info, $site = 0)
    {
        $return = [];
        $xml = '';
        $xml .= '<Item><ItemID>' . $itemId . '</ItemID><ShippingDetails>';
        foreach ($ship_info['Shipping'] as $key => $shipping) {
            $xml .= '<ShippingServiceOptions>';
            $xml .= '<ShippingService>' . $shipping['ShippingService'] . '</ShippingService>';
            $xml .= '<ShippingServiceAdditionalCost>' . sprintf("%.2f", $shipping['ShippingServiceAdditionalCost']) . '</ShippingServiceAdditionalCost>';
            $xml .= '<ShippingServiceCost>' . sprintf("%.2f", $shipping['ShippingServiceCost']) . '</ShippingServiceCost>';
            $xml .= '<ShippingServicePriority>' . $key . '</ShippingServicePriority>';
            $xml .= '</ShippingServiceOptions>';
        }
        foreach ($ship_info['InternationalShipping'] as $key => $shipping) {
            $xml .= '<InternationalShippingServiceOption>';
            $xml .= '<ShippingService>' . $shipping['ShippingService'] . '</ShippingService>';
            $xml .= '<ShippingServiceAdditionalCost>' . sprintf("%.2f", $shipping['ShippingServiceAdditionalCost']) . '</ShippingServiceAdditionalCost>';
            $xml .= '<ShippingServiceCost>' . sprintf("%.2f", $shipping['ShippingServiceCost']) . '</ShippingServiceCost>';
            $xml .= '<ShippingServicePriority>' . $key . '</ShippingServicePriority>';
            foreach ($shipping['ShipToLocation'] as $location) {
                $xml .= '<ShipToLocation>' . $location . '</ShipToLocation>';
            }
            $xml .= '</InternationalShippingServiceOption>';
        }
        if (!empty($ship_info['ExcludeShipToLocation'])) {
            foreach ($ship_info['ExcludeShipToLocation'] as $exclude)
                $xml .= '<ExcludeShipToLocation>' . $exclude . '</ExcludeShipToLocation>';
        }
        $xml .= '</ShippingDetails></Item>';
        $response = $this->buildEbayBody($xml, 'ReviseFixedPriceItem', $site);
        if ($response->Ack == 'Success') {
            $return['status'] = true;
            $return['info'] = 'Success';
        } else {
            $return['status'] = false;
            $return['info'] = isset($response->Errors->LongMessage) ? (string)$response->Errors->LongMessage : '未知错误';
        }
        return $return;
    }

    /** 下架
     * @param $itemId
     * @param string $reason
     * @param int $site
     * @return array
     */
    public function endItems($itemId, $reason = 'NotAvailable Sorry', $site = 0)
    {
        $return = [];
        $xml = '';
        $xml .= '<EndingReason>' . $reason . '</EndingReason>';
        $xml .= '<ItemID>' . $itemId . '</ItemID>';
        $response = $this->buildEbayBody($xml, 'EndItem', $site);
        if ($response->Ack == 'Success') {
            $return['status'] = true;
            $return['info'] = 'Success';
        } else {
            $return['status'] = false;
            $return['info'] = isset($response->Errors->LongMessage) ? (string)$response->Errors->LongMessage : '未知错误';
        }
        return $return;
    }


    public function  buildEbayBody($xml, $call, $site = 0)
    {
        $this->siteID = $site;
        $this->verb = $call;
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?><' . $call . 'Request xmlns="urn:ebay:apis:eBLBaseComponents">';
        $requestXmlBody .= $xml;
        $requestXmlBody .= '<RequesterCredentials><eBayAuthToken>' . $this->requestToken . '</eBayAuthToken></RequesterCredentials></' . $call . 'Request>';
        $result = $this->sendHttpRequest($requestXmlBody);
        $response = simplexml_load_string($result);
        return $response;

    }

    /**
     * case XML DOM
     */
    public function buildcaseBody($xml, $call)
    {
        $this->serverUrl = 'https://svcs.ebay.com/services/resolution/v1/ResolutionCaseManagementService';  //cases API地址
        $this->verb = $call;
        $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?><' . $call . 'Request xmlns="http://www.ebay.com/marketplace/resolution/v1/services">';
        $requestXmlBody .= $xml;
        $requestXmlBody .= '<RequesterCredentials><eBayAuthToken>' . $this->requestToken . '</eBayAuthToken></RequesterCredentials></' . $call . 'Request>';

        $result = $this->sendHttpRequest($requestXmlBody, 'Resolution');
        $response = simplexml_load_string($result);

        return $response;
    }

    private function buildEbayHeaders()
    {
        $headers = array(
            'Content-type: text/xml',
            'X-EBAY-API-COMPATIBILITY-LEVEL: ' . $this->compatLevel,
            'X-EBAY-API-DEV-NAME: ' . $this->devID,
            'X-EBAY-API-APP-NAME: ' . $this->appID,
            'X-EBAY-API-CERT-NAME: ' . $this->certID,
            'X-EBAY-API-CALL-NAME: ' . $this->verb,
            'X-EBAY-API-SITEID: ' . $this->siteID,
        );

        return $headers;
    }


    /*    public function sendHttpRequestMessage($requestBody, $mode='Trading') {
            //build eBay headers using variables passed via constructor
            if (strcasecmp($mode, 'Trading') === 0) {       //交易API的头信息
                $headers = $this->buildEbayHeaders();
            }elseif (strcasecmp($mode, 'Resolution') === 0){//resolution Api头信息
                $headers = $this->buildEbayResolutionHeaders();
            }elseif (strcasecmp($mode, 'Return') === 0){ //return case Api头信息
                $headers = $this->buildEbayReturnHeaders();
            }else {
                $headers = $this->buildEbayHeaders();
            }
            $connection = curl_init();
            curl_setopt($connection, CURLOPT_VERBOSE, 1);
            //set the server we are using (could be Sandbox or Production server)
            curl_setopt($connection, CURLOPT_URL, $this->serverUrl);
            //stop CURL from verifying the peer's certificate
            curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
            //set the headers using the array of headers
            curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
            //set method as POST
            curl_setopt($connection, CURLOPT_POST, 1);
            //set the XML body of the request
            curl_setopt($connection, CURLOPT_POSTFIELDS, $requestBody);
            //set it to return the transfer as a string from curl_exec
            curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);

            curl_setopt($connection, CURLOPT_TIMEOUT, 250);
            //Send the Request
            $response = curl_exec($connection);
            //$error = curl_error($connection);
            //close the connection
            curl_close($connection);
            return $response;
        }*/

    /**
     * cases 使用的头部
     * 创建resolution API的头信息
     * @return multitype:
     */
    private function buildEbayResolutionHeaders()
    {
        $headers = array(
            'X-EBAY-SOA-SERVICE-NAME: ResolutionCaseManagementService',
            'X-EBAY-SOA-OPERATION-NAME: ' . $this->verb,
            'X-EBAY-SOA-SERVICE-VERSION: 1.1.0',
            'X-EBAY-SOA-SECURITY-TOKEN: ' . $this->requestToken,
            'X-EBAY-SOA-REQUEST-DATA-FORMAT: XML'
        );
        return $headers;
    }

    public function sendHttpRequest($requestBody, $type = false)
    {
        if ($type == 'Resolution') { //case 头部
            $headers = $this->buildEbayResolutionHeaders();
        } else {
            $headers = $this->buildEbayHeaders();

        }
        //print_r($headers);

        $connection = curl_init();

        curl_setopt($connection, CURLOPT_URL, $this->serverUrl);
        curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($connection, CURLOPT_POST, 1);
        curl_setopt($connection, CURLOPT_POSTFIELDS, $requestBody);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($connection, CURLOPT_TIMEOUT, 200);
        $response = curl_exec($connection);
        curl_close($connection);
        return $response;
    }


    public function getMessages()
    {
        $message_lists =[];
        $order = 0;
        // 1.封装message 的XML DOM
        $before_day = 1;
        $time_begin = date("Y-m-d H:i:s", time() - (86400 * $before_day));
        $time_end   = date('Y-m-d H:i:s');
        $arr = explode(' ', $time_end);
        $time_end = $arr[0] . 'T' . $arr[1] . '.000Z';
        $arr = explode(' ', $time_begin);
        $time_begin = $arr[0] . 'T' . $arr[1] . '.000Z';
        $message_xml_dom = '<WarningLevel>High</WarningLevel>
                            <DetailLevel>ReturnSummary</DetailLevel>
                            <StartTime>' . $time_begin . '</StartTime>
                            <EndTime>' . $time_end . '</EndTime>';
        //2.获取消息
        $call = 'GetMyMessages';
        $message_ary =  $this->buildEbayBody($message_xml_dom,$call);
        $headers_count = $message_ary->Summary->TotalMessageCount;
        $headers_pages_count = ceil($headers_count / 100); //统计页数
        for($index = 1 ; $index <= $headers_pages_count ; $index ++){
            $content_xml_dom = '<WarningLevel>High</WarningLevel>
                                <DetailLevel>ReturnHeaders</DetailLevel>
                                <Pagination>
                                    <EntriesPerPage>100</EntriesPerPage>
                                    <PageNumber>' . $index . '</PageNumber>
                                </Pagination>
                                <StartTime>' . $time_begin . '</StartTime>
                                <EndTime>' . $time_end . '</EndTime>';
            $content = $this->buildEbayBody($content_xml_dom,'GetMyMessages');
            if(isset($content->Messages->Message)) {
                foreach ($content->Messages->Message as $message){
                    $member_xlm_dom = '
                                    <WarningLevel>High</WarningLevel>
                                    <DetailLevel>ReturnMessages</DetailLevel>
                                    <MessageIDs><MessageID>'.$message->MessageID.'</MessageID>
                                    </MessageIDs>
                                    ';
                    $content_detail = $this->buildEbayBody($member_xlm_dom,'GetMyMessages');
                    $message_lists[$order]['message_id'] = $message->MessageID;
                    $message_lists[$order]['from_name'] = $message->Sender;
                    $message_lists[$order]['from'] = $message->SendingUserID;
                    $message_lists[$order]['to'] = $message->SendToName;
                    $message_lists[$order]['labels'] = '';
                    $message_lists[$order]['label'] = 'Ebay';
                    $message_lists[$order]['date'] = $message->ReceiveDate;
                    $message_lists[$order]['subject'] = $message->Subject;
                    $message_lists[$order]['attachment'] = ''; //附件
                    $message_lists[$order]['content'] = base64_encode(serialize([ 'ebay' => (string)$content_detail->Messages->Message->Text]));
                    $message_fields_ary = [
                        'ItemID'            => (string)$message->ItemID, //应该是订单号
                        'ExternalMessageID' => (string)$message->ExternalMessageID,
                        'ResponseDetails'   => (string)$message->ResponseDetails->ResponseURL,
                    ];
                    $message_lists[$order]['channel_message_fields'] = base64_encode(serialize($message_fields_ary));
                    $message_lists[$order]['channel_order_number'] = (string)$message->ItemID;
                    $order += 1;
                }
            }
        }
        return (!empty($message_lists)) ?  array_reverse($message_lists) : false;
    }
    public function createMemberMessageXML($page) {
        $this->input_str = '
                            <RequesterCredentials>
                            <eBayAuthToken>' . $this->userToken . '</eBayAuthToken>
                            </RequesterCredentials>
                            <WarningLevel>High</WarningLevel>
                            <MailMessageType>All</MailMessageType>
                            <EndCreationTime>' . $this->time_e . '</EndCreationTime>
                            <StartCreationTime>' . $this->time_b . '</StartCreationTime>
                            <Pagination>
                                <EntriesPerPage>100</EntriesPerPage>
                                <PageNumber>' . $page . '</PageNumber>
                            </Pagination>
        ';
    }
    public function sendMessages($replyMessage)
    {
        $message_obj = $replyMessage->message; //关联关系  获取用户邮件
        if(!empty($message_obj)){
            $fields = unserialize(base64_decode($message_obj->channel_message_fields)); //渠道特殊值
            //1.封装XML DOM
            $reply_xml_dom = '<RequesterCredentials>
                              <eBayAuthToken>' . $this->requestToken . '</eBayAuthToken>
                              </RequesterCredentials>
                              <WarningLevel>High</WarningLevel>
                              <ItemID>' . $fields['ItemID'] . '</ItemID>
                              <MemberMessage>
                              <Body>' . $replyMessage->content . '</Body>
                              <DisplayToPublic>false</DisplayToPublic>
                              <EmailCopyToSender>true</EmailCopyToSender>
                              <ParentMessageID>' . $fields['ExternalMessageID'] . '</ParentMessageID>
                              <RecipientID>' . $message_obj->from_name . '</RecipientID>
                              </MemberMessage>';
            $content = $this->buildEbayBody($reply_xml_dom,'AddMemberMessageRTQ');

            if($content->Ack == 'Success'){
                $replyMessage->status = 'SENT';

            }else{
                $replyMessage->status = 'FAIL';
            }
            $replyMessage->save();

            return $content->Ack == 'Success' ? true : false;
        }
    }
    /**
     * 获取纠纷
     */
    public function getCases(){
        /**
         * status   先写死   所有状态都获取
         * time 写死 7天
         *
         */
        $time_end = date('Y-m-d\TH:i:s.000\Z', time());
        $time_begin = date('Y-m-d\TH:i:s.000\Z', strtotime('-7 day'));
        $page = 1;
        $cases_xml = '<creationDateRangeFilter>
                     <fromDate>'.$time_begin.'</fromDate>
                     <toDate>'.$time_end.'</toDate>
                     </creationDateRangeFilter>
                     <paginationInput>
                        <entriesPerPage>100</entriesPerPage>
                        <pageNumber>'.$page.'</pageNumber>
                     </paginationInput>
                     <sortOrder>CREATION_DATE_DESCENDING</sortOrder>';
        $usercases = $this->buildcaseBody($cases_xml,'getUserCases');
        if($usercases->ack == 'Success'){

            foreach ($usercases->cases->caseSummary as $case){
                $buyer = '';
                $seller = '';
                if((string)$case->user->role == 'SELLER'){
                    $seller = (string)$case->user->userId;
                }
                switch ((string)$case->user->role){
                    case 'BUYER':
                        $seller  = (string)$case->otherParty->userId;
                        $buyer = (string)$case->user->userId;
                        break;
                    case 'SELLER':
                        $buyer  = (string)$case->otherParty->userId;
                        $seller = (string)$case->user->userId;
                        break;
                    default:
                        break;
                }
                $status = array_values((array)$case->status);
                if($case->lastModifiedDate){
                    $modify_date = (string)$case->lastModifiedDate;
                }else{
                    $modify_date = '';
                }
                $case_new_ary = [
                    'case_id'        => (string)$case->caseId->id,
                    'status'         => $status[0],
                    'type'           => (string)$case->caseId->type,
                    'seller_id'      => $seller,
                    'buyer_id'       => $buyer,
                    'item_id'        => (string)$case->item->itemId,
                    'item_title'     => (string)$case->item->itemTitle,
                    'transaction_id' => (string)$case->item->transactionId,
                    'case_quantity'  => (int)$case->caseQuantity,
                    'case_amount'    => (float)$case->caseAmount,
                    'respon_date'    => (string)$case->respondByDate,
                    'creation_date'  => (string)$case->creationDate,
                    'last_modify_date'=> $modify_date,
                    'account_id'      => $this->accountID,
                    'process_status'  => 'UNREAD'
                ];
                //获取case 详情	 获取EBP_INR，EBP_SNAD， RETURN三类的详情
                $valid_type = ['EBP_INR','EBP_SNAD','RETURN'];
                if(in_array($case->caseId->type,$valid_type)){
                    $case_detail_ary = [];
                    $content = '';
                    $case_detail = $this->buildcaseBody($this->createCaseDetailXml($case->caseId->id,(string)$case->caseId->type),'getEBPCaseDetail');
                    if($case_detail->ack == 'Success'){
                        // $transaction_id = ''; //paypal交易号
                        if($case_detail->caseDetail->responseHistory){
                            $detail = (array)$case_detail->caseDetail;
                            //dd($detail);
                            if(isset($detail['responseHistory'])){  //若包括消息
                                foreach ($detail['responseHistory'] as $note){
                                    $content []= [
                                        'role' =>(string)$note->author->role,
                                        'activity' => (string)$note->activity,
                                        'creationDate'=> (string)$note->creationDate,
                                        'note' => (string)$note->note,
                                    ];
                                }
                                $content = base64_encode(serialize($content));
                            }
                            // $transaction_id = isset($case_detail->caseDetail->paymentDetail->moneyMovement->paypalTransactionId) ? (string)$case_detail->caseDetail->paymentDetail->moneyMovement->paypalTransactionId : '';
                        }
                        $case_detail_ary = [
                            'tran_price' => $case_detail->item->transactionPrice,
                            'tran_date' => $case_detail->item->transactionDate,
                            'global_id' => $case_detail->item->globalId,
                            'open_reason'=> $case_detail->caseDetail->openReason,
                            'decision'=> $case_detail->caseDetail->decision,
                            'fvf_credited'=> $case_detail->caseDetail->FVFCredited,
                            'agreed_renfund_amount'=> $case_detail->caseDetail->agreedRefundAmount,
                            'buyer_expection'=> $case_detail->caseDetail->initialBuyerExpectation,
                            'content' => $content,
                            //'transaction_id' => $transaction_id,
                        ];
                    }
                    $list_obj =  EbayCasesListsModel::where('case_id','=',(string)$case->caseId->id)->first();
                    if(empty($list_obj)){
                        EbayCasesListsModel::create(array_merge($case_new_ary,$case_detail_ary)); //合并list和detail 创建记录
                        echo 'add one';
                    }else{
                        echo $case->caseId->id.'exist insert into ERP';
                    }
                }
            }
        }
    }
    public function createCaseDetailXml($caseId,$caseType){
        return '<caseId>
                    <id>'.$caseId.'</id>
                    <type>'.$caseType.'</type>
                </caseId>';
    }
    /**
     *
     * 创建offerOtherSolution API发送的XML信息
     *
     * @param  [type] $caseArray [description]
     * @return [type]            [description]
     */
    public function createSolutionXml($caseArray){
        $this->input_str = '
        <caseId>
        <id>'.$caseArray['caseId'].'</id>
        <type>'.$caseArray['caseType'].'</type>
        </caseId>
        <messageToBuyer>'.htmlspecialchars($caseArray['messageToBuyer']).'</messageToBuyer>
        ';
    }
    /**
     * 提供其他的解决方案      send a message
     * @param  [type] $caseArray [description]
     * @return [type]            [description]
     */
    public function offerOtherSolution($paramAry){
        $xml = $this->createSolutionXml($paramAry);
        $content = $this->buildcaseBody($xml,'offerOtherSolution');
        if($content->Ack =='Success' || $content->Ack == 'Warning'){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 创建跟踪号输入
     * @param unknown $caseArray
     */
    public function createTrackingXml($caseArray){
        $this->input_str = '
              <carrierUsed>'.$caseArray['carrierUsed'].'</carrierUsed>
              <caseId>
                <id>'.$caseArray['caseId'].'</id>
                <type>'.$caseArray['caseType'].'</type>
              </caseId>
              <trackingNumber>'.$caseArray['trackingNumber'].'</trackingNumber>
              ';
        if ($caseArray['comments']) {
            $this->input_str .= '<comments>'.htmlspecialchars($caseArray['comments']).'</comments>';
        }
    }
    /**
     * 提供追踪信息
     */
    public function provideTrackingInfo($paramAry){
        $xml     = $this->createTrackingXml($paramAry);
        $content = $this->buildcaseBody($xml,'provideTrackingInfo');
        if($content->Ack =='Success' || $content->Ack == 'Warning'){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 提供发货信息
     */
    public function provideShippingInfo($paramAry){
        $xml     = $this->createShippingXml($paramAry);
        $content = $this->buildcaseBody($xml,'provideShippingInfo');
        if($content->Ack =='Success' || $content->Ack == 'Warning'){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 创建发货相关的XML信息
     * @param unknown $array
     */
    public function createShippingXml($array){
        $shippedDate = date('Y-m-d\TH:i:s.000\Z', strtotime($array['shippedDate']));
        $this->input_str = '
              <carrierUsed>'.$array['carrierUsed'].'</carrierUsed>
              <caseId>
                <id>'.$array['caseId'].'</id>
                <type>'.$array['caseType'].'</type>
              </caseId>
              <shippedDate>'.$shippedDate.'</shippedDate>';
        if ($array['comments']) {
            $this->input_str .= '<comments>'.htmlspecialchars($array['comments']).'</comments>';
        }
    }
    /**
     * compact('item_id','buyer_id','itemids','title','content')
     * 订单列表 send ebay message
     */
    public function ebayOrderSendMessage($paramAry){
        $total = count($paramAry['itemids']);
        $ItemIDXML = ($total == 1) ? '<ItemID>' .$paramAry['itemids'][0] . '</ItemID>' : '' ;
        $moreItem  = ($total > 1) ?  implode(',',$paramAry['itemids']) : '' ;
        $xml ='<WarningLevel>High</WarningLevel>
               ' . $ItemIDXML . '
              <MemberMessage>
                <Subject>' . addslashes($paramAry['title']) . ' ' . addslashes($moreItem) . '</Subject>
                <Body>' . addslashes($paramAry['content']) . '</Body>
                <QuestionType>CustomizedSubject</QuestionType>
                <RecipientID>' . addslashes($paramAry['buyer_id']) . '</RecipientID>
              </MemberMessage>';
        $result = $this->buildEbayBody($xml,'AddMemberMessageAAQToPartner');
        if($result->Ack =='Success' || $result->Ack == 'Warning'){
            return true;
        }else{
            return false;
        }

    }
    /**
     * 修改ebay平台 unpaid case
     * compact('order_item_number','transcation_id')
     */
    public function ebayUnpaidCase($paramAry){
        switch ($paramAry['disputeType']){
            case 'complaints': //unpaid case
                $disputeExplanation = 'BuyerNotPaid';
                $disputeReason      = 'BuyerHasNotPaid';
                break;
            case 'cancel': //取消交易
                $disputeExplanation = 'OtherExplanation';
                $disputeReason      = 'TransactionMutuallyCanceled';
                break;
            default:
                break;
        }
        $xml = '
        	  <DisputeExplanation>' . $disputeExplanation . '</DisputeExplanation>
              <DisputeReason>' . $disputeReason . '</DisputeReason>
              <ItemID>'.$paramAry['order_item_number'].'</ItemID>
              <TransactionID>'.$paramAry['transcation_id'].'</TransactionID>
        ';
        $result = $this->buildcaseBody($xml,'AddDispute');
        if($result->Ack =='Success' || $result->Ack == 'Warning'){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 全额退款操作
     *
     */
    public function caseFullRefund($paramAry){
        $xml = '<caseId>
                    <id>'.$paramAry['caseId'].'</id>
                    <type>'.$paramAry['caseType'].'</type>
                </caseId>
                ';
        $xml .= empty($paramAry['comment']) ? '' : '<comments>'.htmlspecialchars($paramAry['comment']).'</comments>';
        $result = $this->buildcaseBody($xml,'issueFullRefund');
        if($result->Ack =='Success' || $result->Ack == 'Warning'){
            return true;
        }else{
            return false;
        }
    }
    public function casePartRefund($paramAry){
        $xml = '
        	      <amount>'.$paramAry['amount'].'</amount>
                  <caseId>
                    <id>'.$paramAry['caseId'].'</id>
                    <type>EBP_SNAD</type>
                  </caseId>
        ';
        $xml .= empty($paramAry['comment']) ? '' : '<comments>'.htmlspecialchars($paramAry['comment']).'</comments>';
        $result = $this->buildcaseBody($xml,'issuePartialRefund');
        if($result->Ack =='Success' || $result->Ack == 'Warning'){
            return true;
        }else{
            return false;
        }
    }
}