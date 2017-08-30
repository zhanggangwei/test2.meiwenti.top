<?php
/**
 * Created by PhpStorm.
 * 异常故障自查
 * User: zgw
 * Date: 2017/5/2
 * Time: 11:23
 */
namespace Vip\Controller;

class SelfExamController extends CommonController {
	public function truck(){
		// if (IS_POST) {
		//     $lic_number = I('shijian').I('puhao').I('c1').I('c2').I('c3').I('c4').I('c5');
         //    $truck_info = M('truck')->where(array('lic_number' => trim($lic_number)))->find();
        //
         //    if (!$truck_info) {
         //        $this->error('没有这辆车');exit;
         //    }
        //
         //    // 1、车辆状态
         //    $truck_state = '';
         //    switch ($truck_info['state']) {
         //        case 1:
         //            $truck_state = '空车';
         //            break;
         //        case 2:
         //            $truck_state = '待接单';
         //            break;
         //        case 3:
         //            $truck_state = '在路上';
         //            break;
         //        case 4:
         //            $truck_state = '车辆暂时不可用'; // 2017年6月27日15:25:51 zgw 我觉得应该带上原因
         //            break;
         //        default:
         //            break;
         //    }
        //
        //
         //    // 如果有车辆id，是否可以派单
         //    // $res = D('Truck', 'Logic')->isDispatch($truck_id);
         //    // if ($res['code'] != 1) {
         //    //     $res['statusCode'] = 300;
         //    // } else {
         //    //     $res['statusCode'] = 200;
         //    // }
        //
         //    $res = array(
         //        '车辆状态'=>$truck_state,
         //        '司机是否休息'=>'',
         //        '司机排班'=>'',
         //        '是否有单'=>'',
         //        '订单状态'=>'',
         //    );
         //    $this->res = $res;
		// }
		$this->display();
	}

	public function getTruckCheckRes(){
	    if (I('xiaohao')) {
            $truck_info = M('truck')->where(array('owner_order' => trim(I('xiaohao'))))->find();
        } else {
            $truck_info = M('truck')->where(array('lic_number' => trim(I('lic'))))->find();
        }
        $remark = '';
        if (!$truck_info) {
            $res = array(
                'truck_state'=>'没有这辆车',
                'driver_state'=>'',
                'bill_state'=>'',
                'sap_result'=>'',
                'remark'=>'请仔细核对车牌号',
            );
            echo json_encode($res);exit;
        }
        if ($truck_info['user_id'] != session('company_id')) {
            $res = array(
                'truck_state'=>'不是本公司使用的车',
                'driver_state'=>'',
                'bill_state'=>'',
                'sap_result'=>'',
                'remark'=>'只能查看本公司使用的车',
            );
            echo json_encode($res);exit;
        }
        if ($truck_info['is_passed'] != 1) {
            $res = array(
                'truck_state'=>'还没有审核通过',
                'driver_state'=>'',
                'bill_state'=>'',
                'sap_result'=>'',
                'remark'=>'请联系相关人员审核',
            );
            echo json_encode($res);exit;
        }

        // 1、车辆状态
        $truck_state = '';
        switch ($truck_info['state']) {
            case 1:
                $truck_state = '空车';
                break;
            case 2:
                $truck_state = '待接单';
                break;
            case 3:
                $truck_state = '在路上';
                break;
            case 4:
                $truck_state = '车辆暂时不可用'; // 2017年6月27日15:25:51 zgw 我觉得应该带上原因
                break;
            default:
                break;
        }
        // 2、司机是否休息
        $is_driver_work = '';
        $driver_info = M('driver d')
            ->field('d.*,u.real_name')
            ->join('left join coal_users u on u.id = d.uid')
            ->where(array('d.truck_id' => $truck_info['id']))->select();
        if ($driver_info) {
            // dump($driver_info);
            foreach ($driver_info as $key => $val) {
                $is_driver_work .= $val['real_name'];
                if ($val['is_passed'] != 1) {
                    $is_driver_work .= '(未审核)<br>';
                } else {
                    $is_driver_work .= '(';
                    // dump($val['work_time']);
                    switch ($val['work_time'] + 0) {
                        case 1:
                            $is_driver_work .= '全天';
                            break;
                        case 2:
                            $is_driver_work .= '白班';
                            break;
                        case 3:
                            $is_driver_work .= '夜班';
                            break;
                        default:
                            break;
                    }
                    $is_driver_work .= ' ';
                    switch ($val['is_work'] + 0) {
                        case 0:
                            $is_driver_work .= '休息';
                            break;
                        case 1:
                            $is_driver_work .= '工作';
                            break;
                        default:
                            break;
                    }
                    $is_driver_work .= ')<br>';
                }
            }
        } else {
            $is_driver_work = '还没有绑定司机';
        }
        // 3、订单状态
        $bill_state = '';
        $bill_info = M('bill')->where(array('truck_id' => $truck_info['id'], 'state' => array('lt', 6)))->find();
        if ($bill_info) {
            switch ($bill_info['state']) {
                case 1:
                    $bill_state = "待接单";
                    break;
                case 2:
                    $bill_state = "已接单";
                    break;
                case 3:
                    $bill_state = "提货地进矿";
                    break;
                case 4:
                    $bill_state = "提货地出矿";
                    break;
                case 5:
                    $bill_state = "送货地进矿";
                    break;
                default:
                    $bill_state = "状态非法";
                    break;
            }
            $is_have_bill = '有未完成的单('.$bill_state.')';
        } else {
            $is_have_bill = '没有未完成的单';
        }
        // 4、sap结果，排队车辆反馈情况
        $feedback_res = M('truck_restatue')
            ->where(array('lic_number' => $truck_info['lic_number'], 'sys_name' => array('in', $truck_info['jiyun']), 'is_latest' => 1))
            ->order('update_time desc')->select();
        $sap_result = '';
        if ($feedback_res) {
            foreach ($feedback_res as $val) {
                $sap_result .= '集运站：'.$val['sys_name'].',时间：'.date('m-d H:i:s', strtotime($val['update_time'])).'<br>原因：(flag:'.$val['state_code'].')'.$val['state'].'<br>';
            }
            $remark = '请联系sap处理';
        }
        // 5、备注
        // 情况1：车辆为什么不能派单？
        $dispatch_res = D('Truck', 'Logic')->isDispatch($truck_info['id']);
        if ($dispatch_res['code'] == 0) {
            $remark = '不能派单原因：'.$dispatch_res['message'];
        }

        $res = array(
            'truck_state'=>$truck_state,
            'driver_state'=>$is_driver_work,
            'bill_state'=>$is_have_bill,
            'sap_result'=>$sap_result,
            'remark'=>$remark,
        );
        echo json_encode($res);exit;
    }
}