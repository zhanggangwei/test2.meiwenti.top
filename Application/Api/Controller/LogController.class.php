<?php
/**
 * log记录
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/6/13
 * Time: 10:54
 */
namespace Api\Controller;
use Think\Controller;
class LogController extends ApiController
{
    // 记录车辆排队情况
    public function truckOrderLog(){
        $times = I('times');
        $text = I('text');
        if (!$times || !$text) {
            $res['code'] = 0;
            $res['message'] = '参数错误';
            backJson($res);
        }
        $data = array(
            'times'  => $times,
            'text'  => $text,
            'create_time'  => get_time(),
        );
        $result = M('truck_order_log')->add($data);
        if ($result) {
            $res['code'] = 1;
            $res['message'] = '保存成功';
            backJson($res);
        } else {
            $res['code'] = 0;
            $res['message'] = '保存失败';
            backJson($res);
        }
    }

    // 记录车辆排队情况
    public function autoBillLog(){
        $times = I('times');
        $lic_number = I('lic_number');
        $bill_number = I('bill_number');
        $reason = I('reason');
        if (!$times || !$lic_number || !$bill_number || !$reason) {
            $res['code'] = 0;
            $res['message'] = '参数错误';
            backJson($res);
        }
        $data = array(
            'times'  => $times,
            'lic_number'  => $lic_number,
            'bill_number'  => $bill_number,
            'reason'  => $reason,
            'create_time'  => get_time(),
        );
        $result = M('auto_bill_log')->add($data);
        if ($result) {
            $res['code'] = 1;
            $res['message'] = '保存成功';
            backJson($res);
        } else {
            $res['code'] = 0;
            $res['message'] = '保存失败';
            backJson($res);
        }
    }
}