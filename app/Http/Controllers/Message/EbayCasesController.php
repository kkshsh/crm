<?php
/**
 * Ebay case纠纷
 * @author Norton
 *
 */

namespace App\Http\Controllers\Message;


use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Message\Issues\EbayCasesListsModel;
use App\Modules\Channel\Adapter\EbayAdapter;
use App\Models\Order\RefundModel;


class EbayCasesController extends Controller
{
    public function __construct(EbayCasesListsModel $caselist)
    {
        $this->model = $caselist;
        $this->mainIndex = route('ebayCases.index');
        $this->mainTitle = 'Ebay cases';
        $this->viewPath = 'message.ebay_cases.';
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $response = [
            'metas'    => $this->metas(__FUNCTION__),
            'data'     => $this->autoList($this->model),
            'status'   => $this->model->distinct()->get(['status']),
            'types'     => $this->model->distinct()->get(['type']),
        ];
        return view($this->viewPath . 'index',$response);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = $this->model->find($id);
        if(empty($data)){
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', $this->mainTitle . '不存在.'));
        }
        if($data->process_status != 'COMPLETE' && $data->process_status != 'PROCESS'){
            $data->process_status = 'PROCESS';
            $data->save();
        }
        $response = [
            'metas' => $this->metas(__FUNCTION__),
            'case'  => $data,
            'case_order_info' => $data->CaseOrderInfo

        ];
        return view($this->viewPath . 'edit',$response);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * case回复：给用户回复消息
     */
    public function MessageToBuyer(){
        $request = request()->input();
        $ebay = new EbayAdapter($this->model->find(request()->input('id'))->account->ApiConfig);
        $case_obj = $this->model->find($request['id']);
        $caseAry['caseType'] = $case_obj->type;
        $caseAry['caseId'] = $case_obj->id;
        $caseAry['messageToBuyer'] = trim($request['messgae_content']);

        if($ebay->offerOtherSolution($caseAry)){
            $case_obj->assign_id = request()->user()->id;
            $case_obj->process_status = 'COMPLETE';
            $case_obj->save();
            return redirect($this->mainIndex)->with('alert', $this->alert('success', $this->mainTitle . '处理成功.'));

        }else{
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', $this->mainTitle . '处理失败.'));
        }
    }

    /**
     * case回复：提供客户追踪信息
     */
    public function AddTrackingDetails(){

        $request = request()->input();
        $ebay = new EbayAdapter($this->model->find($request['id'])->account->ApiConfig);
        $case_obj = $this->model->find($request['id']);

        $caseId = $case_obj->id;
        $caseType = $case_obj->type;
        $comments = $request['comments'];


        if($request['is_tracked'] == 10){
            $trackingNumber = $request['trackingNumber'];
            $carrierUsed = $request['carrier'];
            $result = $ebay->provideTrackingInfo(compact('caseId','caseType','comments','trackingNumber','carrierUsed'));

        }else{
            $carrierUsed = $request['carrierUsed'];
            $shippedDate = $request['shippedDate'];
            $result = $ebay->provideTrackingInfo(compact('caseId','caseType','comments','shippedDate','carrierUsed'));
        }

        if($result){
            $case_obj->assign_id = request()->user()->id;
            $case_obj->process_status = 'COMPLETE';
            $case_obj->save();
            return redirect($this->mainIndex)->with('alert', $this->alert('success', '处理成功.'));
        }else{
            return redirect($this->mainIndex)->with('alert', $this->alert('danger',  '处理失败.'));
        }
    }

    /**
     * case 回复： 退款
     */
    public function RefundBuyer(RefundModel $refund){
        
        $reason  = request()->input('refund-reason');
        $comment = request()->input('comment');
        $case    = $this->model->find(request()->input('id'));

        if(empty($case) || empty($case->transaction_id)){
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', '无法处理，数据不完整'));
        }
        $ebay     = new EbayAdapter($case->account->ApiConfig);
        $caseId   = $case->id;
        $caseType = $case->type;

        $relation_order = $case->orderItem->order;

        if($ebay->caseFullRefund(compact('caseId','caseType','comment'))){
            $case->process_status = 'COMPLETE';
            $case->save();

            $refund->type            = 'FULL';
            $refund->refund          = 2;
            $refund->refund_currency = $relation_order->currency;
            $refund->price           = $relation_order->amount;
            $refund->refund_amount   = $relation_order->amount;
            $refund->reason          = $reason;
            $refund->order_id        = $relation_order->id;
            $refund->process_status  = 'COMPLETE';
            $refund->customer_id     = request()->user()->id;
            $refund->channel_id      = $relation_order->channel_id;
            $refund->save();
            return redirect($this->mainIndex)->with('alert', $this->alert('success', '退款成功！'));
        }else{
            $refund->type            = 'PARTIAL';
            $refund->refund          = 2;
            $refund->refund_currency = $relation_order->currency;
            $refund->price           = $relation_order->amount;
            $refund->refund_amount   = $relation_order->amount;
            $refund->reason          = $reason;
            $refund->order_id        = $relation_order->id;
            $refund->process_status  = 'FAILED';
            $refund->customer_id     = request()->user()->id;
            $refund->channel_id      = $relation_order->channel_id;
            $refund->save();

            return redirect($this->mainIndex)->with('alert', $this->alert('danger', '退款失败！'));
        }
    }

    public function PartRefundBuyer(RefundModel $refund){
        $form = request()->input();
        $case = $this->model->find(request()->input('id'));

        if(empty($form['amount']) || empty($case)){
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', '退款失败，参数不完整'));
        }
        $ebay = new EbayAdapter($case->account->ApiConfig);
        $caseId   = $case->id;
        $caseType = $case->type;
        $comment  = $form['comment'];
        $amount   = $form['amount'];
        $relation_order = $case->orderItem->order;

        if($ebay->casePartRefund(compact('caseId','caseType','comment','amount'))){
            $case->process_status = 'COMPLETE';
            $case->save();
            
            $refund->type            = 'PARTIAL';
            $refund->refund          = 2;
            $refund->refund_currency = $relation_order->currency;
            $refund->price           = $relation_order->amount;
            $refund->refund_amount   = $relation_order->amount;
            $refund->reason          = $form['reason'];
            $refund->order_id        = $relation_order->id;
            $refund->process_status  = 'COMPLETE';
            $refund->customer_id     = request()->user()->id;
            $refund->channel_id      = $relation_order->channel_id;
            $refund->save();
            return redirect($this->mainIndex)->with('alert', $this->alert('success', '部分退款成功！'));
        }else{
            $refund->type            = 'PARTIAL';
            $refund->refund          = 2;
            $refund->refund_currency = $relation_order->currency;
            $refund->price           = $relation_order->amount;
            $refund->refund_amount   = $relation_order->amount;
            $refund->reason          = $form['reason'];
            $refund->order_id        = $relation_order->id;
            $refund->process_status  = 'FAILED';
            $refund->customer_id     = request()->user()->id;
            $refund->channel_id      = $relation_order->channel_id;
            $refund->save();
            return redirect($this->mainIndex)->with('alert', $this->alert('danger', '部分退款失败'));
        }
    }
}
