<?php
//大订单模型
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/4/28
 * Time: 9:33
 */
namespace Common\Logic;
class OrdersLogic extends \Think\Model{
	
    // 订单总量完成情况
    public function getOrderFinish($order_id){
        $res = M('bill')->where(array('order_id' => $order_id, 'state' => 6))->sum('end_w');
        return $res?$res['tp_sum']:0;
    }

    // 订单剩余数量
    public function resQuantity($order_id){
    	$order_quantity = D('Orders')->quantity($order_id);
    	$finish_weight = $this->getOrderFinish($order_id);
    	return ($order_quantity - $finish_weight);
    }

    // 安排的吨数是否过大（大于订单总量）
    public function isArrOverOrder($arr_quantity, $order_id){
    	$res_quanity = $this->resQuantity($order_id);
        return ($arr_quantity > $res_quanity)?true:false;
    }

    // 订单是否完成
    public function isFinished($order_id){
    	$order_quantity = D('Order')->quantity($order_id);
    	$finish_weight = $this->getOrderFinish($order_id);
    	return ($order_quantity == $finish_weight)?true:false;
    }
}