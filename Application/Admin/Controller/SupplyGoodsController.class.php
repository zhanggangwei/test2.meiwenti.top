<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017-05-07
 * Time: 23:02
 */
namespace Admin\Controller;

class SupplyGoodsController extends CommonController {

    // 供货信息
    public function getSellerNews(){
        $db = M('seller_news');
        $where = array();

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        // if ($name = trim(I('real_name', ''))) {
        //     $where['u.real_name'] = array('like', '%'.$name.'%');
        // }
        // if ($phone = I('phone', '')) {
        //     $where['u.phone'] = $phone;
        // }

        $count = $db
            // ->join('left join coal_company c on c.id = t.owner_id')
            ->where($where)
            ->count();

        $data = $db
            // ->field('t.*, c.name as company_name')
            // ->join('left join coal_company c on c.id = t.owner_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('creat_time desc')
            ->select();
        // sql();
        // dump_text($data);
        foreach ($data as $key => $value) {
            // 煤炭种类
            $data[$key]['coal_type'] = coal_type($value['coal_type']);
            // 交货地
            $data[$key]['buy_address'] = $value['del_address_p'].$value['del_address_s'].$value['del_address_q'];

            // 产地
            $data[$key]['sell_address'] = $value['pro_address_p'].$value['pro_address_s'].$value['pro_address_q'];

            // $option1 = "{
            //     id:'com_truck_edit',
            //     data:{id:'".$value['id']."'},
            //     url:'".U('edit')."'
            //     }";
            $option2 = "{
                id:'supply_goods_detail',
                data:{id:'".$value['id']."'},
                url:'".U('sellerNewsDetail')."',
                width:'800',
                height:'600',
                }";
            $dostr = create_button($option2, 'dialog', '详情');
            if (!$value['is_passed']) {
                $option3 = "{
                type:'get',
                data:{id:'".$value['id']."'},
                url:'".U('passSellNews')."',
                confirmMsg:'确定要审核通过吗？'
                }";
                $dostr .= create_button($option3, 'doajax', '审核通过');
            }
            // $dostr = create_button($option1, 'dialog', '编辑').create_button($option2, 'dialog', '司机管理').create_button($option3, 'doajax', '删除');
            // // echo $dostr;exit;
            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }
    // 供货信息详情
    public function sellerNewsDetail(){
        $id = I('id');
        $info = M('seller_news')->find($id);
        if ($info) {
            $this->info = $info;
            $this->display();
        } else {
            alert_illegal();
        }

    }
    // 审核供货信息，is_passed还没有加 2017年5月11日18:43:44 zgw zjw说不用审核了
    public function passSellNews(){
        $id = I('id');
        $info = M('seller_news')->find($id);
        if ($info) {
            $res = M('seller_news')->save(array('id' => $id, 'is_passed' => 1));
            if ($res !== false) {
                after_alert(array('tabid' => 'SupplyGoods_sellerNews'));
            } else {
                alert_false();
            }
        } else {
            alert_illegal();
        }
    }

    // 货源信息
    public function getLogisticsGoods(){
        $db = M('logistics_goods');
        $where = array();

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        // if ($name = trim(I('real_name', ''))) {
        //     $where['u.real_name'] = array('like', '%'.$name.'%');
        // }
        // if ($phone = I('phone', '')) {
        //     $where['u.phone'] = $phone;
        // }

        $count = $db
            // ->join('left join coal_company c on c.id = t.owner_id')
            ->where($where)
            ->count();

        $data = $db
            // ->field('t.*, c.name as company_name')
            // ->join('left join coal_company c on c.id = t.owner_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('is_passed asc, create_time desc')
            ->select();
        // sql();
        // dump_text($data);
        foreach ($data as $key => $value) {
            // 出发地
            $data[$key]['sell_address'] = $value['from_province'].$value['from_city'].$value['from_area'];

            // 到达地
            $data[$key]['buy_address'] = $value['to_province'].$value['to_city'].$value['to_area'];

            // 交费
            if ($value['tip_type'] == 1) {
                $tip_type = '信息费';
            } else if ($value['tip_type'] == 2) {
                $tip_type = '保证金';
            }
            $data[$key]['tip'] = $tip_type.':'.$value['tip'].'元';

            // 煤炭种类
            $data[$key]['coal_type'] = coal_type($value['coal_type']);

            // 承运状态
            $state = M('re_goods')->where(array('uid' => $value['id']))->getField('state');
            $data[$key]['state'] = getReGoodsType($state);

            $dostr = '';
            // $option1 = "{
            //     id:'com_truck_edit',
            //     data:{id:'".$value['id']."'},
            //     url:'".U('edit')."'
            //     }";
            $option2 = "{
                id:'company_truck_detail',
                data:{id:'".$value['id']."'},
                url:'".U('logisticsGoodsDetail')."',
                width:'800',
                height:'500',
                }";
            $dostr .= create_button($option2, 'dialog', '详情');
            if (!$value['is_passed']) {
                $option3 = "{
                type:'get',
                data:{id:'".$value['id']."'},
                url:'".U('passLogGoods')."',
                confirmMsg:'确定要审核通过吗？'
                }";
                $dostr .= create_button($option3, 'doajax', '审核通过');
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
    // 货源信息详情
    public function logisticsGoodsDetail(){
        $id = I('id');
        $info = M('logistics_goods')->find($id);
        $info['coal_type'] = M('coal_type')->where(array('id' => $info['coal_type']))->getField('name');
        if ($info['tip_type']==2) {
            $tip_type = '保证金';
        } else if ($info['tip_type']==1) {
            $tip_type = '信息费';
        } else {
            $tip_type = '类型错误';
        }
        $info['tip_type'] = $tip_type;
        $info['price'] .= '元';
        $info['tip'] .= '元';
        $info['quantity'] .= '吨';
        if ($info) {
            $this->info = $info;
            $this->display();
        } else {
            alert_illegal();
        }
    }

    // 货源信息审核
    public function passLogGoods(){
        $id = I('id');
        $info = M('logistics_goods')->find($id);
        if ($info) {
            $res = M('logistics_goods')->save(array('id' => $id, 'is_passed' => 1));
            if ($res !== false) {
                after_alert(array('tabid' => 'SupplyGoods_logisticsGoods'));
            } else {
                alert_false();
            }
        } else {
            alert_illegal();
        }
    }

    // 货源待放空列表
    public function getEmptyTruck(){
        // 拿到state = 5的记录
        $db = M('re_goods r');
        $where = array('r.state' => 5);

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        // if ($name = trim(I('real_name', ''))) {
        //     $where['u.real_name'] = array('like', '%'.$name.'%');
        // }
        // if ($phone = I('phone', '')) {
        //     $where['u.phone'] = $phone;
        // }

        $count = $db
            ->join('left join coal_logistics_goods l on l.id = r.uid')
            ->where($where)
            ->count();

        $data = $db
            ->field('r.id,r.tip,r.create_time,r.end_time,r.state,u.real_name,
            l.from_province,l.from_city,l.from_area,l.to_province,l.to_city,l.to_area,l.coal_type,
            l.price,l.quantity,l.tip_type,l.tip,l.comment,l.click')
            ->join('left join coal_logistics_goods l on l.id = r.uid')
            ->join('left join coal_users u on u.id = r.writer_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('r.create_time desc,r.id desc')
            ->select();
        // dump($data);
        foreach ($data as $key => $value) {
            // 出发地
            $data[$key]['sell_address'] = $value['from_province'].$value['from_city'].$value['from_area'];

            // 到达地
            $data[$key]['buy_address'] = $value['to_province'].$value['to_city'].$value['to_area'];

            // 交费
            if ($value['tip_type'] == 1) {
                $tip_type = '信息费';
            } else if ($value['tip_type'] == 2) {
                $tip_type = '保证金';
            }
            $data[$key]['tip'] = $tip_type.':'.$value['tip'].'元';

            // 煤炭种类
            $data[$key]['coal_type'] = coal_type($value['coal_type']);

            // 承运状态
            $data[$key]['state'] = getReGoodsType($value['state']);

            $dostr = '';
            $option3 = "{
                type:'get',
                data:{id:'".$value['id']."',state:6},
                url:'".U('changeRGoodsState')."',
                confirmMsg:'确定要操作吗？'
                }";
            $dostr .= create_button($option3, 'doajax', '放空通过');
            $option4 = "{
                type:'get',
                data:{id:'".$value['id']."',state:7},
                url:'".U('changeRGoodsState')."',
                confirmMsg:'确定要操作吗？'
                }";
            $dostr .= create_button($option4, 'doajax', '放空拒绝');
            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 放空处理
    public function changeRGoodsState(){
        $id = I('id');
        $state = I('state');
        if (!$id || !in_array($state, array(6,7))) {
            alert_illegal();
        }
        $res = M('re_goods')->save(array('id'=> $id, 'state' => $state));
        show_res($res);
    }
}