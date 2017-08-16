<?php
namespace App\Modules\Channel\Adapter;

/**
 * Created by PhpStorm.
 * User: Vincent
 * Date: 16/5/17
 * Time: 下午2:51
 */
interface AdapterInterface
{
    /**
     * 获取单个订单
     *
     * @param $orderID
     * @return mixed
     */
    public function getOrder($orderID);

    /**
     * 获取订单列表
     *
     * @param $startDate
     * @param $endDate
     * @param array $status呵da
     * @param int $perPage
     * @return $orderArray or $errorArray
     *
     * 返回数据格式:
     * ****************************************************
     * 成功:
     * [
     *  'orders' =>
     *      [
     *          [
     *              'channel_ordernum' => '',
     *              'email' => '',
     *              'amount' => '',
     *              'currency' => '',
     *              'status' => '',
     *              'payment' => '',
     *              'shipping' => '',
     *              'shipping_firstname' => '',
     *              'shipping_lastname' => '',
     *              'shipping_address' => '',
     *              'shipping_address1' => '',
     *              'shipping_city' => '',
     *              'shipping_state' => '',
     *              'shipping_country' => '',
     *              'shipping_zipcode' => '',
     *              'shipping_phone' => '',
     *              'payment_date' => '',
     *              'create_time' => '',
     *              'items' => [
     *                  [
     *                      'sku' => '',
     *                      'channel_sku' => '',
     *                      'quantity' => '',
     *                      'price' => '',
     *                      'currency' => '',
     *                  ],
     *                  [
     *                      Same As above ...
     *                  ],
     *              ]
     *          ],
     *          [
     *              Same As above ...
     *          ],
     *      ],
     *  'nextToken' => ''
     * ]
     * **************************************************
     * 失败:
     * [
     *  'error' =>
     *      [
     *          'code' => '',
     *          'message' => ''
     *      ]
     * ]
     */
    public function listOrders($startDate, $endDate, $status = [], $perPage = 10, $nextToken = null);

    /**
     * 回传物流信息
     *
     * $tracking_info =[
     * 'id' => '',//wish 使用
     * 'tracking_number' =>'',  //wish 使用
     * 'tracking_provider' =>'', //wish 使用
     * 'ship_note' =>'' //wish 使用
     * ]
     *
     */
    public function returnTrack($tracking_info);

    /**
     * 获取平台邮件
     * @return mixed
     *
     * 返回数据格式:
     * [
     *      [
     *          'message_id' => '', 渠道messageID
     *          'title' => '',
     *          'from_name' => '',
     *          'from_email' => '',
     *          'to_name' => '',
     *          'to_email' => '',
     *          'date' => '',
     *          'content' => '',
     *          'attanchments' =>
     *          [
     *              ['name','path']
     *              ['name','path']
     *              ['name','path']
     *           ],
     *      ],
     *      [
     *          Same As above ...
     *      ],
     * ]
     *
     *
     * 无数据返回：false;
     *
     */
    public function getMessages();

    public function sendMessages($replyMessage);

}