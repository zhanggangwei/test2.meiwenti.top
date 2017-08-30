<?php
/**
 * Created by PhpStorm.
 * 大订单的相关操作都写这
 * User: zgw
 * Date: 2017/4/5
 * Time: 11:23
 */
namespace Vip\Controller;

class OrdersController extends CommonController {
    private $is_private; // 是否是自己玩的,1是自己玩;0 不是，是要推送的.
    /**
     * 新增订单
     */
    public function addOrder(){
        if (IS_POST) {
            $post = I('post.');
            // 生成订单号
            if (!$post['order_id']) {
                $post['order_id'] = 'MWT'.session('phone').'T'.time();
            }
            // 根据订单类型不同生成对应身份的id
            if ($post['order_t'] == 1) {
                // 采购订单
                $post['buyer_id'] = session('company_id'); // 自己公司id
                if (!$post['seller_id']) {
                    alert('销售方不能为空', 300);
                }
                $post['seller_id'] = $this->getOrderCompanyId($post['seller_id']);
                $judge_company = $post['seller_id'];
            } else {
                // 销售订单
                $post['seller_id'] = session('company_id');// 自己公司id
                if (!$post['buyer_id']) {
                    alert('采购方不能为空', 300);
                }
                $post['buyer_id'] = $this->getOrderCompanyId($post['buyer_id']);
                $judge_company = $post['buyer_id'];
            }
            if ($judge_company == session('company_id')) {
                alert('双方不能是同一个公司', 300);
            }
            // 如果是自己玩的就不做，订单状态变为3；如果对方在系统，做推送（推送给公司还是人？）提醒:您有一份订单待确认。
            if ($this->is_private == 1) {
                $post['order_type'] = 3;
                $post['is_private'] = 1;
            } else {
                // 推送
                $post['is_private'] = 0;
            }

            $post['creator'] = session('user_id');
            $post['log_id'] = session('company_id');
            $post['n_log_id'] = $judge_company;

            M()->startTrans();
            $res = D('Orders')->addData($post);
            $detail_data = array(
                'orders_id' => $res['message'],
                'begin_address' => $post['address'],
                'end_address' => $post['address1']
            );
            $detail_data_res = M('orders_address_detail')->add($detail_data);
            D('GeneralCompany', 'Logic')->log($judge_company, 1);
            if ($res['code'] && $detail_data_res) {
                M()->commit();
                $array = array('statusCode' => 200, 'message' => '处理成功！', 'reload' => true, 'tabid' => 'Vip_Orders_addOrder');
                echo json_encode($array);exit;
            } else {
                M()->rollback();
                alert('处理失败', 300);
            }
        }

        // 获取公司默认gps, 地址由页面反编译
        $gps= getComapnyGps($_SESSION['company_id']);


        $urlgps=$gps['x'].','.$gps['y'];
        $amap_data = file_get_contents("http://restapi.amap.com/v3/geocode/regeo?key=fdb9da26af3d2b60194ede08a75b0aea&location=$urlgps&extensions=base&batch=false&roadlevel=0");
        $amap_data=json_decode($amap_data,true);
         $company_amap_address=$amap_data['regeocode']['formatted_address'];


        $company_gps_data=array(
            'address'=>$company_amap_address,
            'gps'=>$urlgps
        );
        $this->assign('company_gps_data',$company_gps_data);
        // 获得煤的种类
        $this->coalType = M('coal_type')->select();
        $this->display();
    }

    /**
     * 订单列表
     */
    public function orderList(){
        $info = $this->getOrderList();
        $this->assign($info);
        // dump($info);exit;
        // echo json_encode($info);
        $this->display();
    }

    //订单列表ajax获取分页
    public function ajaxGetOrderList(){
        $info = $this->getOrderList();
        echo json_encode($info);
    }

    /**
     * 审核订单,待我审核的订单
     */
    public function getAuditOrder(){
        $db = M('Orders o');
        $company_id = session('company_id');

        $where = array();

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        if ($order_id = I('order_id', '')) {
            $where['o.order_id'] = array('like', '%'.$order_id.'%');
        }
        // if ($account = I('account', '')) {
        //     $where['a.account'] = array('like', $account.'%');
        // }

        $where['_string'] = '(log_id = '.$company_id.' and order_type = 2) or (log_id != '.$company_id.' and order_type = 1)';
        $where['_complex'] = array('buyer_id' => $company_id, 'seller_id' => $company_id, '_logic'=>'or');

        $count = $db
            ->join('left join coal_company a on a.id = o.buyer_id')
            ->join('left join coal_company b on b.id = o.seller_id')
            ->join('left join coal_coal_type t on t.id = o.coal_type')
            ->where($where)->count();

        $data = $db
            ->field('o.id, o.order_id, o.order_type, a.name as buyer_name, b.name as seller_name, o.coal_type,
             o.total_money, t.name as coal_type_name,o.quantity, o.create_time')
            ->join('left join coal_company a on a.id = o.buyer_id')
            ->join('left join coal_company b on b.id = o.seller_id')
            ->join('left join coal_coal_type t on t.id = o.coal_type')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('o.create_time desc, o.id desc')
            ->select();

        foreach ($data as $key => $value) {
            $option1 = "{
                id:'order_edit',
                url:'".U('Vip/Orders/edit')."',
                data:{id:'".$value['id']."'},
                type:'get',
                height:'400'
            }";
            $option2 = "{
                type:'get',
                data:{id:'".$value['id']."', order_type:'3'},
                url:'".U('Vip/Orders/editOne')."',
                confirmMsg:'确定要提交确认吗？'
                }";
            $option3 = "{
                type:'get',
                data:{id:'".$value['id']."', order_type:'2'},
                url:'".U('Vip/Orders/editOne')."',
                confirmMsg:'确定要退回吗？'
                }";
            // 详情
            $option4 = "{
                id:'order_audit',
                url:'".U('Vip/Orders/findOne')."',
                data:{id:'".$value['id']."'},
                type:'get',
                height:'600',
                width: '500'
                }";
            // 待我确认：2提交确认、3退回、4详情；对方退回：1修改、4详情
            $dostr = create_button($option4, 'dialog', '查看详情');

            if ($value['order_type'] == 1) {
                $dostr .= create_button($option3, 'doajax', '退回');
                $dostr .= create_button($option2, 'doajax', '提交确认');
            } else {
                $dostr .= create_button($option1, 'dialog', '修改');
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

    //获取订单列表
    private function getOrderList(){
        $db = M('Orders o');
        $where = array(
            'order_type' => array('in', array(1,2,3,6)),
            '_complex' => array('buyer_id' => session('company_id'), 'seller_id' => session('company_id'),'_logic'=>'or')
        );
        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 10);

        if ($order_id = I('order_id', '')) {
            $where['o.order_id'] = array('like', '%'.$order_id.'%');
        }
        // if ($account = I('account', '')) {
        //     $where['a.account'] = array('like', $account.'%');
        // }

        $count = $db
            ->join('left join coal_company a on a.id = o.buyer_id')
            ->join('left join coal_company b on b.id = o.seller_id')
            ->join('left join coal_coal_type t on t.id = o.coal_type')
            ->where($where)->count();

        $data = $db
            ->field('o.id, o.order_id, o.order_type, a.name as buyer_name, b.name as seller_name, t.name as coal_type_name, 
            o.total_money, o.quantity, o.create_time, o.buyer_id, o.seller_id, o.log_id')
            ->join('left join coal_company a on a.id = o.buyer_id')
            ->join('left join coal_company b on b.id = o.seller_id')
            ->join('left join coal_coal_type t on t.id = o.coal_type')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('o.create_time desc, o.id desc')
            ->select();

        foreach ($data as $key => $value) {
            $name = ($value['buyer_id'] == session('company_id'))?$value['seller_name']:$value['buyer_name'];
            $action = ($value['buyer_id'] == session('company_id'))?' 采购':' 销售';
            $logistics = ($value['log_id'] == session('company_id'))?'并负责物流':'对方负责物流';
            if ($value['order_type'] == 6) {
                $msg1 = '我公司 '.$value['create_time'].' 向 '.$name.$action.', <span style="color:red">系统自动派出的提煤单</span>';
            } else {
                $msg1 = '我公司 '.$value['create_time'].' 向 '.$name.$action.' '.$logistics.'，'.$name.getOrderType($value['order_type']);
            }
            $data[$key]['order_status'] = $msg1;

            // 订单统计情况
            $statis_str = '';
                // 已经安排的吨数
                $arr_w = M('logistics')->where(array('order_id' => $value['order_id']))->sum('quantity');
                $arr_w = $arr_w?$arr_w:0;
                // 未安排的吨数
                $res_arr_w = $value['quantity'] - $arr_w;
                //计划单已完成吨数
                $finish_quantity = M('bill')->where(array('order_id' => $value['order_id'], 'state' => 6))->sum('arrange_w');
                $finish_quantity = $finish_quantity?$finish_quantity:0;
                // $data[$key]['finish_quantity'] = $finish_quantity?$finish_quantity:0;
                // 正在进行的吨数
                $doing_quantity = M('bill')->where(array('order_id' => $value['order_id'], 'state' => array('lt', 6)))->sum('arrange_w');
                $doing_quantity = $doing_quantity?$doing_quantity:0;
                //剩余吨数
                $res_quantity = M('logistics')->where(array('order_id' => $value['order_id']))->sum('res_quantity');
                $res_quantity = $res_quantity?$res_quantity:0;
                // $data[$key]['res_quantity'] = $value['quantity'] - $finish_quantity;

            $statis_str .= '已安排：'.$arr_w.'(未完成'.$res_quantity.'，进行中'.$doing_quantity.'，已完成'.$finish_quantity.')<br>未安排：'.$res_arr_w;
            $data[$key]['statistics'] = $statis_str;

            // 实际完成吨数
            $finished_w = M('bill')->where(array('order_id' => $value['order_id'], 'state' => 6))->sum('end_w');
            $finished_w = $finished_w?$finished_w:0;
            $data[$key]['finished_w'] = $finished_w;

            $option3 = "{
                id:'order_detail',
                url:'".U('Vip/Orders/findOne')."',
                data:{id:'".$value['id']."'},
                type:'get',
                height:'600',
                width: '500'
                }";
            $option1 = "{
                id:'order_edit',
                url:'".U('Vip/Orders/edit')."',
                data:{id:'".$value['id']."'},
                type:'get',
                width:'800',
                height:'600'
                }";
            $option2 = "{
                type:'get',
                data:{id:'".$value['id']."'},
                url:'".U('Vip/Orders/del')."',
                confirmMsg:'确定要删除吗？'
                }";
            $dostr = create_button($option3, 'dialog', '查看详情');
            if ($value['order_type'] == 1 || $value['order_type'] == 2) {
                $dostr .= create_button($option1, 'dialog', '编辑');
                $dostr .= create_button($option2, 'doajax', '删除');
            }
            if ($finished_w >= $value['quantity']) {
                // 实际完成吨数大于订单总数时，给按钮手动结束大订单
                $option4 = "{
                type:'get',
                data:{id:'".$value['id']."'},
                url:'".U('Vip/Orders/closeOrder')."',
                confirmMsg:'确定操作吗？'
                }";
                $dostr .= create_button($option4, 'doajax', '结束订单');
            }
            // 物流详情
            $option5 = "{
                id:'Orders_logisCount',
                url:'".U('Vip/Orders/logisCount')."',
                data:{order_id:'".$value['order_id']."'},
                type:'get',
                width:'800',
                height:'600'
                }";
            $dostr .= create_button($option5, 'dialog', '物流详情');
            // echo $dostr;exit;
            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        // 某个订单的状态

        // dump($info);exit;
        return $info;
    }

    // 手动结束订单
    public function closeOrder(){
        $id = I('id');
        $info = M('orders')->find($id);
        if ($info & $info['log_id'] == session('company_id')) {
            //  严格验证已经拉运大于订单数，待做

            $res = M('orders')->save(array('id' => $id, 'order_type' => 4));
            show_res($res);
        } else {
            alert_illegal();
        }
    }

    // 加载物流统计
    public function logisCount(){
        $this->order_id = I('order_id');
        $this->display();
    }

    // 物流统计
    public function getLogisCount(){
        $db = M('logistics l');
        $company_id = session('company_id');

        $where = array(
            'l.state' => 1,
            '_complex' => array('o.buyer_id' => $company_id, 'o.seller_id' => $company_id, '_logic' => 'or')
        );

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        if ($order_id = I('get.order_id', '')) {
            $where['l.order_id'] = $order_id;
        } else {
            alert_illegal();
        }
        // if ($account = I('account', '')) {
        //     $where['a.account'] = array('like', $account.'%');
        // }

        // $where['_string'] = '(log_id = '.$company_id.' and order_type = 2) or (log_id != '.$company_id.' and order_type = 1)';
        // $where['_complex'] = array('buyer_id' => $company_id, 'seller_id' => $company_id, '_logic'=>'or');

        $count = $db
            ->join('left join coal_orders o on o.order_id = l.order_id')
            // ->join('left join coal_company a on a.id = o.buyer_id')
            // ->join('left join coal_company b on b.id = o.seller_id')
            // ->join('left join coal_coal_type t on t.id = o.coal_type')
            ->where($where)
            ->group('l.assigned_id')
            ->count();

        $data = $db
            ->field('l.assigned_id,sum(l.quantity) as arr_w')
            ->join('left join coal_orders o on o.order_id = l.order_id')
            // ->join('left join coal_company a on a.id = o.buyer_id')
            // ->join('left join coal_company b on b.id = o.seller_id')
            // ->join('left join coal_coal_type t on t.id = o.coal_type')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            // ->order('o.create_time desc, o.id desc')
            ->group('l.assigned_id')
            ->select();

        foreach ($data as $key => $value) {
            // 公司名
            $data[$key]['name'] = M('company')->where(array('id' => $value['assigned_id']))->getField('name');
            // 车次，完成吨数
            $res = M('bill')->field('count(id) as times, sum(end_w) as sum_w')->where(array('order_id' => $order_id, 'company' => $value['assigned_id'], 'state' => 6))->select();
            $data[$key]['times'] = $res[0]['times'];
            $data[$key]['sum_w'] = $res[0]['sum_w'];
            // 完成率
            $rate = round(($res[0]['sum_w'] / $value['arr_w']) * 10000) / 100;
            $data[$key]['rate'] = $rate.'%';
        }

        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        // dump($info);exit;
        echo json_encode($info);
    }

    // 正在进行的订单,先不上线


    // 历史订单,先不上线
    public function getOrdersHistory(){
        $tag = 'orders o';
        $db = M($tag);
        $where = array(
            'o.order_type' => array('in', array(4,5)),
            '_complex' => array('buyer_id' => session('company_id'), 'seller_id' => session('company_id'),'_logic'=>'or'),
        );

        $page_num  = I('pageCurrent', 1) - 1;
        $page_size = I('pageSize', 2);

        $count = $db
            ->join('left join coal_company a on a.id = o.buyer_id')
            ->join('left join coal_company b on b.id = o.seller_id')
            ->join('left join coal_coal_type t on t.id = o.coal_type')
            ->where($where)->count();

        $data = $db
            ->field('o.id, o.order_id, o.order_type, a.name as buyer_name, b.name as seller_name, t.name as coal_type_name,
            o.total_money, o.quantity, o.create_time, o.buyer_id, o.seller_id, o.log_id')
            ->join('left join coal_company a on a.id = o.buyer_id')
            ->join('left join coal_company b on b.id = o.seller_id')
            ->join('left join coal_coal_type t on t.id = o.coal_type')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('o.create_time asc')
            ->select();

        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 查看详情
    public function findOne(){
        $id = I('id');
        $res = M('orders o')
            ->field('o.*, a.name as buyer_name, b.name as seller_name, t.name as coal_type_name, d.begin_address, d.end_address')
            ->join('left join coal_company a on a.id = o.buyer_id')
            ->join('left join coal_company b on b.id = o.seller_id')
            ->join('left join coal_coal_type t on t.id = o.coal_type')
            ->join('left join coal_orders_address_detail d on d.orders_id = o.id')
            ->where(array('o.id' => $id))
            ->find();
        // dumpT($res);
        $this->assign('info',$res);
        $this->display();
    }

    // 编辑订单
    public function edit(){
        if (IS_POST) {
            $post = I('post.');
            $map = array('id' => $post['id']);
            $tmp = M('orders')->find($post['id']);
            if ($tmp) {
                $post['order_type'] = 1;
                $post['seller_id'] = $tmp['seller_id'];
                $post['buyer_id'] = $tmp['buyer_id'];
                $res = D('Orders')->editData($map, $post);
                if ($res['code']) {
                    if ($res['info'] !== false) {
                        $array = array('statusCode' => 200, 'message' => '处理成功！', 'closeCurrent' => true, 'tabid' => 'Orders_auditOrder');
                        echo json_encode($array);exit;
                    } else {
                        alert('处理失败！', 300);
                    }
                } else {
                    alert($res['info'], 300);
                }
            } else {
                alert_illegal();
            }
        }
        $id = I('get.id');

//        $info = M('orders')->getField('id,nickname,email')->find($id);

//        $info=M()->query("select order_id,order_type,buyer_id,seller_id,ST_X(begin_gps) as begin_gps_x,ST_Y(begin_gps) as begin_gps_y,ST_X(end_gps) as end_gps_x,ST_Y(end_gps) as end_gps_y,coal_type,quantity,price,total_money,creator,create_time,log_id,n_log_id,is_private from coal_orders where id=$id");
//

        $info = M("orders");

        $vv=$info->field('id,order_id,order_type,buyer_id,seller_id,IFNULL(ST_X(begin_gps),109.993041) as begin_gps_x,IFNULL(ST_Y(begin_gps),39.81681) as begin_gps_y,IFNULL(ST_X(end_gps),109.993041) as end_gps_x,IFNULL(ST_Y(end_gps),39.81681) as end_gps_y,coal_type,quantity,price,total_money,creator,create_time,log_id,n_log_id,is_private')-> where("id=$id")->find();


        $gps= getComapnyGps($_SESSION['company_id']);
        $urlgps=$gps['x'].','.$gps['y'];
        $amap_data = file_get_contents("http://restapi.amap.com/v3/geocode/regeo?key=fdb9da26af3d2b60194ede08a75b0aea&location=$urlgps&extensions=base&batch=false&roadlevel=0");
        $amap_data=json_decode($amap_data,true);
        $company_amap_address=$amap_data['regeocode']['formatted_address'];

        $order_type_data=M()->query("select * from coal_orders where log_id=buyer_id and id=$id");
        if(empty($order_type_data)){
            $order_type="销售订单";
        }else{
            $order_type="采购订单";
        }




        $company_gps_data=array(
            'address'=>$company_amap_address,
            'gps'=>$urlgps
        );
        $this->assign('company_gps_data',$company_gps_data);

        if ($vv) {
            // 基本信息
            $this->info = $vv;
            $this->order_type = $order_type;
            // 煤种
            $this->coal_info = M('coal_type')->select();
            $this->display('editOrder');
        } else {
            alert_illegal();
        }
    }

    // 确认订单和退回，只对审核操作页面
    public function editOne(){
        $id = I('id');
        $order_type = I('order_type');
        $res = M('orders')->save(array('id' => $id, 'order_type' => $order_type));
        if ($res !== false) {
            $array = array('statusCode' => 200, 'message' => '处理成功！', 'closeCurrent' => true, 'tabid' => 'Orders_auditOrder');
            echo json_encode($array);exit;
        } else {
            alert('处理失败', 300);
        }
    }

    // 删除订单
    public function del(){
        // 确认过的订单不能删除
        $id = I('id');
        $order = M('orders')->find($id);
        if ($order['order_type'] > 3) {
            alert('订单已确认，不能删除', 300);
        }
        $res = M('orders')->save(array('id' => $id, 'order_type' => 5)); // 逻辑删除
        show_res($res);
    }

    /**
     * 得到公司的id，用于在没有公司时，新增自己玩的公司，等待认领
     * @param $company_name
     * @return integer
     */
    public function getOrderCompanyId($company_name){
        // 如果找到返回id。如果不是总公司(只有两级的)，返回总公司id。如果没有这个公司，新增后返回公司id
        $company = M('company')->field('id,pid,is_passed')->where(array('name' => trim($company_name)))->find();
        if ($company) {
            if (is_company_have_manage($company['id'])) {
                $this->is_private = 0;
            }else {
                $this->is_private = 1;
            }
            // 如果传的是分公司的名字，就返回总公司的id。这一处的逻辑有待测试，验证
            if ($company['pid'] != 0) {
                $company_id = $company['pid'];
            } else {
                $company_id = $company['id'];
            }
        } else {
            $this->is_private = 1;
            $data = array(
                'name' => $company_name,
                'is_passed' => 0,
                'is_vip' => 0,
                'create_time' => get_time()
            );
            $company_id = M('company')->add($data);
        }
        return $company_id;
    }

}