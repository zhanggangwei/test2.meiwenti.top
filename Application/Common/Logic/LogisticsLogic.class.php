<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/4/18
 * Time: 14:18
 */
namespace Common\Logic;

class LogisticsLogic extends \Think\Model{

    /**
     * 贸易商安排物流计划
     * @param $order_id
     * @param $assigned_id
     * @param $price
     * @param $quantity
     * @return mixed
     */
    public function arrange($order_id, $assigned_id, $price, $quantity){
        $data = array(
            'order_id' => $order_id,
            'quantity' => $quantity,
            'res_quantity' => $quantity,
            'price' => $price,
            'assign_id' => session('company_id'),
            'assigned_id' => $assigned_id,
        );
        $res = D('Logistics')->addData($data);
        D('GeneralCompany', 'Logic')->log($assigned_id, 2);
        return $res;
    }

    public function reArr($Logistics_id, $company_id){

    }

    /**
     * 手动添加物流计划
     * @param $order_id
     * @param $assign_id
     * @param $quantity
     * @return mixed
     */
    public function arrManual($order_id, $assign_id, $quantity){
        $data = array(
            'order_id' => $order_id,
            'quantity' => $quantity,
            'res_quantity' => $quantity,
            'price' => 0,
            'assign_id' => $assign_id, // 指派者是承运商
            'assigned_id' => session('company_id'), // 指定自己是物流公司
        );
        $res = D('Logistics')->addData($data);
        D('GeneralCompany', 'Logic')->log($assign_id, 2);
        return $res;
    }

    // 收回物流计划
    public function back($logistics_id){
        $res = $this->save(array('id' => $logistics_id, 'state' => 0));
        return ($res !== false)?true:false;
    }
    
}