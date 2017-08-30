<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/4/10
 * Time: 14:21
 */
namespace Vip\Controller;

class LogisticsController extends CommonController {

    /**
     * getLogistics 获得已确定的大订单列表
     * @return 
     */
    public function getLogistics(){
        $tag = 'orders o';
        $db = M($tag);
        $where = array(
            'o.log_id' => session('company_id'),
            'o.order_type' => 3
        );

        $page_num  = I('pageCurrent', 1) - 1;
        $page_size = I('pageSize', 2);

        $count = $db
            ->where($where)->count();

        $data = $db
            ->field('o.id, o.order_id, o.order_type, a.name as buyer_name, b.name as seller_name, o.coal_type, 
            o.total_money, o.quantity, o.create_time, o.buyer_id, o.seller_id, o.log_id')
            ->join('left join coal_company a on a.id = o.buyer_id')
            ->join('left join coal_company b on b.id = o.seller_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('o.create_time desc,o.id desc')
            ->select();
        // sql();
        foreach ($data as $key => $value) {
            // 已经安排的物流
            $arrange_quantity = M('logistics')->where(array('order_id' => $value['order_id'], 'state' => 1))->sum('quantity');
            $arrange_quantity += 0;
            $data[$key]['arrange_quantity'] = $arrange_quantity;
            // 未安排的物流
            $data[$key]['leave_quantity'] = $value['quantity'] - $arrange_quantity;

            // 正在进行的吨数
            $doing_quantity = M('bill')->where(array('order_id' => $value['order_id'], 'state' => array('lt', 6)))->sum('arrange_w');
            //已完成吨数
            $finish_quantity = M('bill')->where(array('order_id' => $value['order_id'], 'state' => 6))->sum('end_w');
            $data[$key]['real_quantity'] = $finish_quantity?$finish_quantity:0;
            $data[$key]['doing_quantity'] = $doing_quantity?$doing_quantity:0;
            // 操作
            $option1 = "{
                id:'logistics_add',
                url:'".U('Vip/Logistics/addLogistics')."',
                data:{id:'".$value['id']."'},
                type:'get',
                height:'450',
                width:'600'
            }";

            $dostr = create_button($option1,'dialog','安排物流');
            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    /**
     * 增加物流安排
     */
    public function addLogistics(){
        // 已确定订单数据
        $id = I('id');
        $info = M('orders')->field('id,order_id,quantity')->where(array('id' => $id))->find();

        if (IS_POST) {
            $post = I('post.');
            $order_id = $post['order_id'];
            $assigned_id = $post['assigned_id'];
            $price = $post['price'];
            $quantity = $post['quantity'];

            //查看是否是自己安排物流的订单
            if (D('Orders')->isAbnormal($order_id)) {
                alert('订单状态异常', 300);
            }
            //查看物流公司是不是已经通过审核
            if (D('Company')->isAbnormal($assigned_id)) {
                alert('选择的物流公司状态异常', 300);
            }
            //查看是否还有没有运完的吨数
            if (D('Orders', 'Logic')->isArrOverOrder($quantity, $order_id)) {
                alert('安排吨数过多', 300);
            }
            // 物流安排是否大于订单总数
            $arr_quantity = M('logistics')->where(array('order_id' => $order_id, 'state' => 1))->sum('quantity');
            $info = M('orders')->field('id,order_id,quantity')->where(array('order_id' => $order_id))->find();
            if ($quantity + $arr_quantity + 0 > $info['quantity']) {
                alert('剩余安排吨数不足', 300);
            }
            // 2017年6月13日14:37:40 zgw 是否可以安排物流，以前是根据实际是否拉完来算（一期）。今日改为用已安排的物流订单的总数来算
            //查看物流公司是不是已经安排过物流还没完成
            if (!D('Logistics')->isLastFinshed($assigned_id, $order_id)) {
                alert('该物流公司还有未完成的吨数', 300);
            }

            // 安排物流
            $res = D('Logistics', 'Logic')->arrange($order_id, $assigned_id, $price, $quantity);

            if ($res['code']) {
                D('Push', 'Logic')->noticeManager('派车计划',getCompanyName(session('company_id')).'向贵公司安排一个计划，请尽快处理', $assigned_id);
                $array = array('statusCode' => 200, 'message' => '处理成功！', 'closeCurrent' => true, 'tabid' => 'Logistics_logistics');
                echo json_encode($array);exit;
            } else {
                alert('处理失败', 300);
            }
        }
        $this->info = $info;
        // dump_text_exit($this->info);
        $this->display();
    }

    /**
     * 进行中的物流
     */
    public function getLogisticsing(){
        $tag = 'logistics l';
        $db = M($tag);
        $where = array('res_quantity' => array('gt', 0), 'state' => 1, 'assign_id' => session('company_id'));

        $page_num  = I('pageCurrent', 1) - 1;
        $page_size = I('pageSize', 2);

        // if ($account = I('account', '')) {
        //     $where['a.account'] = array('like', $account.'%');
        // }

        $count = $db
            ->where($where)->count();

        $data = $db
            ->field('l.*, a.name as assigned_name')
            ->join('left join coal_company a on a.id = l.assigned_id')
            // ->join('left join coal_company b on b.id = o.seller_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('l.create_time desc,l.id desc')
            ->select();
        foreach ($data as $key => $value) {
            //已完成吨数
            $finish_quantity = M('bill')
                ->where(array('order_id' => $value['order_id'], 'company' => $value['assigned_id'], 'state' => 6))
                ->sum('end_w');
            $data[$key]['finish_quantity'] = $finish_quantity?$finish_quantity:0;
            //剩余吨数
            $data[$key]['res_quantity'] = $value['quantity'] - $finish_quantity;

            $option1 = "{
                id:'logistics_add',
                url:'".U('Vip/Logistics/reLogistics')."',
                data:{id:'".$value['id']."'},
                type:'get',
                height:'300',
                width:'600'
            }";
            $option2 = "{
                id:'logistics_detail',
                url:'".U('Vip/Logistics/logisDetail')."',
                data:{id:'".$value['id']."'},
                type:'get',
                height:'600',
                width:'800'
            }";
        
            $dostr = create_button($option1,'dialog','重新安排').create_button($option2,'dialog','拉运情况');
            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 拉运情况
    public function logisDetail(){
        $logis_id = I('id');
        // echo $logis_id;
        $bill = M('bill b')
            ->field('b.*,t.lic_number')
            ->join('left join coal_truck t on t.id = b.truck_id')
            ->where(array('b.logistics_id' => $logis_id, 'b.state' => array('lt',6)))
            ->select();
        // sql();
        foreach ($bill as $key => $val) {
            // 操作
            // 查看地图
            $option = "{
                id:'arr_bill',
                url:'".U('/Vip/map/map_realTime')."',
                data:{userid:'".$val['driver_id']."',limit:'5'},
                type:'get',
                height:'450',
                width:'600',
                onClose:'socket.close()'
            }";
            $dostr = create_button($option, 'dialog', '查看地图');
            $bill[$key]['dostr'] = $dostr;
        }
        $this->bill = $bill;
        $this->display();
    }

    // 物流历史记录，物流统计，页面
    public function logisticsStatistics(){
        // 对于所有的公司
        $companies = M('bill b')
            ->field('c.id,c.name')
            ->join('left join coal_company c on c.id = b.company')
            ->where(array('_complex' => array('seller' => session('company_id'), 'buyer' => session('company_id'),'_logic'=>'or')))
            ->group('company')->select();
        $bill_state = C('BILL_STATE');
        $data = array(
            'bill_state'  => $bill_state,
            'companies'  => $companies,
        );
        $this->assign($data);
        $this->display();
    }

    /**
     * 物流历史记录，物流统计
     */
    public function getLogisticsStatistics(){
        $tag = 'bill b';
        $db = M($tag);
        $current_company = session('company_id');
        $where['_string'] = '(o.is_private = 1 and (b.seller = '.$current_company.' or b.buyer = '.$current_company.')) 
        or (o.is_private = 1 and b.order_id = o.order_id and b.company='.$current_company.')';
        // 2017年5月25日11:49:16 zgw 我觉得自己玩的单不用出现在这

        $page_num  = I('pageCurrent', 1) - 1;
        $page_size = I('pageSize', 2);

        // 筛选条件
        // 时间段
        $s_time = I('s_time', date('Y-m-01 00:00:00'));
        $e_time = I('e_time', date('Y-m-d 23:59:59'));
        if ($s_time && $e_time) {
            if ($s_time > $e_time) {
                alert_false('开始时间不能大于结束时间');
            }
            $s_time = date('Y-m-d H:i:s', strtotime($s_time));
            $e_time = date('Y-m-d H:i:s', strtotime($e_time));
            $where['b.dis_time'] = array('between', array($s_time, $e_time));
        }
        // 大订单
        if ($order_id = I('order_id', '')) {
            $where['b.order_id'] = array('like', '%'.$order_id.'%');
        }
        // 物流公司
        if ($company = I('company', '')) {
            $where['b.company'] = $company;
        }
        // 状态
        $bill_state = I('bill_state', 6);
        if (I('bill_state')) {
            $where['b.state'] = $bill_state;
        }

        $count = $db
            ->join('left join coal_company a on a.id = b.buyer') // 买家
            ->join('left join coal_company s on s.id = b.seller')  // 卖家
            ->join('left join coal_company c on c.id = b.company')  // 物流公司
            ->join('left join coal_orders o on o.order_id = b.order_id')
            ->where($where)
            ->count();

        $data = $db
            ->field('b.*,a.name as buyer_name, s.name as seller_name, c.name as company_name
            , o.create_time')
            ->join('left join coal_company a on a.id = b.buyer') // 买家
            ->join('left join coal_company s on s.id = b.seller')  // 卖家
            ->join('left join coal_company c on c.id = b.company')  // 物流公司
            ->join('left join coal_orders o on o.order_id = b.order_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('b.state desc,b.id desc')
            ->select();
        foreach ($data as $key => $value) {
            // 订单创建时间
            $data[$key]['create_time'] = date('Y-m-d',strtotime($value['create_time']));
            // 订单状态
            $bill_state = '';
            switch ($value['state']) {
                case 1:
                    $bill_state = '待接单';
                    break;
                case 2:
                    $bill_state = '已接单';
                    break;
                case 3:
                case 4:
                case 5:
                    $bill_state = '进行中';
                    break;
                case 6:
                    $bill_state = '已完成';
                    break;
                case 7:
                    $bill_state = '物流公司收回';
                    break;
                case 8:
                    $bill_state = '贸易商收回';
                    break;
                case 9:
                    $bill_state = '窜矿处理';
                    break;
                default:
                    break;
            }
            $data[$key]['state'] = $bill_state;
        }
        // dump_text($data);
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    /**
     * 实时查询物流计划
     */
    public function getAssignedLogistics(){
        
    }

    // 重新安排物流
    public function reLogistics(){
        if (IS_POST && I('post.id', 0) && I('post.order_id', 0) && I('post.assigned_id', 0)) {
            // 接收数据
            $logis_id = I('post.id');
            $order_id = I('post.order_id');
            $assigned_id = I('post.assigned_id');

            //查看物流公司是不是已经通过审核
            if (D('Company')->isAbnormal($assigned_id)) {
                alert('物流公司状态异常', 300);
            }

            // 查看物流状态是否正常
            if (!$logistics = D('Logistics')->isBackNormal($logis_id, $assigned_id)) {
                alert('物流状态异常', 300);
            }

            //查看物流公司是不是已经安排过物流还没完成
            if (!D('Logistics')->isLastFinshed($assigned_id, $order_id)) {
                alert('该物流公司还有未完成的吨数', 300);
            }

            M()->startTrans();
            //1、新增一个物流计划
            $res1 = D('Logistics', 'Logic')->arrange($order_id, $assigned_id, $logistics['price'], $logistics['quantity']);
            //2、收回旧计划
            $res2 = D('Logistics', 'Logic')->back($logis_id);
            if ($res1 && $res2) {
                M()->commit();
                after_alert(array('closeCurrent' => true, 'tabid' => 'Logistics_logisticsing'));
            } else {
                M()->rollback();
                alert_false();
            }
        }
        // 物流计划
        $logis_id = I('get.id', 0);
        $logistics = M('logistics')->find($logis_id);
        if ($logistics) {
            $this->logistics = $logistics;
            $this->display();
        } else {
            alert('非常访问', 300);
        }
    }

    // 物流结构图
    public function logisticsOrgChart(){
        // 只取当月的数据
        // 一级，公司名
        $cid = session('company_id');
        $data=array('name'=>getCompanyName($cid));

        // 所有相关物流公司
        $bill = M('bill')->where(array('state' => 6, '_string' => 'seller = '.$cid.' or buyer = '.$cid))->group('company')->select();
        // sql();
        foreach ($bill as $key => $value) {

            $bill1 = M('bill')->where(array('company' => $value['company'],'state' => 6, '_string' => 'seller = '.$cid.' or buyer = '.$cid))->select();
            // sql();
            $tmp_traffickers = array();// 临时数组
            $i = 0;
            foreach ($bill1 as $k => $val) {
                // dump($val);
                if ($val['seller'] == $cid) {
                    $trafficker_id = $val['buyer'];
                    $where = array('seller' => $cid, 'buyer' => $trafficker_id);
                } else {
                    $trafficker_id = $val['seller'];
                    $where = array('seller' => $trafficker_id, 'buyer' => $cid);
                }
                // echo '<br>';
                // echo $trafficker_id;

                if (in_array($trafficker_id, $tmp_traffickers)) {
                    continue;
                } else {
                    $tmp_traffickers[] = $trafficker_id;
                    $day_count = M('bill')
                        ->where(array('company' => $value['company'],'state' => 6))
                        ->where($where)
                        ->where(array('do_time' => array('between',array(date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')))))
                        ->sum('end_w');
                    $day_count = $day_count + 0;
                    // $trafficker[$trafficker_id]['day_count'] = $day_count + 0; // 当日
                    $month_count = M('bill')
                        ->where(array('company' => $value['company'],'state' => 6))
                        ->where($where)
                        ->where(array('do_time' => array('between',array(date('Y-m-1 00:00:00'), date('Y-m-d 23:59:59')))))
                        ->sum('end_w');
                    $month_count = $month_count + 0;
                    // $trafficker[$trafficker_id]['month_count'] = $month_count + 0;// 当月
                    $total_count = M('bill')
                        ->where(array('company' => $value['company'],'state' => 6))
                        ->where($where)
                        ->sum('end_w');
                    // sql();
                    $total_count = $total_count + 0;
                    // $trafficker[$trafficker_id]['total_count'] = $total_count + 0;// 所有

                    // 三级，对方名
                    $trafficker[$i]['name'] = getCompanyName($trafficker_id);
                    $trafficker[$i]['当日'] = $day_count.' 吨';
                    $trafficker[$i]['当月'] = $month_count.' 吨';
                    $trafficker[$i]['所有'] = $total_count.' 吨';
                    $trafficker[$i]['children'] = array(0=>array('name' => '当日：'.$day_count.' 吨'));
                    $i++;
                }
            }
            // 二级，物流公司
            $data['children'][$key]=array('name'=>getCompanyName($value['company']),'children'=>$trafficker);
        }
        // dump($data);
        $this->assign('data',json_encode($data,false));
        $this->display();
    }
}