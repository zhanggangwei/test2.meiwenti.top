<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/4/7
 * Time: 9:56
 */
namespace Vip\Controller;

class CompanyController extends CommonController {

    // 搜索公司
    // type 1:所有公司=》新增订单
    //      2:审核过的公司=》新增物流安排
    public function searchCompany($type = 1){
        $db = M('company');
        $where = array();
        switch ($type) {
            case 2:
                $where['is_passed'] = 1;
                // $where['id'] = array('neq', session('company_id'));// 2017年5月18日10:45:38 薛 物流公司作为贸易商安排自己拉货
                break;
            default:
                break;
        }
        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        if ($name = trim(I('name', ''))) {
            $where['name'] = array('like', '%'.$name.'%');
        }
        // if ($account = I('account', '')) {
        //     $where['a.account'] = array('like', $account.'%');
        // }

        $count = $db
            ->where($where)->count();

        $data = $db
            ->field('id,name,IFNULL(ST_X(add_gps),109.993041) as x,IFNULL(ST_Y(add_gps),39.81681) as y')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->select();
        // echo M()->_sql();

        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 监听是否有计划单
    public function listening(){
        // 当计划有变化时或当车辆有变化时
        $where = array(
            'assigned_id'     => session('company_id'),
            'state'           => 1,
            'res_quantity'    => array('gt', 0),
            );
        $count = M('logistics')->where($where)->count();
        $data['count'] = $count;
        if ($count > 0) {
            // 是否有变动
            $info = M('logistics')->where($where)->select();
            $info = json_encode($info);

            if ($info == session('logis_ids')) {
                $data['is_change'] = 0;
            } else {
                $data['is_change'] = 1;
                session('logis_ids', json_encode($info));
            }
            // 是否是自动派单
            $data['auto_state'] = D('Company', 'Logic')->getAutoArrBillState();
        } else {
            session('logis_ids', 1);
        }
        echo json_encode($data);
    }

    // 获取首页数据
    public function getDynamicData($type = 1){
        // 当月招标量
        $tender = M('company')->where(array('id' => session('company_id')))->getField('tender');
        // sql();
        // 月数据
        $where_month = array(
            'company'  => session('company_id'),
            'state'    => 6,
            'dis_time' => array('between', array(date("Y-m-01 0:0:0"), date("Y-m-d 23:59:59")))
            );
        $timers_count = M('bill')->where($where_month)->count();
        $quantity_count = M('bill')->where($where_month)->sum('end_w');

        // 日数据
        $where_day = array(
            'company'  => session('company_id'),
            'state'    => 6,
            'dis_time' => array('between', array(date("Y-m-d 0:0:0"), date("Y-m-d 23:59:59")))
            );
        $day_timers_count = M('bill')->where($where_day)->count();
        $day_quantity_count = M('bill')->where($where_day)->sum('end_w');

        // 待安排吨数
        $res_quantity = M('logistics')
            ->where(array('assigned_id' => session('company_id'), 'state' => 1))
            ->sum('res_quantity');
        // sql();
        $data = array(
            'tender'  => $tender?$tender:0,
            'quantity_count'  => $quantity_count + 0.00,
            'timers_count'  => $timers_count,
            'day_quantity_count'  => $day_quantity_count + 0.00,
            'day_timers_count'  => $day_timers_count,
            'res_quantity'  => $res_quantity,
            );
        if ($type == 1) {
            return $data;
        } else {
            echo json_encode($data);
        }
    }

    // 获取当前(物流)公司用户的最新计划单
    public function getAssignedLogistics(){
        $tag = 'logistics l';
        $db = M($tag);
        $where = array('l.res_quantity' => array('gt', 0), 'l.state' => 1, 'l.assigned_id' => session('company_id'));

        $page_num  = I('pageCurrent', 1) - 1;
        $page_size = I('pageSize', 2);

        // if ($account = I('account', '')) {
        //     $where['a.account'] = array('like', $account.'%');
        // }

        $count = $db
            ->join('left join coal_orders o on o.order_id = l.order_id')
            ->join('left join coal_company a on a.id = o.buyer_id')
            ->join('left join coal_company b on b.id = o.seller_id')
            ->where($where)
            ->count();

        $data = $db
            ->field('l.*, ct.name as coal_type_name, a.name as buyer_name, b.name as seller_name, c.name as assign_name ')
            ->join('left join coal_orders o on o.order_id = l.order_id')
            ->join('left join coal_company a on a.id = o.buyer_id')
            ->join('left join coal_company b on b.id = o.seller_id')
            ->join('left join coal_company c on c.id = l.assign_id')
            ->join('left join coal_coal_type ct on ct.id = o.coal_type')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('l.create_time asc,l.id asc')
            ->select();
        // dump($data);
        foreach ($data as $key => $value) {
            // 重新组合
            $data[$key]['order_id'] = $value['order_id'] . '<br/>(' . $value['create_time'] . ')';
            $data[$key]['buy_sell'] = $value['seller_name'] . '<br/>==><br/>' . $value['buyer_name'] . '';
            $data[$key]['coal_type_name'] = $value['coal_type_name'] . '<br/>' . $value['res_quantity'] . '吨';

            if (D('Company', 'Logic')->getAutoArrBillState() == 0) {
                $option = "{
                    id:'arr_bill',
                    url:'".U('Vip/Bill/arrBill')."',
                    data:{id:'".$value['id']."',type:1},
                    type:'get',
                    height:'450',
                    width:'600',
                    onClose:function(){start_clock();}
                }";
            
                // $dostr = create_button($option, 'dialog', '派单');
                $dostr = '<button type="button" class="btn-default" onclick="stop_clock()" data-toggle="dialog" data-options="'.$option.'">派单</button>';
                $data[$key]['dostr'] = $dostr;
            }
        }
        
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 获取当前公司在排队的车辆
    public function getWaitTrucks(){
        $tag = 'truck t';
        $db = M($tag);
        $where = array('t.user_id' => session('company_id'), 't.state' => 1, 't.is_passed' => 1);

        $page_num  = I('pageCurrent', 1) - 1;
        $page_size = 300;

        $count = $db
            ->where($where)
            ->count();

        $data = $db
            ->field('t.*')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('t.last_time asc, t.id asc')
            ->select();
        // dump($data);
        $data1 = array();
        $i = 0;
        foreach ($data as $key => $value) {
            $is_dispatch = D('Truck', 'Logic')->isDispatch($value['id']);

            // dump($value['lic_number']);
            // dump($is_dispatch);
            if ($is_dispatch['code'] == 1) {
                $data1[$i] = $value;
                $data1[$i]['last_time'] = $value['last_time']?getday($value['last_time']):'该车辆还未使用';

                $data1[$i]['last_time1'] = $value['last_time']?$value['last_time']:'该车辆还未使用';

                // 拥有者
                $data1[$i]['owner_name'] = get_trucker_name($value['id']);
                // 上次司机
                $bill = M('bill')->field('driver_id')->where(array('truck_id' => $value['id'], 'state' => 6))->order('id desc')->limit(1)->find();
                $d_name = M('users')->where(array('id' => $bill['driver_id']))->getField('real_name');
                $data1[$i]['last_driver'] = $d_name;

                // 排队车辆反馈情况
                $feedback_res = M('truck_restatue')
                    ->where(array('lic_number' => $value['lic_number'], 'sys_name' => array('in', $value['jiyun']), 'is_latest' => 1))
                    ->order('sys_name asc')->select();
                $feedback = '';
                foreach ($feedback_res as $val) {
                    $feedback .= '集运站：'.$val['sys_name'].'，时间：'.date('m-d H:i:s', strtotime($val['update_time'])).
                        '<br>'.$val['state'].'<div style="display:block;background:#999;margin:0;padding-top:1px"></div>';
                }
                $data1[$i]['feedback'] = rtrim($feedback,'<div style="display:block;background:#999;margin:0;padding-top:1px"></div>');
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
            'totalRow' => count($data1),
            'pageSize' => $page_size,
            'list'     => $data1,
        );
        echo json_encode($info);
    }

    // 改变当前公司的auto_arrbill_state
    public function changeAutoState(){
        $type = I('get.type', 0);
        $res = M('company')->where(array('id' => session('company_id')))->save(array('auto_arrbill' => $type));
        show_res($res);
    }

    // 修改当前公司的GPS地址,修改当前公司的基本信息
    public function editCompanyInfo(){
        if (IS_POST) {
            $post = I('post.');
            // 公司地址
            $data = array(
                'id' => session('company_id'),
                'province' => $post['province'],
                'city' => $post['city'],
                'area' => $post['area'],
                'detail' => $post['detail'],
                'tender' => $post['tender'],
            );
            // dump($post);exit;
            $res = M('company')->save($data);
            // 更新公司坐标
            $res1 = update_company_gps($post['gps'],$data['id']);
            if ($res !== false && $res1 !== false) {
                after_alert(array('closeCurrent' => true));
            } else {
                alert_false();
            }
        }
        // 公司的基本信息
        $company_id = session('company_id');
        $info = M('company')->field('add_gps', true)->where(array('id' => $company_id))->find();
        $info['add_gps'] = getComapnyGps($company_id);

        if(empty($info['add_gps']['x'])) {
            $info['add_gps'] = array(
                'x' => '1',
                'y' => '1'
            );
        };
        $data = array(
            'info'  => $info,
            'province'  => get_provice(),
            'cities'  => get_shiqu($info['province']),
            'area'  => get_diqu($info['province'],$info['city']),
        );
        $this->assign($data);
        $this->display();
    }

    // 改变当前公司的首页最新订单显示状态
    public function changeIndexOrder(){
        $type = I('get.type', 0);
        $res = M('company')->where(array('id' => session('company_id')))->save(array('is_hide_index_order' => $type));
        show_res($res);
    }
}