<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017-07-15
 * Time: 16:47
 */
namespace Admin\Controller;

class TruckController extends CommonController
{
    // 车辆状态
    public function normal()
    {
        // 公司名
        $company = M('truck t')->field('t.user_id, c.name')
            ->join('left join coal_company c on c.id = t.user_id')
            ->where(array('t.is_passed' => 1, 't.user_id' => array('not in',array('0','294','300','303','304','307'))))
            ->group('t.user_id')->select();
        // dump($company);exit;
        foreach ($company as $key => $val) {
            if ($val['user_id'] == 0) {
                $company[$key]['name'] = '未使用';
            }
        }
        // 车辆状态
        $truck_state = array(
           array(1, '空车'),
           array(2, '待接单'),
           array(3, '在路上'),
           array(4, '车辆异常'),
        );
        $this->truck_state = $truck_state;
        $this->company = $company;
        $this->display();
    }

    public function getNormal(){
        $db = M('truck t');
        $where = array(
            't.user_id' => array('not in',array(0,'294','300','303','304','307'))
        );

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        if ($lic_number = trim(I('lic_number', ''))) {
            $where['t.lic_number'] = array('like', '%'.$lic_number.'%');
            $lic_number = '';
        }
        if ($owner_order = trim(I('owner_order', ''))) {
            $where['t.owner_order'] = $owner_order;
            $owner_order = '';
        }
        if ($truck_state = trim(I('truck_state', ''))) {
            $where['t.state'] = $truck_state;
            $truck_state = '';
        }
        $user_id = I('user_id', -1);
        if ($user_id != '' && $user_id > -1) {
            $where['t.user_id'] = $user_id;
        }
        $user_id = '';

        $count = $db
            ->join('left join coal_company c on c.id = t.owner_id')
            ->where($where)
            ->count();

        $data = $db
            ->field('t.*, c.name as company_name')
            ->join('left join coal_company c on c.id = t.user_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->select();
        // sql();
        // dump_text($data);
        foreach ($data as $key => $value) {

            // $tmp_info = M('driver')->where(array('truck_id' => $value['id']))->select();
            // if (count($tmp_info) == 0) {
            //     $data[$key]['driver'] = '还没有绑定司机';
            // } else if (count($tmp_info) == 1) {
            //     $data[$key]['driver'] = M('users')->where(array('id' => $tmp_info[0]['uid']))->getField('real_name');
            // } else {
            //     $tmp_name = M('users')->where(array('id' => $tmp_info[0]['uid']))->getField('real_name');
            //     $data[$key]['driver'] = $tmp_name . '等';
            // }

            // 1、车辆状态
            $data[$key]['truck_state'] = getTruckType($value['state']);
            // 2、司机是否休息
            $is_driver_work = '';
            $driver_info = M('driver d')
                ->field('d.*,u.real_name')
                ->join('left join coal_users u on u.id = d.uid')
                ->where(array('d.truck_id' => $value['id']))->select();
            if ($driver_info) {
                // dump($driver_info);
                foreach ($driver_info as $k => $val) {
                    $is_driver_work .= $val['real_name'];
                    if ($val['is_passed'] != 1) {
                        $is_driver_work .= '(未审核)<br>';
                    } else {
                        $is_driver_work .= '(';
                        // dump($val['work_time']);
                        // switch ($val['work_time'] + 0) {
                        //     case 1:
                        //         $is_driver_work .= '全天';
                        //         break;
                        //     case 2:
                        //         $is_driver_work .= '白班';
                        //         break;
                        //     case 3:
                        //         $is_driver_work .= '夜班';
                        //         break;
                        //     default:
                        //         break;
                        // }
                        // $is_driver_work .= ' ';
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
            $data[$key]['driver_state'] = $is_driver_work;
            // 3、订单状态
            $bill_state = '';
            $is_have_bill = '';
            $bill_info = M('bill')->where(array('truck_id' => $value['id'], 'state' => array('lt', 6)))->find();
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
                // echo '000'.',';
                $is_have_bill = $bill_state;
            } else {
                // echo '111'.',';
                $is_have_bill = '没有订单';
            }
            // echo $is_have_bill;
            // echo $value['id'].',';
            $data[$key]['bill_state'] = $is_have_bill;
            // 4、sap结果，排队车辆反馈情况
            $feedback_res = M('truck_restatue')
                ->where(array('lic_number' => $value['lic_number'], 'sys_name' => array('in', $value['jiyun']), 'is_latest' => 1))
                ->order('update_time desc')->select();
            $sap_result = '';
            if ($feedback_res) {
                foreach ($feedback_res as $val) {
                    $sap_result .= '集运站：'.$val['sys_name'].',时间：'.date('m-d H:i:s', strtotime($val['update_time'])).'<br>原因：(flag:'.$val['state_code'].')'.$val['state'].'<br>';
                }
                $remark = '请联系sap处理';
            }
            $data[$key]['sap_result'] = $sap_result;
            // $option1 = "{
            //     id:'com_truck_edit',
            //     data:{id:'".$value['id']."'},
            //     url:'".U('editCompanyTruck')."',
            //     width:'800',
            //     height:'600'
            //     }";
            // $option2 = "{
            //     id:'com_truck_driver_manage',
            //     data:{id:'".$value['id']."'},
            //     url:'".U('drivers')."',
            //     width:'800',
            //     height:'600'
            //     }";
            // $option3 = "{
            //     type:'get',
            //     data:{id:'".$value['id']."'},
            //     url:'".U('del')."',
            //     confirmMsg:'确定要删除吗？'
            //     }";
            // $dostr = create_button($option1, 'dialog', '编辑').create_button($option2, 'dialog', '司机管理').create_button($option3, 'doajax', '删除');
            // echo $dostr;exit;
            // $data[$key]['dostr'] = $dostr;
        }
        // dump($data);exit;
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }
}