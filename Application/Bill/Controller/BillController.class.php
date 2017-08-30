<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/4/17
 * Time: 22:16
 */
namespace Bill\Controller;
use Think\Controller;
class BillController extends Controller {
    // 伊泰已完成
    public function yitai_finished(){
        $db = M('bill b');
        $where = array();
        $where['b.state'] = 6;
        $where['b.create_type'] = 1;
        $where['b.company'] = session('company_id');

        // 搜索条件
        // 1、车牌号
        $lic_number = '';
        if ($lic_number = I('lic_number')) {
            $where['t.lic_number'] = array('like', '%'.$lic_number.'%');
        }
        $this->lic_number = $lic_number;
        // 2、车小号
        $owner_order = '';
        if ($owner_order = I('owner_order')) {
            $where['t.owner_order'] = array('like', '%'.$owner_order.'%');
        }
        $this->owner_order = $owner_order;
        // 3、时间
        // 时间规则，默认是当天的。如果可以是某天的。可以是某几天的。
        $start_time = date('Y-m-d');
        $end_time = date('Y-m-d');
        // $start_time = '2017-06-30';
        // $end_time = '2017-06-30';
        if (I('start_time') && I('end_time')) {
            $start_time = I('start_time');
            $end_time = I('end_time');
        }
        $this->start_time = $start_time;
        $this->end_time = $end_time;
        if (strtotime($start_time) > strtotime($end_time)) {
            $this->error('开始日期不能大于结束日期');
        }
        $where['y.end_time'] = array('between', array(yitai_time($start_time.' 00:00:00',0), yitai_time($end_time.' 23:59:59',0)));

        // 4、卖家
        $seller = '';
        if ($seller = I('seller')) {
            $where['b.seller'] = $seller;
        }
        $this->seller = $seller;
        // 5、买家
        $buyer = '';
        if ($buyer = I('buyer')) {
            $where['b.buyer'] = $buyer;
        }
        $this->buyer = $buyer;

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
        // sql();
        $end_w_count = $db
            ->join('left join coal_driver dr on dr.id = b.driver_id')
            ->join('left join coal_users u on u.id = b.driver_id')
            ->join('left join coal_users u1 on u1.id = b.trucker_id')
            ->join('left join coal_company a on a.id = b.buyer')
            ->join('left join coal_company c on c.id = b.seller')
            ->join('left join coal_truck t on t.id = b.truck_id')
            ->join('left join coal_link_yitai y on y.bill_id = b.id')
            ->where($where)
            ->sum('b.end_w');
        $end_w_count = $end_w_count + 0;
        $Page       = new \Think\Page($count,8);// 实例化分页类 传入总记录数和每页显示的记录数
        //分页跳转的时候保证查询条件

        $Page->setConfig('header','共 <span class="red">%TOTAL_ROW%</span> 车，共 <span class="red">'.$end_w_count.'</span> 吨');
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','第一页');
        $Page->setConfig('last','最后一页');
        $Page->rollPage=5;
        $Page->lastSuffix=false;
        $Page->setConfig('theme',"%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%");
        $show       = $Page->show();// 分页显示输出
        $data = $db
            ->field('b.id, b.buyer, b.seller, b.do_time, b.end_w, b.begin_w, b.arrange_w, y.coal_type,
            u.real_name as driver_name, u1.real_name as trucker_name, u.phone as driver_phone,
            a.name as buyer_name, c.name as seller_name,
            t.lic_number, t.owner_order,
            y.bill_number,y.dis_time,y.begin_time,y.end_time')
            ->join('left join coal_driver dr on dr.id = b.driver_id')
            ->join('left join coal_users u on u.id = b.driver_id')
            ->join('left join coal_users u1 on u1.id = b.trucker_id')
            ->join('left join coal_company a on a.id = b.buyer')
            ->join('left join coal_company c on c.id = b.seller')
            ->join('left join coal_truck t on t.id = b.truck_id')
            ->join('left join coal_link_yitai y on y.bill_id = b.id')
            ->where($where)
            ->limit($Page->firstRow.','.$Page->listRows)
            ->order('y.end_time asc')
            ->select();
        // $end_w_count = 0;
        // 获得卖家买家
        $sellers = M('bill b')
            ->field('b.seller as id, c.name')
            ->join('left join coal_link_yitai y on y.bill_id = b.id')
            ->join('left join coal_company c on c.id = b.seller')
            ->where(array('b.state' => 6, 'b.create_type' => 1, 'b.company' => session('company_id')))
            ->group('seller')->select();
        $buyers = M('bill b')
            ->field('b.buyer as id, c.name')
            ->join('left join coal_link_yitai y on y.bill_id = b.id')
            ->join('left join coal_company c on c.id = b.buyer')
            ->where(array('b.state' => 6, 'b.create_type' => 1, 'b.company' => session('company_id')))
            ->group('buyer')->select();

        foreach ($data as $key => $value) {

            // // 卖家买家
            // if (!isset($sellers[$value['seller']])) {
            //     $sellers[$value['seller']] = M('company')->where(array('id' => $value['seller']))->getField('name');
            // }
            // if (!isset($buyers[$value['buyer']])) {
            //     $buyers[$value['buyer']] = M('company')->where(array('id' => $value['buyer']))->getField('name');
            // }

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

            // 派单时间
            if ($value['dis_time']) {
                $data[$key]['dis_time'] = yitai_time($value['dis_time']);
            } else {
                $data[$key]['dis_time'] = '<span style="color: red">没有提交</span>';
            }
            // 接单时间
            if ($value['do_time']) {
                $data[$key]['do_time'] = yitai_time($value['do_time']);
            } else {
                $data[$key]['do_time'] = '<span style="color: red">没有提交</span>';
            }
            // 拉煤时间
            if ($value['begin_time']) {
                $data[$key]['begin_time'] = yitai_time($value['begin_time']);
            } else {
                $data[$key]['begin_time'] = '<span style="color: red">没有提交</span>';
            }
            // 卸货时间
            if ($value['end_time']) {
                $data[$key]['end_time'] = yitai_time($value['end_time']);
            } else {
                $data[$key]['end_time'] = '<span style="color: red">没有提交</span>';
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
            // $end_w_count += $value['end_w']+0;
        }
        $data1 = array(
            'count' => $count,
            'start' => $Page->firstRow,
            'show' => $show,
            'data' => $data,
            'sellers' => $sellers,
            'buyers' => $buyers,
        );
        $this->assign($data1);
        $this->display();
    }

    // 伊泰进行中
    public function yitai_doing(){

    }

    // 伊泰待接单

    // 伊泰退单记录


}