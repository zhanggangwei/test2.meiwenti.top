<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/4/17
 * Time: 22:16
 */
namespace Vip\Controller;

class BillController extends CommonController {
    private $finishedBill_data=array();
    // 手动添加提煤单
    public function addHandBill(){
        if (IS_POST) {
            $post = I('post.');

            // 得到买卖双方的公司id
            $buyer_id = A('Orders')->getOrderCompanyId($post['buyer_id']);
            $seller_id = A('Orders')->getOrderCompanyId($post['seller_id']);

            // 根据承运方类型，得到承运方
            $carrier_type = I('carrier_type', 0);
            $carrier = $carrier_type?$buyer_id:$seller_id;

            if ($buyer_id == $seller_id) {
                alert('双方不能是同一个公司', 300);
            }

            M()->startTrans();
            // 1、增加大订单
            $post['order_id'] = 'HAND'.session('phone').'T'.time();
            $post['buyer_id'] = $buyer_id;
            $post['seller_id'] = $seller_id;
            $post['order_type'] = 3;
            $post['price'] = 0;
            $post['total_money'] = 0;
            $post['is_private'] = 1;
            $post['creator'] =  session('user_id');

            $res = D('Orders')->addData($post);
            $detail_data = array(
                'orders_id' => $res['message'],
                'begin_address' => $post['address'],
                'end_address' => $post['address1']
            );
            $detail_data_res = M('orders_address_detail')->add($detail_data);
            // 2、添加物流安排
            $quantity = $post['quantity'];
            $logistics_res = D('Logistics', 'Logic')->arrManual($post['order_id'], $carrier, $quantity);

            // 3、提煤单（有车辆要直接派单）
            // D('Bill', 'Logic')->arr();
            if (($truck_id = $post['truck_id'] + 0) > 0) {
                // 如果有车，车的重量不能大于40
                // if ($post['quantity'] > 40 ) {
                //     alert('指派吨数不能大于40吨', 300);
                // }
                $res2 = D('Bill','Logic')->arr($logistics_res['message'],$truck_id,session('company_id'),40);
            } else {
                $res2['code'] = 1;
            }

            if ($res['code'] && $detail_data_res && $logistics_res['message'] && $res2['code']) {
                M()->commit();
                after_alert(array('closeCurrent' => true));
            } else {
                M()->rollback();
                alert_false();
            }
        }
        // 获得煤的种类
        $this->coalType = M('coal_type')->select();
        $this->display();
    }

    // 搜索可用派单车辆
    public function searchBillTruck(){
        $db = M('truck');
        $where = array();
        $where['user_id'] = session('company_id');
        $where['is_passed'] = 1;
        $where['is_work'] = 1;
        $where['state'] = 1;
        $where['is_comperation'] = 1;

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        if ($name = trim(I('lic_number', ''))) {
            $where['lic_number'] = array('like', '%'.$name.'%');
        }
        // if ($account = I('account', '')) {
        //     $where['a.account'] = array('like', $account.'%');
        // }

        $count = $db
            ->where($where)
            ->count();

        $data = $db
            ->field('id,lic_number')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('last_time asc')
            ->select();
        // sql();
        $data1 = array();
        $i = 0;
        foreach ($data as $key => $value) {
            $is_dispatch = D('Truck', 'Logic')->isDispatch($value['id']);
            if ($is_dispatch['code'] == 1) {
                $data1[$i] = $value;
                // $data1[$i]['last_time'] = getday($value['last_time']);
                //
                // // 拥有者
                // if ($value['owner_type'] == 1) {
                //     $name = M('users')->where(array('id' => $value['owner_id']))->getField('real_name');
                // } else {
                //     if ($value['owner_id'] == session('company_id')) {
                //         $name = '自有车辆';
                //     } else {
                //         $name = M('company')->where(array('id' => $value['owner_id']))->getField('name');
                //     }
                // }
                // $data1[$i]['owner_name'] = $name;
                // $option1 = "{
                //     id:'logistics_add',
                //     url:'".U('Vip/Logistics/addLogistics')."',
                //     data:{id:'".$value['id']."'},
                //     type:'get',
                //     height:'450',
                //     width:'600'
                // }";
                //
                // $dostr = '<button type="button" class="btn-default" data-toggle="dialog" data-options="'.$option1.'">安排物流</button>';
                // $data1[$i]['dostr'] = $dostr;
                $i++;
            }
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data1,
        );
        echo json_encode($info);
    }

    // 新增煤种
    public function addCoalType(){
        if (IS_POST) {
            $post = I('post.');
            $res = D('CoalType')->addData($post);
            if ($res['code']) {
                after_alert(array('tabid' => 'Bill_addHandBill', 'closeCurrent' => true));
            } else {
                alert($res['message'], 300);
            }
        }
        $this->display();
    }

    // 物流公司首页,派单
    public function arrBill(){
        if (IS_POST) {
            $logistics_id = I('post.logistics_id');
            $truck_id     = I('post.truck_id');
            $company_id   = session('company_id');
            $arr_w        = I('post.arr_w');
            if (!$logistics_id || !$truck_id) alert('还没有指定车辆',300);
            if ($arr_w <= 0) alert('安排吨数不能小于0',300);
            // 首页派单
            $res = D('Bill', 'Logic')->arr($logistics_id, $truck_id, $company_id, $arr_w);
            // $res = json_decode($res);
            // dump($res);exit;
            if ($res['code'] == 1) {
                after_alert(array('closeCurrent' => true, 'datagrids' => 'latest-logistics-filter,wait-truck-filter'));
            } else {
                alert($res['message'], 300);
            }
        }
        $this->logistics_id = I('get.id');
        $this->type = I('get.type');
        $this->display();
    }

    // 待接单
    public function takeBill(){
        $sellers = M('bill b')
            ->field('b.seller as id, c.name')
            ->join('left join coal_link_yitai y on y.bill_id = b.id')
            ->join('left join coal_company c on c.id = b.seller')
            ->where(array('b.state' => 6, 'b.create_type' => 1, 'b.company' => session('company_id')))
            ->group('seller')->select();
        $this->sellers = $sellers;
        $this->display();
    }

    // 获取待接单数据
    public function getTakeBill(){
        $db = M('bill b');
        $where = array();
        $where['b.company'] = session('company_id');
        $where['b.state'] = 1;

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        if ($lic_number = trim(I('lic_number', ''))) {
            $where['t.lic_number'] = array('like', '%'.$lic_number.'%');
            $lic_number = '';
        }
        if ($owner_order = trim(I('owner_order', ''))) {
            $where['t.owner_order'] = array('like', '%'.$owner_order.'%');
            $owner_order = '';
        }
        if ($seller = I('seller', '')) {
            $where['c.name'] = array('like', '%'.$seller.'%');
            $seller = '';
        }

        $count = $db
            ->join('left join coal_driver dr on dr.id = b.driver_id')
            ->join('left join coal_users u on u.id = dr.uid')
            ->join('left join coal_users u1 on u1.id = b.trucker_id')
            ->join('left join coal_company a on a.id = b.buyer')
            ->join('left join coal_company c on c.id = b.seller')
            ->join('left join coal_truck t on t.id = b.truck_id')
            ->join('left join coal_link_yitai y on y.bill_id = b.id')
            ->where($where)
            ->count();

        $data = $db
            ->field('b.*, u.real_name as driver_name, u1.real_name as trucker_name,u1.phone as trucker_phone, u.phone as driver_phone, a.name as buyer_name, c.name as seller_name, t.lic_number,t.owner_order,t.id as truck_id')
            ->join('left join coal_driver dr on dr.id = b.driver_id')
            ->join('left join coal_users u on u.id = dr.uid')
            ->join('left join coal_users u1 on u1.id = b.trucker_id')
            ->join('left join coal_company a on a.id = b.buyer')
            ->join('left join coal_company c on c.id = b.seller')
            ->join('left join coal_truck t on t.id = b.truck_id')
            ->join('left join coal_link_yitai y on y.bill_id = b.id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('y.dis_time asc')
            ->select();
        // sql();
        foreach ($data as $key => $value) {
            $dis_time = $value['dis_time'];
            // 是否伊泰
            if ($value['create_type'] == 1) {
                $yitai = M('link_yitai')->where(array('bill_id' => $value['id']))->find();
                if (!$yitai) {
                    alert_illegal('异常单号，请联系煤问题技术部QQ453761770');
                }
                $value['order_id'] = $yitai['bill_number'];
                $value['dis_time'] = yitai_time($yitai['dis_time']);
                $dis_time = $yitai['dis_time'];
            }

            $data[$key]['order_id'] = $value['order_id'] . '<br/>(' . $value['dis_time'] . ')';
            //耗时
            $data[$key]['take_time']=getday($dis_time);
            $data[$key]['trucker_name']=$value['trucker_name'].'<br/>'.$value['trucker_phone'];
            //车辆绑定的司机
            $drivers=M('driver d')
                ->join('coal_users u on d.uid=u.id')
                ->where(array('d.truck_id'=>$value['truck_id']))
                ->field('u.real_name as driver_name,u.phone as driver_phone,u.id as driver_id')
                ->select();
            $data[$key]['driver']='';
            foreach ($drivers as $kk =>$vv){
                if(D('Driver',"Logic")->is_work($vv['driver_id'])){
                    $data[$key]['driver']=$data[$key]['driver'].$vv['driver_name'].'('.$vv['driver_phone'].')';
                }
            }
            $data[$key]['driver']=$data[$key]['driver'].'<br/>';

            // 车牌号带车小号
            if ($value['owner_order']) {
                $owner_order = '<br>('.$value['owner_order'].')';
            } else {
                $owner_order = '';
            }
            $data[$key]['lic_number'] = $data[$key]['lic_number'].$owner_order;

            // 买家、卖家
            $data[$key]['buy_sell'] = $value['seller_name'] . '<br/>==><br/>' . $value['buyer_name'] . '';

            // 操作
            $dostr = '';
            switch ($value['create_type']) {
                case 1:
                    // 伊泰
                    // 1、物流公司退单。向SAP提出退单申请，rbill = 5,挂起状态
                    $rbill = M('driver_rbill')->where(array('bill_id' => $value['id'], 'state' => 5))->find();
                    if (!$rbill) {
                        // 物流公司退单
                        $option2 = "{
                        id:'bill_back',
                        url:'" . U('Vip/Bill/backBill') . "',
                        data:{bill_id:'" . $value['id'] . "',type:2},
                        type:'get',
                        height:'300',
                        width:'485'
                        }";
                        // $dostr .= create_button($option2, 'dialog', '物流公司退单');
                    } else {
                        $option4 = "{
                            url:'".U('Vip/Bill/delApplyForSap')."',
                            data:{id:'".$value['id']."'},
                            type:'get',
                            confirmMsg:'确定要操作吗？'
                        }";
                        // $dostr .= create_button($option4, 'doajax', '取消sap申请');
                    }
                    // $dostr .= '如需退单请联系调度，';
                    break;
                default:
                    // 系统
                    $option = "{
                        id:'Return_bill',
                        title:'重新派单',
                        url:'".U('Vip/Bill/reArrBill')."',
                        data:{bill_id:'".$value['id']."'},
                        type:'get',
                        height:'500',
                        width:'800'
                    }";
                    $dostr .= create_button($option, 'dialog', '重新派单');
                    break;
            }
            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        // dump($info);exit;
        echo json_encode($info);
    }

    // 司机退单页面
    public function returnBill(){
        // 很无语的操作，2017年5月23日11:43:57 zjw提的，当bill状态是6是，更新所有rbill的状态，待优化
        $res = M('driver_rbill drb')
            ->field('drb.id')
            ->join('left join coal_bill b on b.id = drb.bill_id')
            ->where(array('b.state' => 6, 'drb.state' => array('in', '1,5')))
            ->select();
        if ($res) {
            foreach ($res as $key => $value) {
                M('driver_rbill')->save(array('id' => $value['id'], 'state' => 4));
            }
        }
        $this->display();
    }

    // 司机退单,获得退单列表
    public function getReturnBill(){
        $db = M('driver_rbill drb');
        $where = array();
        $where['b.company'] = session('company_id');
        $where['drb.state'] = array('in', '1,5');

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        if ($name = trim(I('lic_number', ''))) {
            $where['t.lic_number'] = array('like', '%'.$name.'%');
        }
        // if ($account = I('account', '')) {
        //     $where['a.account'] = array('like', $account.'%');
        // }

        $count = $db
            ->join('left join coal_bill b on b.id = drb.bill_id')
            ->join('left join coal_users u on u.id = b.driver_id') // 司机个人信息
            ->join('left join coal_company a on a.id = b.buyer') // 买家信息
            ->join('left join coal_company c on c.id = b.seller') // 卖家信息
            ->join('left join coal_truck t on t.id = b.truck_id')
            ->where($where)
            ->count();

        $data = $db
            ->field('drb.*, u.real_name as driver_name, u.phone as driver_phone, a.name as buyer_name, 
            c.name as seller_name, b.arrange_w, b.do_time, b.order_id,t.lic_number,t.owner_order, 
            b.buyer, b.seller, b.truck_id, b.driver_id, b.create_type')
            ->join('left join coal_bill b on b.id = drb.bill_id')
            ->join('left join coal_users u on u.id = b.driver_id') // 司机个人信息
            ->join('left join coal_company a on a.id = b.buyer') // 买家信息
            ->join('left join coal_company c on c.id = b.seller') // 卖家信息
            ->join('left join coal_truck t on t.id = b.truck_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('drb.return_time desc, drb.id desc')
            ->select();
        // sql();
        foreach ($data as $key => $value) {
            // 买家名字和手机号
            $data[$key]['buyer_name'] =  $value['buyer_name'] . '<br/>(' . get_company_phone($value['buyer']) . ')';
            // 卖家名字和手机号
            $data[$key]['seller_name'] =  $value['seller_name'] . '<br/>(' . get_company_phone($value['seller']) . ')';
            // 车主名字和手机号
            $tmp = get_trucker_name_and_phone($value['truck_id']);
            $data[$key]['trucker_name'] =  $tmp['trucker_name'] . '<br/>(' . $tmp['phone'] . ')';
            // 派过单的司机名和电话（以后优化）
            $data[$key]['driver'] = $value['driver_name'] . '<br/>(' . $value['driver_phone'] . ')';
            // 司机确认接单时间
            $data[$key]['do_time'] = getday($value['do_time']);
            // 车牌号带车小号
            if ($value['owner_order']) {
                $owner_order = '<br>('.$value['owner_order'].')';
            } else {
                $owner_order = '';
            }
            $data[$key]['lic_number'] = $data[$key]['lic_number'].$owner_order;

            $yitai = M('link_yitai')->where(array('bill_id' => $value['bill_id']))->find();
            // 2017年6月8日14:57:51 zgw 伊泰派单系统，bill表设计没有扩展性，如果有其他系统，就要循环所有对接的表。
            //应该是bill表有一个外键（唯一），关联到一个表，然后对接所有系统。
            if ($yitai) {
                $dis_time = '<br>伊泰派单时间：'.$yitai['dis_time'].'<br>派单用时：'.$yitai['dis_use_time'];

                // 提煤单号,是伊泰的就用伊泰单号
                // 2017年6月22日9:33:58 zgw edit zjw 说伊泰的单就显示伊泰的单号
                $data[$key]['order_id'] = $yitai['bill_number'];
            }

            $option = "{
                id:'Return_bill',
                title:'重新派单',
                url:'".U('Vip/Bill/reArrBill')."',
                data:{bill_id:'".$value['bill_id']."'},
                type:'get',
                height:'500',
                width:'800'
            }";
            $option1 = "{
                url:'".U('Vip/Bill/returnPass')."',
                data:{bill_id:'".$value['bill_id']."'},
                type:'get',
                confirmMsg:'确定要废弃货单吗？'
            }";
            $option2 = "{
                url:'".U('Vip/Bill/returnRefuse')."',
                data:{id:'".$value['id']."',driver_id:'".$value['driver_id']."'},
                type:'get',
                confirmMsg:'确定要拒绝吗？'
            }";
            $option3 = "{
                url:'".U('Vip/Bill/applyForSap')."',
                data:{id:'".$value['id']."'},
                type:'get',
                confirmMsg:'确定要操作吗？'
            }";
            $option4 = "{
                url:'".U('Vip/Bill/delApplyForSap')."',
                data:{id:'".$value['bill_id']."'},
                type:'get',
                confirmMsg:'确定要操作吗？'
            }";
            if ($value['state'] == 5) {
                $dostr = create_button($option4, 'doajax', '取消sap申请');
            } else {
                if ($value['create_type'] != 1) {
                    $tmp_dostr = create_button($option, 'dialog', '重新派单') . create_button($option1, 'doajax', '废弃货单');
                    $sap_dostr = '';
                } else {
                    $tmp_dostr = ''; // 伊泰的单不显示
                    $sap_dostr = create_button($option3, 'doajax', '同意退单，向SAP申请');
                }
                $dostr = $tmp_dostr . create_button($option2, 'doajax', '拒绝退单')
                    . $sap_dostr
                ;
            }
            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 重新派单
    public function reArrBill(){
        if (IS_POST) {
            $bill_id = I('post.bill_id');
            $truck_id = I('post.truck_id');
            // $arr_w = I('post.arr_w');
            $company_id = session('company_id');

            if (!$bill_id || !$truck_id) alert('还没有指定车辆',300);
            $res = D('Bill', 'Logic')->reArr($bill_id, $truck_id, $company_id);
            if ($res['code'] == 1) {
                after_alert(array('message' => $res['message'], 'closeCurrent' => true, 'tabid' => 'Bill_returnBill'));
            } else {
                alert($res['message'], 300);
            }
        }
        $this->bill_info = M('bill')->find(I('bill_id'));
        $this->display();
    }
    // 废弃货单,废弃司机退回的货单
    public function returnPass(){
        //同意退单
        $bill_id = I('bill_id');
        if ($bill_id + 0 > 0) {
            $res = D('Bill', 'Logic')->returnPass($bill_id);
            show_res($res);
        } else {
            alert_illegal();
        }
    }
    //拒绝司机退回的货单
    public function returnRefuse(){
        $id = I('id');
        if (!$id) {
            alert('信息提交有误', 300);
        }
        $data['state']=3;
        $res=M('driver_rbill')->where(array('id'=>$id))->save($data);
        if($res!==false){
            $driver_id = I('driver_id');
            D('Push', 'Logic')->refuseRBill($driver_id);
            after_alert(array('fresh' => true));
        }else{
            alert_false();
        }
    }

    // 向SAP系统提出申请，物流公司退单
    public function applyForSap(){
        $id = I('id');
        if ($id) {
            $res = M('driver_rbill')->where(array('state' => 1, 'id' => $id))->save(array('state' => 5));
            if ($res !== false) {
                after_alert(array('tabid' => 'Bill_returnBill'));
            } else {
                alert_false();
            }
        } else {
            alert_false('参数错误');
        }
    }

    // 正在进行
    public function getDoingBill(){
        $db = M('bill b');
        $where = array();
        $where['b.company'] = session('company_id');
        $where['b.state'] = array('in', array(2,3,4,5));

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        if ($lic_number = trim(I('lic_number', ''))) {
            $where['t.lic_number'] = array('like', '%'.$lic_number.'%');
        }
        if ($owner_order = I('owner_order', '')) {
            $where['t.owner_order'] = array('like', '%'.$owner_order.'%');
        }

        $count = $db
            ->join('left join coal_driver dr on dr.id = b.driver_id')
            ->join('left join coal_users u on u.id = dr.uid')
            ->join('left join coal_users u1 on u1.id = b.trucker_id')
            ->join('left join coal_company a on a.id = b.buyer')
            ->join('left join coal_company c on c.id = b.seller')
            ->join('left join coal_truck t on t.id = b.truck_id')
            ->where($where)
            ->count();

        $data = $db
            ->field('b.*, u.real_name as driver_name, u1.real_name as trucker_name, u.phone as driver_phone, a.name as buyer_name, c.name as seller_name, t.lic_number,t.owner_order')
            ->join('left join coal_driver dr on dr.id = b.driver_id')
            ->join('left join coal_users u on u.id = dr.uid')
            ->join('left join coal_users u1 on u1.id = b.trucker_id')
            ->join('left join coal_company a on a.id = b.buyer')
            ->join('left join coal_company c on c.id = b.seller')
            ->join('left join coal_truck t on t.id = b.truck_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('b.dis_time desc')
            ->select();
        // sql();
        foreach ($data as $key => $value) {
            // 派单时间,如果有伊泰系统派单时间，派单用时
            $dis_time = '';
            $yitai = M('link_yitai')->where(array('bill_id' => $value['id']))->find();
            // 2017年6月8日14:57:51 zgw 伊泰派单系统，bill表设计没有扩展性，如果有其他系统，就要循环所有对接的表。应该是bill表有一个外键（唯一），关联到一个表，然后对接所有系统。
            if ($yitai) {
                $dis_time = '<br>伊泰派单时间：'.yitai_time($yitai['dis_time']).'<br>派单用时：'.$yitai['dis_use_time'];

                // 提煤单号,是伊泰的就用伊泰单号
                // 2017年6月22日9:33:58 zgw edit zjw 说伊泰的单就显示伊泰的单号
                $data[$key]['order_id'] = $yitai['bill_number'];
            }
            $data[$key]['dis_time'] = yitai_time($value['dis_time']).$dis_time;

            // 接单时间
            $data[$key]['do_time'] = yitai_time($value['do_time']).'(耗时：'.usetime(time() - strtotime($value['do_time'])).')';

            //司机名
            $driver=M('users')->where(array('id'=>$value['driver_id']))->find();
            $data[$key]['driver'] = $driver['real_name'] . '<br/>(' . $driver['phone'] . ')';

            // 买卖双方
            $data[$key]['buy_sell'] = $value['seller_name'] . '<br/>==><br/>' . $value['buyer_name'];

            // 人性化显示状态
            $state1 = $value['state'];
            $state = getBillType($value['state']);
            if ($rbill_state = M('driver_rbill')->where(array('bill_id' => $value['id']))->getField('state')) {
                $state .= '（退单：'.getReturnBillType($rbill_state).')';
            }
            $print_state = '';
            switch ($value['use_type']) {
                case 1:
                    $print_state .= '纸质单';
                    break;
                case 2:
                    $print_state .= '电子单';
                    break;
                default:
                    $print_state .= '<span style="color:red">未选择</span>';
                    break;
            }
            $print_state .= ',';
            $print_state .= $value['is_print']?'已打印':'未打印';
            $data[$key]['state'] = $state.'<br>('.$print_state.')';

            // 煤种
            $coal_type_id = M('orders')->where(array('order_id' => $value['order_id']))->getField('coal_type');
            $data[$key]['coal_type'] = coal_type($coal_type_id);

            // 车牌号带车小号
            if ($value['owner_order']) {
                $owner_order = '<br>('.$value['owner_order'].')';
            } else {
                $owner_order = '';
            }
            $data[$key]['lic_number'] = $data[$key]['lic_number'].$owner_order;

            // 开始吨数
            $data[$key]['begin_w'] = '车重：'.($value['begin_first_w']?$value['begin_first_w']:0);
            $data[$key]['begin_w'] .= ',毛重：'.($value['begin_second_w']?$value['begin_second_w']:0);
            $data[$key]['begin_w'] .= ',净重：'.($value['begin_second_w']-$value['begin_first_w']);
            // 结束吨数
            $data[$key]['end_w'] = '毛重：'.($value['end_first_w']?$value['end_first_w']:0);
            $data[$key]['end_w'] .= ',车重：'.($value['end_second_w']?$value['end_second_w']:0);
            $data[$key]['end_w'] .= ',净重：'.($value['end_second_w']-$value['end_first_w']);
            // 操作
            $dostr = '';
            // 查看地图
            $option = "{
                id:'Mapmap_realTime',
                url:'".U('/Vip/Map/map_realTime')."',
                data:{userid:'".$value['driver_id']."',bill_id:'".$value['id']."',limit:'5'},
                type:'get',
                height:'450',
                width:'600',
                fresh:true,
                onClose:'aaa'
            }";
            $dostr .= create_button($option, 'dialog', '查看地图');
            // 窜矿，要求状态接单以后
            if ($state1 <= 2) {
                // 窜矿处理
                $option1 = "{
                    id:'bill_confict_deal',
                    url:'".U('Vip/Bill/confictDeal')."',
                    data:{bill_id:'".$value['id']."'},
                    type:'get',
                    height:'450',
                    width:'600'
                }";
                $dostr .= create_button($option1, 'dialog', '窜矿处理');
            }
            // 物流公司退单，向SAP提出退单申请，rbill = 5,挂起状态
            if ($state1 == 2) {
                // 物流公司退单
                $rbill = M('driver_rbill')->where(array('bill_id' => $value['id']))->find();
                if ($rbill) {
                    $option4 = "{
                        url:'".U('Vip/Bill/delApplyForSap')."',
                        data:{id:'".$value['id']."'},
                        type:'get',
                        confirmMsg:'确定要操作吗？'
                    }";
                    $dostr .= create_button($option4, 'doajax', '取消sap申请');
                } else {
                    $option2 = "{
                        id:'bill_back',
                        url:'".U('Vip/Bill/backBill')."',
                        data:{bill_id:'".$value['id']."',driver_id:'".$value['driver_id']."',type:1},
                        type:'get',
                        height:'300',
                        width:'485'
                    }";
                    $dostr .= create_button($option2, 'dialog', '物流公司退单');
                }
            }
            // 更换司机，
            if ($state1 == 2) {
                // 更换司机
                $option2 = "{
                    id:'change_bill_driver',
                    url:'".U('Vip/Bill/changeDriverView')."',
                    data:{bill_id:'".$value['id']."',driver_id:'".$value['driver_id']."',truck_id:'".$value['truck_id']."',tabid:'Bill_changeDriver'},
                    type:'get',
                    height:'300',
                    width:'485'
                }";
                $dostr .= create_button($option2, 'dialog', '更换司机');
            }

            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        // dump($info);exit;
        echo json_encode($info);
    }
    // 窜矿处理
    public function confictDeal(){
        if (IS_POST) {
            $bill_id = I('bill_id');
            $bill = M('bill')->find($bill_id);
            if ($bill) {
                if ($bill['state'] > 2) {
                    alert_false('提煤单状态不对');
                }
                if ($bill['create_type'] != 1) {
                    alert_false('不是伊泰的派单');
                }
                $post = I('post.');
                //检测输入的买家卖家系统中是否已经存在
                $seller_id = A('Vip/Orders')->getOrderCompanyId($post['seller_id']);

                $bill2['seller']=$seller_id;

                $buyer_id = A('Vip/Orders')->getOrderCompanyId($post['buyer_id']);

                $bill2['buyer']=$buyer_id;

                //检测是否已经存在煤种,如果不存在则添加后返回ID,如果存在则直接使用ID

                $coal_type=M('coal_type')->where(array('name'=>I('coal_type')))->find();
                if($coal_type){
                    $data2['coal_type']=$coal_type['id'];
                } else {
                    $coal_type_id=M('coal_type')->add(array('name'=>I('coal_type')));
                    $data2['coal_type']=$coal_type_id;
                }
                M()->startTrans();
                // 1、作废之前的bill单
                $res1 = M('bill')->save(array('id' => $bill_id, 'state' => 9));
                // 2、增加大订单
                $data2 = array(
                    'order_id' => 'MWT'."YITAI".'T'.time().rand_num(),
                    'create_time' => get_time(),
                    'seller_id'   => $seller_id,
                    'buyer_id'       => $buyer_id,
                    'quantity'    => $bill['arrange_w'],
                );
                $res2 = M('orders')->add($data2);
                // 3、增加物流安排
                $logistics['order_id']=$data2['order_id'];
                $logistics['quantity']=$data2['quantity'];
                $logistics['res_quantity']=0;
                $logistics['assign_id']=0;
                $logistics['assigned_id']=session('company_id');
                $logistics['create_time']=get_time();
                $res3 = M('logistics')->add($logistics);
                // 4、增加提煤单
                $bill2['company']=$bill['company'];
                $bill2['trucker_id']=$bill['trucker_id'];
                $bill2['driver_id']=$bill['driver_id'];
                $bill2['logistics_id']=$res3;
                $bill2['arrange_w']=$bill['arrange_w'];
                $bill2['dis_time']=date("Y-m-d H:i:s");
                $bill2['do_time']=date("Y-m-d H:i:s");
                $bill2['state']=2;
                $bill2['create_type']=1;
                $bill2['order_id']=$data2['order_id'];
                $bill2['truck_id']=$bill['truck_id'];
                $bill2['anchored_id']=$bill['anchored_id'];
                $bill2['owner_type']=$bill['owner_type'];
                $bill2['anchored_id']=$bill['anchored_id'];
                $res4 = M('bill')->add($bill2);
                // 5、增加伊泰关联表
                $link_data = M('link_yitai')->where(array('bill_id' => $bill['id']))->find();
                $link_yitai['bill_id']=$res4;
                $link_yitai['bill_number']=$link_data['bill_number'];
                $link_yitai['code_str']=$link_data['code_str'];
                $link_yitai['coal_type']=$link_data['coal_type'];
                $link_yitai['dis_use_time']=$link_data['dis_use_time'];
                $link_yitai['dis_time'] =$link_data['dis_time'];
                $res5 = M('link_yitai')->where(array('bill_number' => $link_data['bill_number']))->save($link_yitai);
                // 6、更新车辆状态
                $res6 = M('truck')->where(array('id'=>$bill['truck_id']))->save(array('state'=>3));

                if ($res1 !== false && $res2 && $res3 && $res4 && $res5 !== false && $res6 !== false) {
                    M()->commit();

                    if ($bill['driver_id']) {
                        // 成功后app通知司机
                        D('Push', 'Logic')->noticeDriver('窜矿通知','您拉运的提煤单已作窜矿处理，请留意拉货地址',$bill['driver_id']);
                        // 成功后短信通知司机
                    }

                    after_alert(array('closeCurrent' => true));
                } else {
                    M()->rollback();
                    alert_false();
                }
            } else {
                alert_illegal();
            }
        }
        $bill_id = I('get.bill_id');
        $bill = M('bill')->find($bill_id);
        // 原采购方
        $buyer_name = M('company')->where(array('id' => $bill['buyer']))->getField('name');
        // $buyer_gps = getComapnyGps($bill['buyer']);
        // dump($buyer_gps);
        // 原销售方
        $seller_name = M('company')->where(array('id' => $bill['seller']))->getField('name');
        // $seller_gps = getComapnyGps($bill['seller']);
        // 判断是销售订单还是采购订单
        // $orders = M('orders')->where(array('order_id' => $bill['order_id']))->find();

        // if ($orders['log_id'] == $orders['buyer']) {
        //     $type_show = '采购订单';
        // } else {
        //     $type_show = '销售订单';
        // }
        $data = array(
            'bill' => $bill,
            'buyer_name' => $buyer_name,
            'seller_name' => $seller_name,
            // 'buyer_gps' => $buyer_gps,
            // 'seller_gps' => $seller_gps,
            // 'type_show' => $type_show,
            // 'type' => $type,
        );
        $this->assign($data);
        $this->display();
    }

    // 物流公司退单
    public function backBill(){
        $bill_id = I('bill_id');
        if (!$bill_id) {
            alert_false('参数错误1');
        }
        $type = I('type') + 0;
        if ($type == 1) {
            $driver_id = I('driver_id');
            if (!$driver_id) {
                alert_false('参数错误2');
            }
            if (IS_POST) {
                //司机只能在没有拉货的情况下可以退单
                //并且该提煤单必须是派给了当前司机
                $bill = M('bill')
                    ->where(array('id' => $bill_id, 'driver_id' => $driver_id))
                    ->where('state = 2')
                    ->find();
                if (!$bill) {
                    alert_false('提煤单状态异常，不能退单');
                }
                //查看之前是否已经有过提交
                $resault = M('driver_rbill')->where(array('bill_id' => $bill_id))->find();
                //如果存在, 不操作
                if($resault){
                    alert_false('已有退单记录');
                }else{
                    $data['return_time'] = date("Y-m-d H:i:s");
                    $data['bill_id']      = $bill_id;
                    $data['state']        = 5;
                    $data['reason']       = I('reason');
                    $res=M('driver_rbill')->add($data);
                    if ($res) {
                        // 推送给谁？？？
                        // D('Push','Logic')->noticeCompanyRebill(session('user_id'),$bill['company']);
                        // $tab = I('tabid');
                        $array = array('message' => '已经向SAP提交退单申请，请耐心等待返回结果', 'tabid' => 'Bill_takeBill,Bill_doingBill', 'closeCurrent' => true);
                        after_alert($array);
                    } else {
                        alert_false('操作失败');
                    }
                }
                exit;
            }
            $this->id = $bill_id;
            $this->driver_id = $driver_id;
            $this->type = $type;
            $this->display();
        } else if ($type == 2) {
            if (IS_POST) {
                // 未接单，没有司机，伊泰也能收回
                $bill = M('bill')
                    ->where(array('id' => $bill_id))
                    ->where('state = 1')
                    ->find();
                if (!$bill) {
                    alert_false('提煤单状态异常，不能退单');
                }

                //查看之前是否已经有过提交
                $resault = M('driver_rbill')->where(array('bill_id' => $bill_id))->find();
                //如果存在, 不操作
                if($resault){
                    alert_false('已有退单记录');
                }else{
                    $data['return_time'] = date("Y-m-d H:i:s");
                    $data['bill_id']      = $bill_id;
                    $data['state']        = 5;
                    $data['reason']       = I('reason');
                    $res=M('driver_rbill')->add($data);
                    if ($res) {
                        // 推送给谁？？？
                        // D('Push','Logic')->noticeCompanyRebill(session('user_id'),$bill['company']);
                        // $tab = I('tabid');
                        $array = array('message' => '已经向SAP提交退单申请，请耐心等待返回结果', 'tabid' => 'Bill_takeBill,Bill_doingBill', 'closeCurrent' => true);
                        after_alert($array);
                    } else {
                        alert_false('操作失败');
                    }
                }
                exit;
            }
            $this->id = $bill_id;
            $this->type = $type;
            $this->display();
        } else {
            alert_false('参数错误3');
        }
    }

    // 取消sap申请
    public function delApplyForSap(){
        $id = I('id'); // 订单id
        if ($id) {
            $info = M('driver_rbill')->where(array('bill_id' => $id))->find();
            if ($info) {
                $res = M('driver_rbill')->delete($info['id']);
                if ($res) {
                    after_alert(array('reload' => true));
                } else {
                    alert_false();
                }
            } else {
                alert_illegal();
            }
        } else {
            alert_illegal();
        }
    }

    // 更换司机页面
    public function changeDriverView(){
        $this->bill_id = I('bill_id');
        $this->driver_id = I('driver_id');
        $this->truck_id = I('truck_id');
        $drivers = M('driver d')
            ->field('d.uid, u.real_name as name')
            ->join('left join coal_users u on u.id = d.uid')
            ->where(array('d.uid' => array('not in', I('driver_id')), 'd.truck_id' => I('truck_id')))
            ->select();
        if ($drivers) {
            $this->drivers = $drivers;
            $this->display();
        } else {
            alert_false('该车没有另一个司机可接替');
        }
    }

    // 更换司机
    public function changeDriver(){
        $bill_id = I('bill_id');
        $driver_id = I('driver_id');
        $current_driver_id = I('current_driver_id');
        $remark = I('remark');
        if (!$bill_id || !$driver_id || !$current_driver_id || !$remark) {
            alert_false('参数错误');
        }
        // 1、不能是退单状态的
        $is_return = M('driver_rbill')->where(array('bill_id' => $bill_id, 'state' => array('in','1,5')))->find();
        if ($is_return) {
            alert_false('已经有未处理的退单记录');
        }
        // 2、只能是当前车辆的其他司机
        $truck_id = M('driver')->where(array('uid' => $driver_id))->getField('truck_id');
        $driver = M('driver')->where(array('truck_id' => $truck_id, 'uid' => $current_driver_id))->select();
        foreach ($driver as $key => $value){
            if(!D('Driver','Logic')->is_work($value['uid'])){
                unset($driver[$key]);
            }
        }
        if(!count($driver)){
            alert_false('车辆没有可用司机');
        }
        // 3、bill.state = 2
        $bill = M('bill')->find($bill_id);
        if ($bill['state'] != 2) {
            alert_false('提煤单状态不对');
        }
        M()->startTrans();
        // 1、变更bill
        $res = M('bill')->where(array('id' => $bill_id, 'driver_id' => $driver_id))->save(array('driver_id' => $current_driver_id));
            // 通知新司机
            //推送
            $users = M('users')->where(array('id' => $current_driver_id))->find();
            $phone = $users['phone'];
            $name = $users['real_name'];
            $message='您有一条提煤单需要处理,请尽快处理.';
            $title='[煤问题]新订单通知';
            $code=1100;//状态码
            D("Push",'Logic')->arrBill($phone,$message,$title,$code);
            //短信
            vendor('TopSdk.TopSdk');
            $mes=new \Message();
            $mes->informDriverBill($name,$phone);
        // 2、变更记录
        $data = array(
            'bill_id'              =>  $bill_id,
            'last_driver_id'           =>  $driver_id,
            'current_driver_id'  =>  $current_driver_id,
            'remark'               =>  $remark,
            'create_time'         =>  get_time(),
        );
        $res1 = M('bill_driver_change_log')->add($data);
        if ($res !== false && $res1) {
            M()->commit();
            after_alert(array('closeCurrent' => true, 'tabid' => 'Bill_doingBill'));
        } else {
            M()->rollback();
            alert_false();
        }
    }

    // 手动结算提煤单
    public function getManualSettlement(){
        // 2017年5月3日19:13:09
        $db = M('balance_bill bb');
        $where = array();
        $where['b.company'] = session('company_id');
        $where['bb.type'] = 2;
        $where['bb.state'] = 1;

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        // if ($name = trim(I('lic_number', ''))) {
        //     $where['lic_number'] = array('like', '%'.$name.'%');
        // }
        // if ($account = I('account', '')) {
        //     $where['a.account'] = array('like', $account.'%');
        // }

        $count = $db
            ->join('left join coal_bill b on b.id = bb.bill_id')
            // ->join('left join coal_driver dr on dr.id = b.driver_id')
            // ->join('left join coal_users u on u.id = dr.uid')
            // ->join('left join coal_users u1 on u1.id = b.trucker_id')
            // ->join('left join coal_company a on a.id = b.buyer')
            // ->join('left join coal_company c on c.id = b.seller')
            ->where($where)
            ->count();

        $data = $db
            ->field('bb.*, u.real_name as driver_name, u.phone as driver_phone, a.name as buyer_name, c.name as seller_name, b.arrange_w, b.dis_time, b.truck_id, b.order_id')
            ->join('left join coal_bill b on b.id = bb.bill_id')
            // ->join('left join coal_driver dr on dr.id = b.driver_id')
            ->join('left join coal_users u on u.id = b.driver_id') // 司机个人信息
            ->join('left join coal_users u1 on u1.id = b.trucker_id') // 车主个人信息
            ->join('left join coal_company a on a.id = b.buyer') // 买家信息
            ->join('left join coal_company c on c.id = b.seller') // 卖家信息
            // ->join('left join coal_truck t on t.id = b.truck_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            // ->order('last_time asc')
            ->select();
        // sql();
        foreach ($data as $key => $value) {
            // 拥有者
            $data[$key]['owner_name'] = get_trucker_name($value['truck_id']);
            // $data[$key]['order_id'] = $value['order_id'] . '<br/>(' . $value['dis_time'] . ')';
            //
            // // 派过单的司机名（以后优化）
            $data[$key]['driver'] = $value['driver_name'] . '<br/>(' . $value['driver_phone'] . ')';
            //
            // $data[$key]['buy_sell'] = $value['buyer_name'] . '<br/>==><br/>' . $value['seller_name'] . '';
            // // $data[$key]['coal_type_name'] = $value['coal_type_name'] . '<br/>' . $value['res_quantity'] . '吨';
            $option = "{
                id:'Balance_bill',
                url:'".U('Vip/Bill/balanceBill')."',
                data:{bill_id:'".$value['bill_id']."'},
                type:'get',
                fresh:true,
                height:'500',
                width:'800'
            }";

            $dostr = create_button($option, 'navtab', '结算');
            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        // dump($info);exit;
        echo json_encode($info);
    }
    // 显示过磅单页面,手动结算
    public function balanceBill(){
        if (IS_POST) {
//             dump($_POST);exit;
            $bill_id = I('post.bill_id');
            $balance_type = I('post.balance_type');
            $start_weight = I('post.start_weight');
            $start_time = I('post.start_time');
            $end_weight = I('post.end_weight');
            $end_time = I('post.end_time');
            $bill_info = M('bill')->find($bill_id);
            if ($bill_info['state'] == 5 || $bill_info['state'] == 2 || $bill_info['state'] == 3 || $bill_info['state'] == 4) {
                if ($balance_type == 2) {
                    $start_weight = $bill_info['begin_w'];
                    $start_time   = $bill_info['begin_second_time'];
                }
                if (!$bill_id || !$start_weight || !$start_time || !$end_weight || !$end_time) {
                    alert('提交的数据不完整', 300);
                }
                if($start_time > $end_time){
                    alert('结束时间必须大于开始时间', 300);
                }
                // 吨数不做限制，原因看项目需求表
                // 显示过磅单
                M()->startTrans();
                $res = D('Bill', 'Logic')->manualSettlement($bill_id, $start_weight, $start_time, $end_weight, $end_time);
            } else if ($bill_info['state'] == 6){
                M('balance_bill')->where(array('bill_id' => $bill_id, 'state' => 1))->save(array('state' => 2));
                $res = true;
            }else{
                $res = false;// 2017年6月28日22:53:49 zgw 没有明确知道到底其他状态是否允许走，或者以其他方式结束
            }
            if ($res) {
                is_truck_cron($bill_info['truck_id']);
                M()->commit();
                after_alert(array('closeCurrent' => true, 'tabid' => 'Bill_manualSettlement'));
            } else {
                M()->rollback();
                alert_false();
            }
        }
        $bill_id = I('get.bill_id');
        $bill_info = M('bill')->find($bill_id);
        $balance_info = M('balance_bill')->where(array('bill_id' => $bill_id))->select();
        $data = array('bill_id' => $bill_id);
        if ($balance_info) {
            // balance_type说明：1是供需双方都提交过磅单，2是只有买方提交，由物流公司审核。（如果是卖方提交，买家有设备的话，会在买家确认）
            if (count($balance_info) == 1) {
                // 只有一个过磅单
                foreach ($balance_info as $key => $value) {
                    if ($value['type'] == 1) {
                        // $data['seller_balance'] = $value;
                        // $data['buyer_balance'] = array(
                        //     'img' => 0,
                        //     'weight' => $bill_info['end_w'],
                        //     'add_time' => $bill_info['end_second_time']
                        // );
                    } else if ($value['type'] == 2) {
                        $data['buyer_balance'] = $value;
                        $data['seller_balance'] = array(
                            'img' => '',
                            'weight' => $bill_info['begin_w'],
                            'add_time' => $bill_info['begin_second_time']
                        );
                        $data['balance_type'] = 2;
                    }
                }
            } else {
                // 两个过磅单
                foreach ($balance_info as $key => $value) {
                    if ($value['type'] == 1) {
                        $data['seller_balance'] = $value;
                    } else if ($value['type'] == 2) {
                        $data['buyer_balance'] = $value;
                    }
                }
                $data['balance_type'] = 1;
            }
            $this->assign($data);
            $this->display();
        } else {
            alert_illegal();
        }
    }
    //已完成提煤单页面
    function finishedBill(){
        //查看是否是总公司
        $company=M('company')->where(array('id'=>session('company_id')))->find();
        //子公司列表
        $sub_company=M('company')->where(array('pid'=>session('company_id')))->field('id,name')->select();
        // 卖家列表
        $sellers = M('bill')->field('seller')->where(array('company' => session('company_id')))->group('seller')->select();
        foreach ($sellers as $key => $val) {
            $sellers[$key]['id'] = $val['seller'];
            $sellers[$key]['name'] = getCompanyName($val['seller']);
        }
        // 买家列表
        $buyers = M('bill')->field('buyer')->where(array('company' => session('company_id')))->group('buyer')->select();
        foreach ($buyers as $key => $val) {
            $buyers[$key]['id'] = $val['buyer'];
            $buyers[$key]['name'] = getCompanyName($val['buyer']);
        }
        $data = array(
            'is_zong'   =>  $company['pid'],
            'sub_company'   =>  $sub_company,
            'sellers'   =>  $sellers,
            'buyers'   =>  $buyers,
        );
        $this->assign($data);
        $this->display();
    }
    // 获取已完成提煤单
    public function getFinishedBill($type=1){
        $db = M('bill b');
        $where = array();
        //子公司列表
        $sub_company_str=session('company_id');
        $sub_company=M('company')->where(array('pid'=>session('company_id')))->field('id,name')->select();
        foreach ($sub_company as $value){
            $sub_company_str=$sub_company_str.','.$value['id'];
        }
        $where['b.state'] = 6;

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 100000);
        //筛选条件
        // 车牌号
        if ($lic_number = trim(I('lic_number', ''))) {
            $where['t.lic_number'] = array('like', '%'.$lic_number.'%');
        }
        // 车小号
        if ($xiaohao = trim(I('owner_order', ''))) {
            $where['t.owner_order'] = array('like', '%'.$xiaohao.'%');
        }
        // 司机
        if ($driver_name = trim(I('driver_name', ''))) {
            $where['u.real_name'] = array('like', '%'.$driver_name.'%');
        }
        // 时间筛选
        if (I('time_type')) {
            $start_time = I('start_time');
            if (!$start_time) {
                $start_time = date('2017-5-1');
            }
            $end_time = I('end_time');
            if (!$end_time) {
                $end_time = date('Y-m-d 23:59:59');
            }
            switch (I('time_type')) {
                case 1:
                    // 系统派单时间
                    $where['b.dis_time'] = array('between', array($start_time, $end_time));
                    break;
                case 2:
                    // 伊泰派单时间
                    $start_time = date('Y-m-d H:i:s', strtotime($start_time)-60*60*6);
                    $end_time = date('Y-m-d H:i:s', strtotime($end_time)-60*60*6);
                    $where1['dis_time'] = array('between', array($start_time, $end_time));
                    // dump($where1);
                    $yitai_data1 = M('link_yitai')->field('bill_id')->where($where1)->select();
                    foreach ($yitai_data1 as $val) {
                        $tmp[] = $val['bill_id'];
                    }
                    if ($tmp) {
                        $where['b.id'] = array('in', $tmp);
                    }
                    break;
                case 3:
                    // 系统接单时间
                    $where['b.do_time'] = array('between', array($start_time, $end_time));
                    break;
                case 4:
                    // 系统拉煤时间
                    $where['b.begin_second_time'] = array('between', array($start_time, $end_time));
                    break;
                case 5:
                    // 伊泰拉煤时间
                    $start_time = date('Y-m-d H:i:s', strtotime($start_time)-60*60*6);
                    $end_time = date('Y-m-d H:i:s', strtotime($end_time)-60*60*6);
                    $where1['begin_time'] = array('between', array($start_time, $end_time));
                    $yitai_data1 = M('link_yitai')->field('bill_id')->where($where1)->select();
                    foreach ($yitai_data1 as $val) {
                        $tmp[] = $val['bill_id'];
                    }
                    if ($tmp) {
                        $where['b.id'] = array('in', $tmp);
                    }
                    break;
                case 6:
                    // 系统卸货时间
                    $where['b.end_second_time'] = array('between', array($start_time, $end_time));
                    break;
                case 7:
                    // 伊泰卸货时间
                    $start_time = date('Y-m-d H:i:s', strtotime($start_time)-60*60*6);
                    $end_time = date('Y-m-d H:i:s', strtotime($end_time)-60*60*6);
                    $where1['end_time'] = array('between', array($start_time, $end_time));
                    $yitai_data1 = M('link_yitai')->field('bill_id')->where($where1)->select();
                    foreach ($yitai_data1 as $val) {
                        $tmp[] = $val['bill_id'];
                    }
                    if ($tmp) {
                        $where['b.id'] = array('in', $tmp);
                    }
                    break;
                default:
                    break;
            }
        }
        // 卖家，煤矿
        if ($seller = trim(I('seller', ''))) {
            $where['b.seller'] = $seller;
        }
        // 买家，集运站
        if ($buyer = trim(I('buyer', ''))) {
            $where['b.buyer'] = $buyer;
        }
        if ($search_sub_company = trim(I('sub_company', ''))) {
            $where['b.company'] = array('in',$search_sub_company);
        }else{
            $where['b.company'] = array('in',$sub_company_str);
        }
        $count = $db
            ->join('left join coal_driver dr on dr.id = b.driver_id')
            ->join('left join coal_users u on u.id = b.driver_id')
            ->join('left join coal_users u1 on u1.id = b.trucker_id')
            ->join('left join coal_company a on a.id = b.buyer')
            ->join('left join coal_company c on c.id = b.seller')
            ->join('left join coal_truck t on t.id = b.truck_id')
            ->where($where)
            ->count();

        $data = $db
            ->field('b.*, u.real_name as driver_name, u1.real_name as trucker_name, u.phone as driver_phone, a.name as buyer_name, c.name as seller_name, t.lic_number, t.owner_order')
            ->join('left join coal_driver dr on dr.id = b.driver_id')
            ->join('left join coal_users u on u.id = b.driver_id')
            ->join('left join coal_users u1 on u1.id = b.trucker_id')
            ->join('left join coal_company a on a.id = b.buyer')
            ->join('left join coal_company c on c.id = b.seller')
            ->join('left join coal_truck t on t.id = b.truck_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('b.dis_time desc')
            ->select();
        // $sql=M()->_sql();
        // sql();
        $end_w_count = 0;
        foreach ($data as $key => $value) {

            // 派单时间,如果有伊泰系统派单时间，派单用时
            $dis_time = '';
            $begin_time = '';
            $end_time = '';
            $yitai = M('link_yitai')->where(array('bill_id' => $value['id']))->find();
            // 2017年6月8日14:57:51 zgw 伊泰派单系统，bill表设计没有扩展性，如果有其他系统，就要循环所有对接的表。应该是bill表有一个外键（唯一），关联到一个表，然后对接所有系统。
            $sys_dis_time = date('m-d H:i:s', strtotime($value['dis_time']));
            if ($yitai) {
                $dis_time = '<br>伊泰时间：'.date('m-d H:i:s', strtotime($yitai['dis_time']) + 60*60*6).'<br>用时：'.$yitai['dis_use_time'];
                $begin_time = '<br>伊泰时间：'.date('m-d H:i:s', strtotime($yitai['begin_time']) + 60*60*6).'<br>用时：'.$yitai['begin_use_time'];
                $end_time = '<br>伊泰时间：'.date('m-d H:i:s', strtotime($yitai['end_time']) + 60*60*6).'<br>用时：'.$yitai['end_use_time'];
                $sys_dis_time = date('m-d H:i:s', strtotime($value['dis_time'])+ 60*60*6);

                // 提煤单号,用伊泰提煤单号
                $data[$key]['order_id'] = $yitai['bill_number'];
            }
            $data[$key]['dis_time'] = $sys_dis_time.$dis_time;

            // 接单时间
            if ($value['do_time']) {
                $data[$key]['do_time'] = $value['create_type'] == 1?yitai_time($value['do_time']):$value['do_time'];
            } else {
                $data[$key]['do_time'] = '<span style="color: red">没有提交</span>';
            }
            // 拉煤时间
            if ($value['begin_second_time']) {
                if ($value['create_type'] == 1) {
                    $data[$key]['begin_second_time'] = yitai_time($value['begin_second_time']).$begin_time;
                } else {
                    $data[$key]['begin_second_time'] = date('m-d H:i:s', strtotime($value['begin_second_time']));
                }
            } else {
                $data[$key]['begin_second_time'] = '<span style="color: red">没有提交</span>';
            }
            // 卸货时间
            if ($value['end_second_time']) {
                if ($value['create_type'] == 1) {
                    $data[$key]['end_second_time'] = yitai_time($value['end_second_time']).$end_time;
                } else {
                    $data[$key]['end_second_time'] = date('m-d H:i:s', strtotime($value['end_second_time']));
                }
            } else {
                $data[$key]['end_second_time'] = '<span style="color: red">没有提交</span>';
            }

            // 派过单的司机名（以后优化）
            // $data[$key]['driver'] = $value['driver_name'] . '<br/>(' . $value['driver_phone'] . ')';
            //司机名
            $driver=M('users')->where(array('id'=>$value['driver_id']))->find();
            $data[$key]['driver'] = $driver['real_name'] . '<br/>(' . $driver['phone'] . ')';

            //损耗吨数
            if ($value['begin_w']) {
                $lost = round($value['begin_w']-$value['end_w'],2);

                if (abs($lost) >= 0.6) {
                    $tmp = M('check_bill')->where(array('bill_id' => $value['id'], 'state' => 0))->find();
                    if ($tmp) {
                        $data[$key]['use_w'] = '<span style="color: red">'.$lost.'</span><br>'
                            .'核对中……'
                        ;
                    } else {
                        $option = "{
                        url:'".U('Vip/Bill/applyCheck')."',
                        data:{id:'".$value['id']."'},
                        type:'get',
                        confirmMsg:'确定要操作吗？确定后，请联系伊泰调度核对数据。'
                    }";
                        $data[$key]['use_w'] = '<span style="color: red">'.$lost.'</span><br>'
                            .create_button($option,'doajax','申请核对')
                        ;
                    }
                } else {
                    $data[$key]['use_w'] = $lost;
                }
            } else {
                $data[$key]['begin_w'] = '<span style="color: red">没有提交</span>';
                $data[$key]['use_w'] = '未知';
            }

            $data[$key]['buy_sell'] = $value['buyer_name'] . '<br/>==><br/>' . $value['seller_name'] . '';

            // 车牌号带车小号
            if ($value['owner_order']) {
                $owner_order = '<br>('.$value['owner_order'].')';
            } else {
                $owner_order = '';
            }
            $data[$key]['lic_number'] = $data[$key]['lic_number'].$owner_order;

            // 总重量
            $end_w_count += $value['end_w']+0;
        }

        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
            // 'count_weight'     => 10000,
        );
        // session('bill_finished_data'.session('company_id'),array('times' => $count, 'end_w_count' => $end_w_count));
        // dump($info);exit;
        if($type==1){
            echo json_encode($info);
        }else{

            $this->finishedBill_data=$data;
        }
    }
    // 获取已完成提煤单，用来下载
    public function getFinishedBill1(){
        $db = M('bill b');
        $where = array();
        //子公司列表
        $sub_company_str=session('company_id');
        $sub_company=M('company')->where(array('pid'=>session('company_id')))->field('id,name')->select();
        foreach ($sub_company as $value){
            $sub_company_str=$sub_company_str.','.$value['id'];
        }
        $where['b.state'] = 6;
        //筛选条件
        // 车牌号
        if ($lic_number = trim(I('lic_number', ''))) {
            $where['t.lic_number'] = array('like', '%'.$lic_number.'%');
        }
        // 车小号
        if ($xiaohao = trim(I('owner_order', ''))) {
            $where['t.owner_order'] = array('like', '%'.$xiaohao.'%');
        }
        // 司机
        if ($driver_name = trim(I('driver_name', ''))) {
            $where['u.real_name'] = array('like', '%'.$driver_name.'%');
        }
        // 时间筛选
        if (I('time_type')) {
            $start_time = I('start_time');
            if (!$start_time) {
                $start_time = date('2017-5-1');
            }
            $end_time = I('end_time');
            if (!$end_time) {
                $end_time = date('Y-m-d 23:59:59');
            }
            switch (I('time_type')) {
                case 1:
                    // 系统派单时间
                    $where['b.dis_time'] = array('between', array($start_time, $end_time));
                    break;
                case 2:
                    // 伊泰派单时间
                    $start_time = date('Y-m-d H:i:s', strtotime($start_time)-60*60*6);
                    $end_time = date('Y-m-d H:i:s', strtotime($end_time)-60*60*6);
                    $where1['dis_time'] = array('between', array($start_time, $end_time));
                    $yitai_data1 = M('link_yitai')->field('bill_id')->where($where1)->select();
                    foreach ($yitai_data1 as $val) {
                        $tmp[] = $val['bill_id'];
                    }
                    if ($tmp) {
                        $where['b.id'] = array('in', $tmp);
                    }
                    break;
                case 3:
                    // 系统接单时间
                    $where['b.do_time'] = array('between', array($start_time, $end_time));
                    break;
                case 4:
                    // 系统拉煤时间
                    $where['b.begin_second_time'] = array('between', array($start_time, $end_time));
                    break;
                case 5:
                    // 伊泰拉煤时间
                    $start_time = date('Y-m-d H:i:s', strtotime($start_time)-60*60*6);
                    $end_time = date('Y-m-d H:i:s', strtotime($end_time)-60*60*6);
                    $where1['begin_time'] = array('between', array($start_time, $end_time));
                    $yitai_data1 = M('link_yitai')->field('bill_id')->where($where1)->select();
                    foreach ($yitai_data1 as $val) {
                        $tmp[] = $val['bill_id'];
                    }
                    if ($tmp) {
                        $where['b.id'] = array('in', $tmp);
                    }
                    break;
                case 6:
                    // 系统卸货时间
                    $where['b.end_second_time'] = array('between', array($start_time, $end_time));
                    break;
                case 7:
                    // 伊泰卸货时间
                    $start_time = date('Y-m-d H:i:s', strtotime($start_time)-60*60*6);
                    $end_time = date('Y-m-d H:i:s', strtotime($end_time)-60*60*6);
                    $where1['end_time'] = array('between', array($start_time, $end_time));
                    $yitai_data1 = M('link_yitai')->field('bill_id')->where($where1)->select();
                    foreach ($yitai_data1 as $val) {
                        $tmp[] = $val['bill_id'];
                    }
                    if ($tmp) {
                        $where['b.id'] = array('in', $tmp);
                    }
                    break;
                default:
                    break;
            }
        }
        // 卖家，煤矿
        if ($seller = trim(I('seller', ''))) {
            $where['b.seller'] = $seller;
        }
        // 买家，集运站
        if ($buyer = trim(I('buyer', ''))) {
            $where['b.buyer'] = $buyer;
        }
        if ($search_sub_company = trim(I('sub_company', ''))) {
            $where['b.company'] = array('in',$search_sub_company);
        }else{
            $where['b.company'] = array('in',$sub_company_str);
        }
        $count = $db
            ->join('left join coal_driver dr on dr.id = b.driver_id')
            ->join('left join coal_users u on u.id = b.driver_id')
            ->join('left join coal_users u1 on u1.id = b.trucker_id')
            ->join('left join coal_company a on a.id = b.buyer')
            ->join('left join coal_company c on c.id = b.seller')
            ->join('left join coal_truck t on t.id = b.truck_id')
            ->where($where)
            ->count();

        $data = $db
            ->field('b.*, u.real_name as driver_name, u1.real_name as trucker_name, u.phone as driver_phone, a.name as buyer_name, c.name as seller_name, t.lic_number, t.owner_order')
            ->join('left join coal_driver dr on dr.id = b.driver_id')
            ->join('left join coal_users u on u.id = b.driver_id')
            ->join('left join coal_users u1 on u1.id = b.trucker_id')
            ->join('left join coal_company a on a.id = b.buyer')
            ->join('left join coal_company c on c.id = b.seller')
            ->join('left join coal_truck t on t.id = b.truck_id')
            ->where($where)
            ->order('b.dis_time desc')
            ->select();
        $end_w_count = 0;
        foreach ($data as $key => $value) {

            // 派单时间,如果有伊泰系统派单时间，派单用时
            $dis_time = '';
            $begin_time = '';
            $end_time = '';
            $yitai = M('link_yitai')->where(array('bill_id' => $value['id']))->find();
            // 2017年6月8日14:57:51 zgw 伊泰派单系统，bill表设计没有扩展性，如果有其他系统，就要循环所有对接的表。应该是bill表有一个外键（唯一），关联到一个表，然后对接所有系统。
            $sys_dis_time = date('m-d H:i:s', strtotime($value['dis_time']));
            if ($yitai) {
                $dis_time = $yitai['dis_use_time'];
                $begin_time = $yitai['begin_use_time'];
                $end_time = $yitai['end_use_time'];
                $sys_dis_time = date('m-d H:i:s', strtotime($value['dis_time'])+ 60*60*6);

                // 提煤单号,用伊泰提煤单号
                $data[$key]['order_id'] = $yitai['bill_number'];
            }
            $data[$key]['dis_time'] = $sys_dis_time.$dis_time;

            // 接单时间
            if ($value['do_time']) {
                $data[$key]['do_time'] = date('m-d H:i:s', strtotime($value['do_time']));
            } else {
                $data[$key]['do_time'] = '<span style="color: red">没有提交</span>';
            }
            // 拉煤时间
            if ($value['begin_second_time']) {
                $data[$key]['begin_second_time'] = date('m-d H:i:s', strtotime($value['begin_second_time'])).$begin_time;
            } else {
                $data[$key]['begin_second_time'] = '<span style="color: red">没有提交</span>';
            }
            // 卸货时间
            if ($value['end_second_time']) {
                $data[$key]['end_second_time'] = date('m-d H:i:s', strtotime($value['end_second_time'])).$end_time;
            } else {
                $data[$key]['end_second_time'] = '<span style="color: red">没有提交</span>';
            }

            // 派过单的司机名（以后优化）
            // $data[$key]['driver'] = $value['driver_name'] . '<br/>(' . $value['driver_phone'] . ')';
            //司机名
            $driver=M('users')->where(array('id'=>$value['driver_id']))->find();
            $data[$key]['driver'] = $driver['real_name'] . '<br/>(' . $driver['phone'] . ')';

            //损耗吨数
            if ($value['begin_w']) {
                $data[$key]['use_w']=round($value['begin_w']-$value['end_w'],2);
            } else {
                $data[$key]['begin_w'] = '<span style="color: red">没有提交</span>';
                $data[$key]['use_w'] = '未知';
            }

            $data[$key]['buy_sell'] = $value['buyer_name'] . '<br/>==><br/>' . $value['seller_name'] . '';

            // 车牌号带车小号
            if ($value['owner_order']) {
                $owner_order = '<br>('.$value['owner_order'].')';
            } else {
                $owner_order = '';
            }
            $data[$key]['lic_number'] = $data[$key]['lic_number'].$owner_order;

            // 总重量
            $end_w_count += $value['end_w']+0;
        }

        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
       $this->finishedBill_data=$data;
    }

    // 获取已完成的提煤单，用原始的页面。只是伊泰的。
    public function finishedBill2(){
        $db = M('bill b');
        $where = array();
        //子公司列表
        // $sub_company_str=session('company_id');
        // $sub_company=M('company')->where(array('pid'=>session('company_id')))->field('id,name')->select();
        // foreach ($sub_company as $value){
        //     $sub_company_str=$sub_company_str.','.$value['id'];
        // }
        $where['b.state'] = 6;
        $where['b.create_type'] = 1;

        // //筛选条件
        // // 车牌号
        // if ($lic_number = trim(I('lic_number', ''))) {
        //     $where['t.lic_number'] = array('like', '%'.$lic_number.'%');
        // }
        // // 车小号
        // if ($xiaohao = trim(I('owner_order', ''))) {
        //     $where['t.owner_order'] = array('like', '%'.$xiaohao.'%');
        // }
        // // 司机
        // if ($driver_name = trim(I('driver_name', ''))) {
        //     $where['u.real_name'] = array('like', '%'.$driver_name.'%');
        // }
        // // 时间筛选
        // // 时间规则，默认是当天的。如果可以是某天的。可以是某几天的。
        // if (I('time_type')) {
        //     $start_time = I('start_time');
        //     if (!$start_time) {
        //         $start_time = date('2017-5-1');
        //     }
        //     $end_time = I('end_time');
        //     if (!$end_time) {
        //         $end_time = date('Y-m-d 23:59:59');
        //     }
        //     switch (I('time_type')) {
        //         case 1:
        //             // 系统派单时间
        //             $where['b.dis_time'] = array('between', array($start_time, $end_time));
        //             break;
        //         case 2:
        //             // 伊泰派单时间
        //             $start_time = date('Y-m-d H:i:s', strtotime($start_time)-60*60*6);
        //             $end_time = date('Y-m-d H:i:s', strtotime($end_time)-60*60*6);
        //             $where1['dis_time'] = array('between', array($start_time, $end_time));
        //             $yitai_data1 = M('link_yitai')->field('bill_id')->where($where1)->select();
        //             foreach ($yitai_data1 as $val) {
        //                 $tmp[] = $val['bill_id'];
        //             }
        //             if ($tmp) {
        //                 $where['b.id'] = array('in', $tmp);
        //             }
        //             break;
        //         case 3:
        //             // 系统接单时间
        //             $where['b.do_time'] = array('between', array($start_time, $end_time));
        //             break;
        //         case 4:
        //             // 系统拉煤时间
        //             $where['b.begin_second_time'] = array('between', array($start_time, $end_time));
        //             break;
        //         case 5:
        //             // 伊泰拉煤时间
        //             $start_time = date('Y-m-d H:i:s', strtotime($start_time)-60*60*6);
        //             $end_time = date('Y-m-d H:i:s', strtotime($end_time)-60*60*6);
        //             $where1['begin_time'] = array('between', array($start_time, $end_time));
        //             $yitai_data1 = M('link_yitai')->field('bill_id')->where($where1)->select();
        //             foreach ($yitai_data1 as $val) {
        //                 $tmp[] = $val['bill_id'];
        //             }
        //             if ($tmp) {
        //                 $where['b.id'] = array('in', $tmp);
        //             }
        //             break;
        //         case 6:
        //             // 系统卸货时间
        //             $where['b.end_second_time'] = array('between', array($start_time, $end_time));
        //             break;
        //         case 7:
        //             // 伊泰卸货时间
        //             $start_time = date('Y-m-d H:i:s', strtotime($start_time)-60*60*6);
        //             $end_time = date('Y-m-d H:i:s', strtotime($end_time)-60*60*6);
        //             $where1['end_time'] = array('between', array($start_time, $end_time));
        //             $yitai_data1 = M('link_yitai')->field('bill_id')->where($where1)->select();
        //             foreach ($yitai_data1 as $val) {
        //                 $tmp[] = $val['bill_id'];
        //             }
        //             if ($tmp) {
        //                 $where['b.id'] = array('in', $tmp);
        //             }
        //             break;
        //         default:
        //             break;
        //     }
        // }
        // // 卖家，煤矿
        // if ($seller = trim(I('seller', ''))) {
        //     $where['b.seller'] = $seller;
        // }
        // // 买家，集运站
        // if ($buyer = trim(I('buyer', ''))) {
        //     $where['b.buyer'] = $buyer;
        // }
        // if ($search_sub_company = trim(I('sub_company', ''))) {
        //     $where['b.company'] = array('in',$search_sub_company);
        // }else{
        //     $where['b.company'] = array('in',$sub_company_str);
        // }
        $count = $db
            ->join('left join coal_driver dr on dr.id = b.driver_id')
            ->join('left join coal_users u on u.id = b.driver_id')
            ->join('left join coal_users u1 on u1.id = b.trucker_id')
            ->join('left join coal_company a on a.id = b.buyer')
            ->join('left join coal_company c on c.id = b.seller')
            ->join('left join coal_truck t on t.id = b.truck_id')
            ->join('left join coal_link_yitai y on y.bill_id = b.id')
            ->where($where)
            ->count();

        $Page       = new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
        //分页跳转的时候保证查询条件

        $Page->setConfig('header','共 %TOTAL_ROW% 条记录');
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','第一页');
        $Page->setConfig('last','最后一页');
        $Page->rollPage=5;
        $Page->lastSuffix=false;
        $Page->setConfig('theme',"%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%");
        $show       = $Page->show();// 分页显示输出
        $data = $db
            ->field('b.id, b.do_time, b.end_w, b.begin_w, b.arrange_w,
            u.real_name as driver_name, u1.real_name as trucker_name, u.phone as driver_phone, 
            a.name as buyer_name, c.name as seller_name, 
            t.lic_number, t.owner_order,
            y.bill_number,y.dis_time')
            ->join('left join coal_driver dr on dr.id = b.driver_id')
            ->join('left join coal_users u on u.id = b.driver_id')
            ->join('left join coal_users u1 on u1.id = b.trucker_id')
            ->join('left join coal_company a on a.id = b.buyer')
            ->join('left join coal_company c on c.id = b.seller')
            ->join('left join coal_truck t on t.id = b.truck_id')
            ->join('left join coal_link_yitai y on y.bill_id = b.id')
            ->where($where)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->order('y.end_time desc')
            ->select();
        // $sql=M()->_sql();
        // sql();
        // dump($data);
        $end_w_count = 0;

        foreach ($data as $key => $value) {

            // 派单时间,如果有伊泰系统派单时间，派单用时
            $dis_time = '';
            $begin_time = '';
            $end_time = '';
            // $yitai = M('link_yitai')->where(array('bill_id' => $value['id']))->find();
            // 2017年6月8日14:57:51 zgw 伊泰派单系统，bill表设计没有扩展性，如果有其他系统，就要循环所有对接的表。应该是bill表有一个外键（唯一），关联到一个表，然后对接所有系统。
            // $sys_dis_time = date('m-d H:i:s', strtotime($value['dis_time']));
            // if ($yitai) {
            //     $dis_time = '<br>伊泰时间：'.date('m-d H:i:s', strtotime($yitai['dis_time']) + 60*60*6).'<br>用时：'.$yitai['dis_use_time'];
            //     $begin_time = '<br>伊泰时间：'.date('m-d H:i:s', strtotime($yitai['begin_time']) + 60*60*6).'<br>用时：'.$yitai['begin_use_time'];
            //     $end_time = '<br>伊泰时间：'.date('m-d H:i:s', strtotime($yitai['end_time']) + 60*60*6).'<br>用时：'.$yitai['end_use_time'];
            //     $sys_dis_time = date('m-d H:i:s', strtotime($value['dis_time'])+ 60*60*6);
            //
            //     // 提煤单号,用伊泰提煤单号
            //     $data[$key]['order_id'] = $yitai['bill_number'];
            // }
            // $data[$key]['dis_time'] = $sys_dis_time.$dis_time;

            // 接单时间
            if ($value['do_time']) {
                $data[$key]['do_time'] = date('m-d H:i:s', strtotime($value['do_time']));
            } else {
                $data[$key]['do_time'] = '<span style="color: red">没有提交</span>';
            }
            // 拉煤时间
            if ($value['begin_second_time']) {
                $data[$key]['begin_second_time'] = date('m-d H:i:s', strtotime($value['begin_second_time'])).$begin_time;
            } else {
                $data[$key]['begin_second_time'] = '<span style="color: red">没有提交</span>';
            }
            // 卸货时间
            if ($value['end_second_time']) {
                $data[$key]['end_second_time'] = date('m-d H:i:s', strtotime($value['end_second_time'])).$end_time;
            } else {
                $data[$key]['end_second_time'] = '<span style="color: red">没有提交</span>';
            }

            // 派过单的司机名（以后优化）
            // $data[$key]['driver'] = $value['driver_name'] . '<br/>(' . $value['driver_phone'] . ')';
            //司机名
            // $driver=M('users')->where(array('id'=>$value['driver_id']))->find();
            $data[$key]['driver'] = $value['driver_name'] . '<br/>(' . $value['driver_phone'] . ')';

            //损耗吨数
            if ($value['begin_w']) {
                $data[$key]['use_w']=round($value['begin_w']-$value['end_w'],2).'吨';
            } else {
                $data[$key]['begin_w'] = '<span style="color: red">没有提交</span>';
                $data[$key]['use_w'] = '未知';
            }

            $data[$key]['buy_sell'] = $value['buyer_name'] . '<br/>==><br/>' . $value['seller_name'] . '';

            // 车牌号带车小号
            if ($value['owner_order']) {
                $owner_order = '<br>('.$value['owner_order'].')';
            } else {
                $owner_order = '';
            }
            $data[$key]['lic_number'] = $data[$key]['lic_number'].$owner_order;

            // 总重量
            $end_w_count += $value['end_w']+0;
        }
        $data1 = array(
            'count' => $count,
            'show' => $show,
            'end_w_count' => $end_w_count,
            'data' => $data,
        );
        $this->assign($data1);
        $this->display();
    }

    // 派单历史查询
    public function billHistory(){
        //查看是否是总公司
        $company=M('company')->where(array('id'=>session('company_id')))->find();
        //子公司列表
        $sub_company=M('company')->where(array('pid'=>session('company_id')))->field('id,name')->select();
        // 卖家列表
        $sellers = M('bill')->field('seller')->where(array('company' => session('company_id')))->group('seller')->select();
        foreach ($sellers as $key => $val) {
            $sellers[$key]['id'] = $val['seller'];
            $sellers[$key]['name'] = getCompanyName($val['seller']);
        }
        // // 买家列表
        // $sellers = M('bill')->field('company')->where(array('buyer' => session('company_id')))->group('seller')->select();
        // foreach ($sellers as $key => $val) {
        //     $sellers[$key]['id'] = $val['company'];
        //     $sellers[$key]['name'] = getCompanyName($val['company']);
        // }
        $data = array(
            'is_zong'   =>  $company['pid'],
            'sub_company'   =>  $sub_company,
            'sellers'   =>  $sellers,
            // 'buyers'   =>  'ppp',
        );
        $this->assign($data);
        $this->display();
    }

    // 获得派单历史查询数据
    public function getBillHistory(){
        $db = M('arr_history b');
        $where = array();
        //子公司列表
        $sub_company_str = session('company_id');
        $sub_company = M('company')->where(array('pid' => $sub_company_str))->field('id,name')->select();
        foreach ($sub_company as $value){
            $sub_company_str=$sub_company_str.','.$value['id'];
        }
        if ($search_sub_company = trim(I('sub_company', ''))) {
            $where['b.company_id'] = array('in',$search_sub_company);
        }else{
            $where['b.company_id'] = array('in',$sub_company_str);
        }

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);
        //筛选条件
        // 车牌号
        if ($lic_number = trim(I('lic_number', ''))) {
            $where['b.first_truck'] = array('like', '%'.$lic_number.'%');
        }
        // 车小号
        if ($xiaohao = trim(I('owner_order', ''))) {
            $where['t.owner_order'] = array('like', '%'.$xiaohao.'%');
        }
        // 司机
        // if ($driver_name = trim(I('driver_name', ''))) {
        //     $where['u.real_name'] = array('like', '%'.$driver_name.'%');
        // }
        // 时间筛选
        if (I('time_type')) {
            if (I('start_time') && I('end_time') && strtotime(I('end_time')) > strtotime(I('start_time'))) {
                $start_time = I('start_time');
                $end_time = I('end_time');
            } else {
                $start_time = '2017-05-01';
                $end_time = date('Y-m-d 23:59:59');
            }

            switch (I('time_type')) {
                case 1:
                    // 系统派单时间
                    $where['b.arr_time'] = array('between', array($start_time, $end_time));
                    break;
                // case 2:
                //     // 伊泰派单时间
                //     $start_time = date('Y-m-d H:i:s', strtotime($start_time)-60*60*6);
                //     $end_time = date('Y-m-d H:i:s', strtotime($end_time)-60*60*6);
                //     $where1['dis_time'] = array('between', array($start_time, $end_time));
                //     $yitai_data1 = M('link_yitai')->field('bill_id')->where($where1)->select();
                //     foreach ($yitai_data1 as $val) {
                //         $tmp[] = $val['bill_id'];
                //     }
                //     $where['b.id'] = array('in', $tmp);
                //     break;
                // case 3:
                //     // 系统接单时间
                //     $where['b.do_time'] = array('between', array($start_time, $end_time));
                //     break;
                // case 4:
                //     // 系统拉煤时间
                //     $where['b.begin_second_time'] = array('between', array($start_time, $end_time));
                //     break;
                // case 5:
                //     // 伊泰拉煤时间
                //     $start_time = date('Y-m-d H:i:s', strtotime($start_time)-60*60*6);
                //     $end_time = date('Y-m-d H:i:s', strtotime($end_time)-60*60*6);
                //     $where1['begin_time'] = array('between', array($start_time, $end_time));
                //     $yitai_data1 = M('link_yitai')->field('bill_id')->where($where1)->select();
                //     foreach ($yitai_data1 as $val) {
                //         $tmp[] = $val['bill_id'];
                //     }
                //     $where['b.id'] = array('in', $tmp);
                //     break;
                // case 6:
                //     // 系统卸货时间
                //     $where['b.end_second_time'] = array('between', array($start_time, $end_time));
                //     break;
                // case 7:
                //     // 伊泰卸货时间
                //     $start_time = date('Y-m-d H:i:s', strtotime($start_time)-60*60*6);
                //     $end_time = date('Y-m-d H:i:s', strtotime($end_time)-60*60*6);
                //     $where1['end_time'] = array('between', array($start_time, $end_time));
                //     $yitai_data1 = M('link_yitai')->field('bill_id')->where($where1)->select();
                //     foreach ($yitai_data1 as $val) {
                //         $tmp[] = $val['bill_id'];
                //     }
                //     $where['b.id'] = array('in', $tmp);
                //     break;
                default:
                    break;
            }
        }
        // 卖家，煤矿
        // if ($seller = trim(I('seller', ''))) {
        //     $where['b.seller'] = $seller;
        // }

        $count = $db
            // ->join('left join coal_driver dr on dr.id = b.driver_id')
            // ->join('left join coal_users u on u.id = b.driver_id')
            // ->join('left join coal_users u1 on u1.id = b.trucker_id')
            // ->join('left join coal_company a on a.id = b.buyer')
            // ->join('left join coal_company c on c.id = b.seller')
            ->join('left join coal_truck t on t.id = b.truck_id')
            ->where($where)
            ->count();

        $data = $db
            // ->field('b.*, u.real_name as driver_name, u1.real_name as trucker_name, u.phone as driver_phone, a.name as buyer_name, c.name as seller_name, t.lic_number, t.owner_order')
            // ->join('left join coal_driver dr on dr.id = b.driver_id')
            // ->join('left join coal_users u on u.id = b.driver_id')
            // ->join('left join coal_users u1 on u1.id = b.trucker_id')
            // ->join('left join coal_company a on a.id = b.buyer')
            // ->join('left join coal_company c on c.id = b.seller')
            ->join('left join coal_truck t on t.id = b.truck_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('b.id desc')
            ->select();
        // sql();
        $end_w_count = 0;
        foreach ($data as $key => $value) {

            // // 派单时间,如果有伊泰系统派单时间，派单用时
            // $dis_time = '';
            // $begin_time = '';
            // $end_time = '';
            // $yitai = M('link_yitai')->where(array('bill_id' => $value['id']))->find();
            // // 2017年6月8日14:57:51 zgw 伊泰派单系统，bill表设计没有扩展性，如果有其他系统，就要循环所有对接的表。应该是bill表有一个外键（唯一），关联到一个表，然后对接所有系统。
            // if ($yitai) {
            //     $dis_time = '<br>伊泰时间：'.date('m-d H:i:s', strtotime($yitai['dis_time']) + 60*60*6).'<br>用时：'.$yitai['dis_use_time'];
            //     $begin_time = '<br>伊泰时间：'.date('m-d H:i:s', strtotime($yitai['begin_time']) + 60*60*6).'<br>用时：'.$yitai['begin_use_time'];
            //     $end_time = '<br>伊泰时间：'.date('m-d H:i:s', strtotime($yitai['end_time']) + 60*60*6).'<br>用时：'.$yitai['end_use_time'];
            // }
            // $data[$key]['dis_time'] = date('m-d H:i:s', strtotime($value['dis_time'])).$dis_time;
            //
            // // 提煤单号
            // $data[$key]['id'] = $value['id'].'<br>'.$yitai['bill_number'];
            //
            // // 接单时间
            // if ($value['do_time']) {
            //     $data[$key]['do_time'] = date('m-d H:i:s', strtotime($value['do_time']));
            // } else {
            //     $data[$key]['do_time'] = '<span style="color: red">没有提交</span>';
            // }
            // // 拉煤时间
            // if ($value['begin_second_time']) {
            //     $data[$key]['begin_second_time'] = date('m-d H:i:s', strtotime($value['begin_second_time'])).$begin_time;
            // } else {
            //     $data[$key]['begin_second_time'] = '<span style="color: red">没有提交</span>';
            // }
            // // 卸货时间
            // if ($value['end_second_time']) {
            //     $data[$key]['end_second_time'] = date('m-d H:i:s', strtotime($value['end_second_time'])).$end_time;
            // } else {
            //     $data[$key]['end_second_time'] = '<span style="color: red">没有提交</span>';
            // }
            //
            // // 派过单的司机名（以后优化）
            // // $data[$key]['driver'] = $value['driver_name'] . '<br/>(' . $value['driver_phone'] . ')';
            // //司机名
            // $driver=M('users')->where(array('id'=>$value['driver_id']))->find();
            // $data[$key]['driver'] = $driver['real_name'] . '<br/>(' . $driver['phone'] . ')';
            //
            // //损耗吨数
            // if ($value['begin_w']) {
            //     $data[$key]['use_w']=round($value['begin_w']-$value['end_w'],2);
            // } else {
            //     $data[$key]['begin_w'] = '<span style="color: red">没有提交</span>';
            //     $data[$key]['use_w'] = '未知';
            // }
            //
            // $data[$key]['buy_sell'] = $value['buyer_name'] . '<br/>==><br/>' . $value['seller_name'] . '';
            //
            // 车牌号带车小号
            $tmp = M('truck')->where(array('lic_number' => $value['first_truck']))->getField('owner_order');
            if ($tmp) {
                $owner_order = '('.$tmp.')';
            } else {
                $owner_order = '';
            }
            $data[$key]['first_truck'] = $data[$key]['first_truck'].$owner_order;
            // 车牌号带车小号
            $tmp = M('truck')->where(array('lic_number' => $value['lic_number']))->getField('owner_order');
            if ($tmp) {
                $owner_order = '('.$tmp.')';
            } else {
                $owner_order = '';
            }

            if ($value['first_truck'] != $value['lic_number']) {
                $data[$key]['lic_number'] = '<span style="color:red">'.$data[$key]['lic_number'].$owner_order.'</span>';
            } else {
                $data[$key]['lic_number'] = $data[$key]['lic_number'].$owner_order;
            }
            //
            // // 总重量
            // $end_w_count += $value['end_w']+0;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        // session('bill_finished_data'.session('company_id'),array('times' => $count, 'end_w_count' => $end_w_count));
        // dump($info);exit;
        echo json_encode($info);
    }

    // 退单记录
    public function getReturnHistory(){
        $db = M('driver_rbill drb');
        $where = array();
        $where['b.company'] = session('company_id');
        $where['drb.state'] = array('neq', 1);

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        if ($name = trim(I('lic_number', ''))) {
            $where['t.lic_number'] = array('like', '%'.$name.'%');
        }
        // if ($account = I('account', '')) {
        //     $where['a.account'] = array('like', $account.'%');
        // }
        // dump($_POST); // 不能筛选
        // dump($where);
        $count = $db
            ->join('left join coal_bill b on b.id = drb.bill_id')
            ->join('left join coal_users u on u.id = b.driver_id') // 司机个人信息
            ->join('left join coal_company a on a.id = b.buyer') // 买家信息
            ->join('left join coal_company c on c.id = b.seller') // 卖家信息
            ->join('left join coal_truck t on t.id = b.truck_id')
            ->where($where)
            ->count();

        $data = $db
            ->field('drb.*, u.real_name as driver_name, u.phone as driver_phone, a.name as buyer_name, 
                c.name as seller_name, b.arrange_w, b.do_time, b.order_id,t.lic_number,t.owner_order, b.buyer, b.seller, b.truck_id,b.driver_id')
            ->join('left join coal_bill b on b.id = drb.bill_id')
            ->join('left join coal_users u on u.id = b.driver_id') // 司机个人信息
            ->join('left join coal_company a on a.id = b.buyer') // 买家信息
            ->join('left join coal_company c on c.id = b.seller') // 卖家信息
            ->join('left join coal_truck t on t.id = b.truck_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('drb.return_time desc, drb.id desc')
            ->select();
        // sql();
        foreach ($data as $key => $value) {
            // 状态显示
            $state_arr = array('等待公司确定',"<span stype='color:green'> 确认退单</span>",'拒绝退单','系统自动处理'); // 状态数组
            $data[$key]['state'] =  $state_arr[$value['state']-1]; // 状态显示
            // 买家名字和手机号
            $data[$key]['buyer_name'] =  $value['buyer_name'] . '<br/>(' . get_company_phone($value['buyer']) . ')';
            // 卖家名字和手机号
            $data[$key]['seller_name'] =  $value['seller_name'] . '<br/>(' . get_company_phone($value['seller']) . ')';
            // 车主名字和手机号
            $tmp = get_trucker_name_and_phone($value['truck_id']);
            $data[$key]['trucker_name'] =  $tmp['trucker_name'] . '<br/>(' . $tmp['phone'] . ')';
            // 派过单的司机名和电话（以后优化）
            $data[$key]['driver'] = $value['driver_name'] . '<br/>(' . $value['driver_phone'] . ')';
            // 司机确认接单时间
            $data[$key]['do_time'] = getday($value['do_time']);
            // 车牌号带车小号
            if ($value['owner_order']) {
                $owner_order = '<br>('.$value['owner_order'].')';
            } else {
                $owner_order = '';
            }
            $data[$key]['lic_number'] = $data[$key]['lic_number'].$owner_order;

            $yitai = M('link_yitai')->where(array('bill_id' => $value['bill_id']))->find();
            // 2017年6月8日14:57:51 zgw 伊泰派单系统，bill表设计没有扩展性，如果有其他系统，就要循环所有对接的表。
            //应该是bill表有一个外键（唯一），关联到一个表，然后对接所有系统。
            if ($yitai) {
                $dis_time = '<br>伊泰派单时间：'.$yitai['dis_time'].'<br>派单用时：'.$yitai['dis_use_time'];

                // 提煤单号,是伊泰的就用伊泰单号
                // 2017年6月22日9:33:58 zgw edit zjw 说伊泰的单就显示伊泰的单号
                $data[$key]['order_id'] = $yitai['bill_number'];
            }
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        $this->history_data=$data;
        // dump($info);exit;
        echo json_encode($info);
    }
    //导出提煤单
    function down_load_bill(){
        $this->getFinishedBill1();
        $data=array();
        foreach ($this->finishedBill_data as $key=>$value){
            //订单号
            $data[$key]['order_id']=$value['order_id'];
            //买方
            $data[$key]['buyer']=M('company')->where(array('id'=>$value['buyer']))->getField('name');
            //卖方
            $data[$key]['seller']=M('company')->where(array('id'=>$value['seller']))->getField('name');
            //物流公司
            $data[$key]['company']=M('company')->where(array('id'=>$value['company']))->getField('name');
            //车主
            $data[$key]['trucker']=$value['trucker_name'];
            //司机
            $data[$key]['diver']=$value['driver_name'];
            //派单时间
            $data[$key]['dis_time']=$value['dis_time'];
            //接单时间
            $data[$key]['do_time']=$value['do_time'];
            //进矿时间
            $data[$key]['begin_first_time']=$value['begin_first_time'];
            //出矿时间
            $data[$key]['begin_second_time']=$value['begin_second_time'];
            //送货时间
            $data[$key]['end_first_time']=$value['end_first_time'];
            //完成时间
            $data[$key]['end_second_time']=$value['end_second_time'];
            //指派吨数
            $data[$key]['dis_w']=$value['dis_w'];
            //拉货吨数
            $data[$key]['begin_w']=$value['begin_w'];
            //送货吨数
            $data[$key]['end_w']=$value['end_w'];
            //损耗吨数
            $data[$key]['sunhao_w']=$value['end_w']-$value['begin_w']>0?$value['end_w']-$value['begin_w']:0;
        }
        $title=array('订单号','买方','卖方','物流公司','车主','司机','派单时间','接单时间','接单时间','进矿时间','出矿时间','送货时间','完成时间','指派吨数','拉货吨数','损耗吨数');
        downExcel($data,$title,'提煤单统计');
    }

    //导出提煤单
    function test(){
        $data1 = M('bill')->where(array('company' => 302))->order('dis_time desc')->select();
        $data=array();
        foreach ($data1 as $key=>$value){

            $data[$key]['lic_number']= M('truck')->where(array('id'=>$value['truck_id']))->getField('lic_number');
            $data[$key]['owner_order']= M('truck')->where(array('id'=>$value['truck_id']))->getField('owner_order');
            $data[$key]['bill_number']= M('link_yitai')->where(array('bill_id'=>$value['id']))->getField('bill_number');

            // 上次数据
            $tmp = M('bill')->where(array('truck_id'=>$value['truck_id'], 'state' => 6))->order('dis_time desc')->find();
            if ($tmp) {
                $data[$key]['last_bill_number']= M('link_yitai')->where(array('bill_id'=>$tmp['id']))->getField('bill_number');
                $data[$key]['last_end_time']=$tmp['end_second_time'];
            } else {
                $data[$key]['last_bill_number'] = '没有上次数据';
                $data[$key]['last_end_time'] = '没有上次数据';
            }

            $data[$key]['dis_time']= $value['dis_time'];
            $data[$key]['do_time']= $value['do_time'];


            // //订单号
            // $data[$key]['order_id']= strstr($value['order_id'],"<br/>",true);
            // //买方
            // $data[$key]['buyer']=M('company')->where(array('id'=>$value['buyer']))->getField('name');
            // //卖方
            // $data[$key]['seller']=M('company')->where(array('id'=>$value['seller']))->getField('name');
            // //物流公司
            // $data[$key]['company']=M('company')->where(array('id'=>$value['company']))->getField('name');
            // //车主
            // $data[$key]['trucker']=$value['trucker_name'];
            // //司机
            // $data[$key]['diver']=$value['driver_name'];
            // //派单时间
            // $data[$key]['dis_time']=$value['dis_time'];
            // //接单时间
            // $data[$key]['do_time']=$value['do_time'];
            // //进矿时间
            // $data[$key]['begin_first_time']=$value['begin_first_time'];
            // //出矿时间
            // $data[$key]['begin_second_time']=$value['begin_second_time'];
            // //送货时间
            // $data[$key]['end_first_time']=$value['end_first_time'];
            // //完成时间
            // $data[$key]['end_second_time']=$value['end_second_time'];
            // //指派吨数
            // $data[$key]['dis_w']=$value['dis_w'];
            // //拉货吨数
            // $data[$key]['begin_w']=$value['begin_w'];
            // //送货吨数
            // $data[$key]['end_w']=$value['end_w'];
            // //损耗吨数
            // $data[$key]['sunhao_w']=$value['end_w']-$value['begin_w']>0?$value['end_w']-$value['begin_w']:0;
        }
        $title=array('车牌号','车小号','本次单号','上次单号','上次结束时间','本次派单时间','接单时间');
        downExcel($data,$title,'提煤单历史统计');
        // session('last_truck','蒙KA1281');
        // session('last_truck_'.session('company_id'),'蒙KA1281');
    }

    // 申请核对伊泰数据
    public function applyCheck(){
        $bill_id = I('id') + 0;
        $bill_info = M('bill')->where(array('id' => $bill_id))->find();
        $yitai = M('link_yitai')->where(['bill_id' => $bill_id])->find();
        if ($bill_info['create_type'] != 1 || !$yitai) {
            alert_false('不是伊泰的提煤单');
        }
        if ($bill_info['state'] < 6) {
            alert_false('该单还没有结束');
        }
        $data = array(
            'company_id'   => session('company_id'),
            'bill_id'      => $bill_id,
            'bill_number'  => $yitai['bill_number'],
            'begin_w'      => $bill_info['begin_w'],
            'end_w'        => $bill_info['end_w'],
            'money'        => $yitai['money'],
            // 'remark' => I('remark'),
            'create_time' => get_time(),
        );
        $where = array(
            'bill_id' => $bill_id,
            'bill_number' => $yitai['bill_number'],
            'state' => 0,
        );
        $tmp = M('check_bill')->where($where)->find();
        if ($tmp) {
            alert_false('存在还没有执行核对的任务');
        }
        try {
            $res = M('check_bill')->add($data);
        } catch (\Exception $e) {
            alert_false();
        }
        if ($res) {
            after_alert(array('fresh' => true));
        } else {
            alert_false();
        }
    }
}