<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/4/7
 * Time: 9:56
 */
namespace Vip\Controller;

class CompanyTruckerController extends CommonController {

    // 搜索可合作的车主，搜索出社会车主
    public function searchCompanyTrucker(){
        $db = M('truck t');
        $where = array(
            't.owner_type' => 1,
            't.is_passed'  => 1,
            '_string'  => 'u.id is not null',
        );

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        if ($name = trim(I('real_name', ''))) {
            $where['u.real_name'] = array('like', '%'.$name.'%');
        }
        if ($phone = I('phone', '')) {
            $where['u.phone'] = array('like', '%'.$phone.'%');
        }
        if ($lic_number = I('lic_numbers', '')) {
            $where['t.lic_number'] = array('like', '%'.$lic_number.'%');
        }

        $count_res = $db
            ->join('left join coal_users u on t.owner_id = u.id')
            ->where($where)
            ->group('t.owner_id')
            ->select();
        $count = count($count_res);
        $data = $db
            ->field('u.id,u.real_name,u.phone')
            ->join('left join coal_users u on t.owner_id = u.id')
            ->where($where)
            ->group('t.owner_id')
            ->limit($page_size * $page_num, $page_size)
            ->select();
        // sql();
        // $data_arr=array();
        // $i=0;
        foreach ($data as $key => $val) {
            // 车牌号 和 车主拥有车的数量
            $tmp = M('truck')->where(array('owner_id' => $val['id'], 'owner_type' => 1))->select();
            $data[$key]['num'] = count($tmp);

            $lic_numbers = '';
            foreach ($tmp as $v) {
                $lic_numbers .= $v['lic_number'].',';
            }
            $lic_numbers = rtrim($lic_numbers, ',');
            $data[$key]['lic_numbers'] = $lic_numbers;

            // $tmp_res = M('company_trucker')
            //     ->where(array('company' => session('company_id'), 'trucker_id' => $val['id'], 'status' => array('in', array(1,2,3))))
            //     ->find();
            // if (!$tmp_res) {
            //     $data_arr[$i] = $val;
            //     // 车辆所有lic_number
            //     $lic_numbers = '';
            //     $res1 = M('truck')->where(array('owner_id' => $val['id']))->select();
            //
            //     foreach ($res1 as $k => $v) {
            //         $lic_numbers .= $v['lic_number'].',';
            //     }
            //     $data_arr[$i]['lic_numbers'] = rtrim($lic_numbers, ',');
            //     $i++;
            // }
        }

        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 绑定合作的车主
    public function addCompanyTrucker(){
        if (IS_POST) {
            $trucker_id = I('post.trucker_id');
            $data = array(
                'trucker_id'  => $trucker_id + 0,
                'company'     => session('company_id')
            );
            $where = array(
                'owner_id'     => $trucker_id,
                'owner_type'  => 1,
                'is_passed'  => 1
            );
            //是否通过审核,任一车辆
            $acc = M('truck')->where($where)->find();
            // sql();
            if (!$acc) {
                alert('您的车辆还没有通过审核', 300);
            }
            //查看是否已经合作了
            $info = D('CompanyTrucker')->where($data)->find();
            if ($info) {
                if ($info['status'] == 4 || $info['status'] == 5) {
                    $res = D('CompanyTrucker')->save(array('id' => $info['id'], 'status' => 2));
                    // 通知车主有公司合作申请
                    D('Push', 'Logic')->noticeTruckerCompanyCooperation($data['trucker_id'], $data['company']);

                } else {
                    alert('该车主已与本公司合作', 300);
                }
            } else {
                $res = D('CompanyTrucker')->addData($data);
                // 通知车主有公司合作申请
                D('Push', 'Logic')->noticeTruckerCompanyCooperation($data['trucker_id'], $data['company']);
            }
            show_res($res);
        }
        $this->display();
    }

    // 待确认车主的列表
    public function getNotConfirmTrucker(){
        $db = M('company_trucker t');
        $where = array(
            't.status' => array('in', '2,3'),
            't.company' => session('company_id'),
            'tr.is_passed' => 1,
            'tr.owner_type'  => 1
        );

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        if ($name = trim(I('real_name', ''))) {
            $where['u.real_name'] = array('like', '%'.$name.'%');
        }
        if ($phone = I('phone', '')) {
            $where['u.phone'] = $phone;
        }

        $count = $db
            ->join('left join coal_users u on u.id = t.trucker_id')
            // ->join('left join coal_area a on a.relation_id = t.trucker_id')
            ->join('left join coal_truck tr on tr.owner_id = u.id')
            ->where($where)
            ->group('u.id')
            ->count();

        $data = $db
            ->field('t.*, u.account, u.real_name, u.phone, u.province, u.city, u.area, count(tr.id) num')
            ->join('left join coal_users u on u.id = t.trucker_id')
            // ->join('left join coal_area a on a.relation_id = t.trucker_id')
            ->join('left join coal_truck tr on tr.owner_id = u.id')
            ->where($where)
            ->group('u.id')
            ->limit($page_size * $page_num, $page_size)
            ->order('t.codate desc')
            ->select();
        // sql();
        // dump_text($data);
        foreach ($data as $key => $value) {
            $data[$key] = $value;

            // 合作表的状态表述
            switch ($value['status']) {
                case 2:
                    $msg = '待确认';
                    break;
                case 3:
                    $msg = '车主已拒绝';
                    break;
                default:
                    $msg = '未知错误';
                    break;
            }
            $data[$key]['status'] = $msg;

            $option2 = "{
                type:'get',
                data:{id:'".$value['id']."'},
                url:'".U('del')."',
                confirmMsg:'确定要删除吗？'
                }";
            $dostr = create_button($option2, 'doajax', '删除合作');
            // echo $dostr;exit;
            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 删除合作记录
    public function del(){
        $id = I('id');
        // 合作之前的删除
        $data = M('company_trucker')->where(array('id' => $id, 'status' => array('in', array(2,3))))->find();
        $res = M('company_trucker')->where(array('id' => $id, 'status' => array('in', array(2,3))))->delete();
        //将车主拉剔除公司群
        vendor('Emchat.Easemobclass');
        $group_id=M('company')->where(array('id'=>session('company_id')))->getField('group_id');
        $h=new \Easemob();
        $h->deleteGroupMember($group_id,$data['trucker_id']);
        show_res($res);
    }

    // 合作中的个人车主
    public function getCooTrucker(){
        $db = M('company_trucker t');
        $where = array(
            't.status' => 1,
            't.company' => session('company_id'),
            'tr.is_passed' => 1,
            'tr.owner_type'  => 1,
            // 'tr.anchored_id'  => session('company_id'),
        );

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 10);

        if ($name = trim(I('real_name', ''))) {
            $where['u.real_name'] = array('like', '%'.$name.'%');
        }
        if ($phone = I('phone', '')) {
            $where['u.phone'] = $phone;
        }

        // $count = $db
        //     ->join('left join coal_users u on u.id = t.trucker_id')
        //     ->join('left join coal_truck tr on tr.owner_id = u.id and tr.owner_type = 1')
        //     ->where($where)
        //     ->group('u.id')
        //     ->count();
        // sql();
        $data = $db
            ->field('t.*, u.account, u.real_name, u.phone')
            ->join('left join coal_users u on u.id = t.trucker_id')
            ->join('left join coal_truck tr on tr.owner_id = u.id and tr.owner_type = 1')
            ->where($where)
            ->group('u.id')
            ->limit($page_size * $page_num, $page_size)
            ->select();
        // sql();
        // dump_text($data);
        $count = 0;
        foreach ($data as $key => $value) {
            // 获取公司合作车主合作中的车辆
            $data[$key]['num'] = D('Company', 'Logic')->getCooTruckNum($value['trucker_id']);

            // 生成操作链接
            $option1 = "{
                id:'coo_trucker_detail',
                url:'".U('cooTrucks')."',
                data:{trucker_id:'".$value['trucker_id']."'},
                type:'get',
                fresh:true,
                height:'600',
                width:'800'
            }";
            $option2 = "{
                type:'post',
                data:{id:'".$value['id']."', status:'4', owner_id:'".$value['trucker_id']."'},
                url:'".U('delCoo')."',
                confirmMsg:'确定要解除合作吗？'
                }";
            $dostr = create_button($option1, 'dialog', '详情') . create_button($option2, 'doajax', '解除合作');
            // echo $dostr;exit;
            $data[$key]['dostr'] = $dostr;
            $count++;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 某个合作主车的车辆详情页面
    public function cooTrucks(){
        // 根据车主id得到车主的个人信息
        $trucker_id = I('trucker_id', 0);
        $this->tid = $trucker_id;
        if ($trucker_id) {
            $this->info = M('users u')
                ->field('u.id, u.real_name, u.phone, u.create_time, a.detail')
                ->join('left join coal_area a on a.relation_id = u.id and a.is_company = 0')
                ->where('u.id = ' . $trucker_id)
                ->find();
        }
        $this->display();
    }

    // 获取某个合作主车的车辆详情
    public function getCooTrucks(){
        $trucker_id = I('get.trucker_id');
        // dump_text($trucker_id);
        $db = M('truck t'); 
        $where = array(
            't.owner_type' => 1, // 个人的
            't.is_passed'  => 1, // 审核过的
            't.owner_id'   =>  $trucker_id,
        );
        $coo_state = I('get.coo_state');
        if ($coo_state == 1) {
            // 已合作车辆
            $where['t.anchored_id']  = session('company_id');
            $where['t.is_anchored']   = 1;
        } else {
            // 未合作车辆
            $where['t.anchored_id']   = 0;
            $where['t.is_anchored']   = 0;
            $where['t.is_comperation']   = 0;
        }
        $page_num  = I('pageCurrent', 1) - 1;
        $page_size = I('pageSize', 2);

        // if ($name = trim(I('real_name', ''))) {
        //     $where['u.real_name'] = array('like', '%'.$name.'%');
        // }
        // if ($phone = I('phone', '')) {
        //     $where['u.phone'] = $phone;
        // }

        $count = $db
            // ->join('left join coal_users u on u.id = t.trucker_id')
            // ->join('left join coal_area a on a.relation_id = t.trucker_id')
            ->where($where)
            // ->group('u.id')
            ->count();

        $data = $db
            // ->field('t.*, u.account, u.real_name, u.phone, a.province, a.city, a.area, count(tr.id) num')
            // ->join('left join coal_users u on u.id = t.trucker_id')
            // ->join('left join coal_area a on a.relation_id = t.trucker_id')
            ->where($where)
            // ->group('u.id')
            ->limit($page_size * $page_num, $page_size)
            ->select();
        // sql();
        // dump_text($data);
        foreach ($data as $key => $value) {

            // 得到车绑定的司机
            $data[$key]['driver'] = get_drivers($value['id']);

            // 得到车的状态
            $data[$key]['state'] = getTruckType($value['state']);

            // 得到行驶证照片
            $data[$key]['lic_pic'] = '<a href="'.getTrueImgSrc($value['lic_pic']).'" target="_blank"><img src="'.getTrueImgSrc($value['lic_pic'],true,60,100).'"/></a>';

            $dostr = '';
            if (!($value['state'] == 2 || $value['state'] == 3)) {
                // 未合作的车辆要接收按钮
                if ($coo_state == 0) {
                    if (strtotime($value['ins_date']) < time() || strtotime($value['check_date']) < time()) {
                        $option1 = "{
                        id:'takeover_before',
                        url:'".U('takeOverBefore')."',
                        data:{id:'".$value['id']."'},
                        }";
                        $button_type = 'dialog';
                    } else {
                        $option1 = "{
                        type:'post',
                        data:{id:'".$value['id']."'},
                        url:'".U('takeOver')."',
                        confirmMsg:'确定要接收吗？'
                        }";
                        $button_type = 'doajax';
                    }
                    $dostr = create_button($option1, $button_type, '接收') . $dostr;
                } else {
                    $option2 = "{
                    type:'post',
                    data:{id:'".$value['id']."'},
                    url:'".U('removeTakeOver')."',
                    confirmMsg:'确定要移除车辆吗？'
                    }";
                    $dostr = create_button($option2, 'doajax', '移除车辆');
                }
            }
            // echo $dostr;exit;
            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 公司解除与车主的合作
    public function delCoo(){
        if (IS_POST) {
            $id = I('post.id');
            $status = I('post.status');
            $owner_id = I('post.owner_id');
            // 看有没有接收的车辆
            $info = M('truck')->where(array('owner_id' => $owner_id, 'anchored_id' => session('company_id')))->find();
            if ($info) {
                alert('车主还有车辆在接收状态中，请在详情中移除车辆', 300);
            }
            $data = array(
                'id'      => $id,
                'status'  => $status,
            );
            $res = D('CompanyTrucker')->save($data);
            doLog('公司'.session('company_id').'解除了与车主'.$owner_id.'合作');
            // 通知车主有公司解除合作
            D('Push', 'Logic')->noticeTruckerCompanyBreakCooperation($owner_id, session('company_id'));
            //将车主拉剔除公司群
            vendor('Emchat.Easemobclass');
            $group_id=M('company')->where(array('id'=>session('company_id')))->getField('group_id');
            $h=new \Easemob();
            $rr=$h->deleteGroupMember($group_id,$owner_id);
            show_res($res);
        } else {
            alert_false();
        }
    }

    // 接收车辆
    public function takeOver(){
        if (IS_POST) {
            $truck_id = I('post.id'); // 车辆id
            // 验证可以接收的车辆
            $info = D('Company', 'Logic')->isCanTakeOver($truck_id);
            if ($info['code']) {
                $res = D('Company', 'Logic')->takeOver($truck_id);
                if ($res !== false) {
                    $truck = M('truck')->where(array('id' => $truck_id))->find();
                    D('Push', 'Logic')->noticeTrucker('[煤问题]接收车辆成功',getCompanyName(session('company_id')).'接收了您的车辆'.$truck['lic_number'], $truck['owner_id']);
                    after_alert(array('closeCurrent' => true));
                } else {
                    alert_false('接收失败');
                }
            } else {
                alert_false($info['message']);
            }
        } else {
            alert_illegal();
        }
    }

    // 接收之前看保险日期
    public function takeOverBefore(){
        if (IS_POST) {
            $truck_id = I('post.id'); // 车辆id
            // 年检日期和保险日期要大于当前时间（只有其中一个时，验证还没兼容）
            // 这里先只考虑都没有的
            $ins_date = I('post.ins_date');
            $check_date = I('post.check_date');
            // 2017年7月17日12:51:48 zgw 运营说不方便获取，教车队乱填。妓总同意改为选填项。
            // if (!($ins_date  && $check_date && strtotime($ins_date) > time() && strtotime($check_date) > time())) {
            //     alert('日期填写有误', 300);
            // }

            // 验证可以接收的车辆
            $info = D('Company', 'Logic')->isCanTakeOver($truck_id);
            if ($info['code']) {
                M()->startTrans();
                $res = D('Company', 'Logic')->takeOver($truck_id);
                $res1 = M('truck')->save(array('id' => $truck_id, 'ins_date' => $ins_date, 'check_date' => $check_date));
                if ($res !== false && $res1 !== false) {
                    M()->commit();
                    $truck = M('truck')->where(array('id' => $truck_id))->find();
                    D('Push', 'Logic')->noticeTrucker('[煤问题]接收车辆成功',getCompanyName(session('company_id')).'接收了您的车辆'.$truck['lic_number'], $truck['owner_id']);
                    after_alert(array('closeCurrent' => true, 'dialogid' => 'coo_trucker_detail', 'tabid' => 'CompanyTrucker_cooTrucker'));
                } else {
                    M()->rollback();
                    alert_false();
                }
            } else {
                alert_false($info['message']);
            }
        }
        $truck_id = I('id');
        $this->info = M('truck')->field('id, lic_number, ins_date, check_date')->find($truck_id);
        $this->display();
    }

    // 移除接收的车辆
    public function removeTakeOver(){
        if (IS_POST) {
            $truck_id = I('post.id'); // 车辆id
            // 验证可以移除的车辆
            $where = array(
                'id' => $truck_id,
                'is_passed' => 1,
                'anchored_id' => session('company_id'),
                'state' => array('not in', array(2,3)),
            );
            $info = M('truck')->where($where)->find();
            // sql();
            if ($info) {
                $data = array(
                    'id'      => $truck_id,
                    'anchored_id'     => 0,
                    'user_id'         => 0,
                    'is_anchored'     => 0,
                    'is_comperation'  => 0,
                );
                $res = D('Truck')->save($data);
                if ($res !== false) {
                    //把司机拉进公司群
                    vendor('Emchat.Easemobclass');
                    $group_id=M('company')->where(array('id'=>session('company_id')))->getField('group_id');
                    $driver_id=M('driver')->where(array('truck_id'=>$truck_id))->field('uid')->select();
                    $h=new \Easemob();
                    foreach ($driver_id as $value){
                        $h->deleteGroupMember($group_id,$value['uid']);
                    }
                    // 移除通知
                    $truck = M('truck')->where(array('id' => $truck_id))->find();
                    D('Push', 'Logic')->noticeTrucker('车辆移除通知', getCompanyName(session('company_id')).'移除了您的车辆'.$truck['lic_number'].',请知悉', $truck['owner_id']);
                    after_alert(array('closeCurrent' => true));
                } else {
                    alert_false();
                }
            } else {
                alert_false();
            }
        } else {
            alert_false();
        }
    }
    //待核算列表页面
    function listCheck(){
        $trucker_id=M('company_trucker')->where(array('company'=>session('company_id'),'status'=>1))->select();
        $i=0;
        //计算车主完成的车次和金额
        foreach ($trucker_id as $key =>$item) {
            $bill=D('Bill',"Logic")->history($item['trucker_id'],null,null,null,null,null,null,session('company_id'),null,null);
            foreach ($bill as $k=>$v){
                $bill[$k]=M('bill')->where(array('id'=>$v['id']))->find();
            }
            //计算金额
            if(count($bill)){
                $trucker[$i]['name']=M('users')->where(array('id'=>$item['trucker_id']))->getField('real_name');
                $acc=M('users')->where(array('id'=>$item['trucker_id']))->field('phone')->find();
                $trucker[$i]['phone']=$acc['phone'];
                $trucker[$i]['total_times']=count($bill);
                foreach ($bill as $kk =>$vv){
                    //查找单价
                    $price=M('information')->where(array('buyer_id'=>$vv['buyer'],'seller_id'=>$vv['seller'],'company_id'=>session('company_id')))->find();
                    if(!$price){
                        $buyer=M("company")->where(array('id'=>$vv['buyer']))->getField('name');
                        $seller=M("company")->where(array('id'=>$vv['seller']))->getField('name');
                        alert($seller."'->'".$buyer."'线路没有添加无法完成核算",300);
                    }
                }
            }
        }
        $this->display();
    }
    //待核算列表数据
    function getCheck(){
        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);
        //查找跟我合作的车主
        $trucker_id=M('company_trucker')->where(array('company'=>session('company_id'),'status'=>1))->select();
        $i=0;
        //计算车主完成的车次和金额
        foreach ($trucker_id as $key =>$item) {
            $bill=D('Bill',"Logic")->history($item['trucker_id'],null,null,null,null,null,null,session('company_id'),null,null);
            $j=0;
            $rbill=array();
            foreach ($bill as $v){
                $bill_one=M('bill')->where(array('id'=>$v['id']))->find();
                if($bill_one['state']==6 and $bill_one['check_state']==0){
                    $rbill[$j]=$bill_one;
                    $j++;
                }
            }
            //计算金额
            if(count($rbill)){
                $trucker[$i]['name']=M('users')->where(array('id'=>$item['trucker_id']))->getField('real_name');
                $acc=M('users')->where(array('id'=>$item['trucker_id']))->field('phone')->find();
                $trucker[$i]['phone']=$acc['phone'];
                $trucker[$i]['total_times']=count($bill);
                foreach ($rbill as $k =>$vv){
                    //查找单价
                    $price=M('information')->where(array('buyer_id'=>$vv['buyer'],'seller_id'=>$vv['seller'],'company_id'=>session('company_id')))->find();
                    //损耗吨数去零
                    $sunhao_w=$vv['begin_w']-$vv['end_w']>0?$vv['begin_w']-$vv['end_w']:0;
                    //利用让损方式获取最终的损耗吨数用来计算
                    switch ($price['debit_mode']){
                        case 0:break;//一分不减
                        case 1:$sunhao_w=$sunhao_w-$price['debit_w']>=0?$sunhao_w-$price['debit_w']:0;break;
                        case 2:$sunhao_w=$sunhao_w-$price['debit_w']>=0?$sunhao_w-$price['debit_w']:$sunhao_w;break;
                    }
                    $final_price=$price['price']*$vv['end_w']-$price['oli_price']-$price['road_toll']-$sunhao_w*$price['debit'];
                    $trucker[$i]['total_money']=$trucker[$i]['total_money']+$final_price;
                    //总吨数
                    $trucker[$i]['total_w']=$trucker[$i]['total_w']+$vv['end_w'];
                    $trucker[$i]['trucker_id']=$item['trucker_id'];
                }
                $i++;
            }
        }
        foreach ($trucker as $key =>$value){
            $option1= "{
                   type:'get',
                   data:{id:'".$value['trucker_id']."'},
                   url:'".U('doCheck')."',
                   confirmMsg:'确定要进行核算吗？'
                   }";
            $option2 = "{
                id:'company_information_edit',
                type:'get',
                data:{id:'".$value['trucker_id']."'},
                url:'".U('checkDetail')."',
                width:'1000',
                height:'600',
                }";
            $trucker[$key]['dostr'] = create_button($option2, 'dialog', '详情').create_button($option1,'doajax','确认核算');
        }
        $count=$i;
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $trucker,
        );
        echo json_encode($info);
    }
    //待核算详情
    function checkDetail(){
        $this->id = I('id');
        $this->display();
    }
    //待核算详情
    function getDetail(){
        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);
        $bills=D('Bill','Logic')->history(I('get.id'),null,null,null,null,null,null,session('company_id'),null,null);
        $rbill=array();
        $i=0;
        foreach ($bills as $key=>$value){
            $bill_one=M('bill')->where(array('id'=>$value['id']))->find();
            if($bill_one['state']==6 and $bill_one['check_state']==0){
                $rbill[$i]=$bill_one;
                $rbill[$i]['seller']=M('company')->where(array('id'=>$bill_one['seller']))->getField('name');
                $rbill[$i]['buyer']=M('company')->where(array('id'=>$bill_one['buyer']))->getField('name');
                $rbill[$i]['sunhao']=$bill_one['begin_w']-$bill_one['end_w']>0?$bill_one['begin_w']-$bill_one['end_w']:0;
                //计算运费
                $price=M('information')->where(array('company_id'=>session('company_id'),'buyer_id'=>$bill_one['buyer'],'seller_id'=>$bill_one['seller']))->find();
                $sunhao_w=$bill_one['begin_w']-$bill_one['end_w']>0?$bill_one['begin_w']-$bill_one['end_w']:0;
                //利用让损方式获取最终的损耗吨数用来计算
                switch ($price['debit_mode']){
                    case 0:break;//一分不减
                    case 1:$sunhao_w=$sunhao_w-$price['debit_w']>=0?$sunhao_w-$price['debit_w']:0;break;
                    case 2:$sunhao_w=$sunhao_w-$price['debit_w']>=0?$sunhao_w-$price['debit_w']:$sunhao_w;break;
                }
                $final_price=$price['price']*$bill_one['end_w']-$price['oli_price']-$price['road_toll']-$sunhao_w*$price['debit'];
                $rbill[$i]['sunhao']=$sunhao_w;
                $rbill[$i]['money']=$final_price;
                $i++;
            }
        }
        //做分页
        $data=array();
        $n=0;
        for($j=$page_num*$page_size;$j<$page_size*($page_num+1);$j++){
            $data[$n]=$rbill[$j];
            $n++;
        }
        $count=$i;
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }
    //处理核算
    function doCheck(){
        if(!I('id')){
            alert('提交信息不完整',300);
        }
        $bills=D('Bill','Logic')->history(I('get.id'),null,null,null,null,null,null,session('company_id'),null,null);
        $rbill=array();
        $i=0;
        foreach ($bills as $key=>$value){
            $bill_one=M('bill')->where(array('id'=>$value['id']))->find();
            if($bill_one['state']==6 and $bill_one['check_state']==0){
                $rbill[$i]=$bill_one;
                $rbill[$i]['seller']=M('company')->where(array('id'=>$bill_one['seller']))->getField('name');
                $rbill[$i]['buyer']=M('company')->where(array('id'=>$bill_one['buyer']))->getField('name');
                $rbill[$i]['sunhao']=$bill_one['begin_w']-$bill_one['end_w']>0?$bill_one['begin_w']-$bill_one['end_w']:0;
                //计算运费
                $price=M('information')->where(array('company_id'=>session('company_id'),'buyer_id'=>$bill_one['buyer'],'seller_id'=>$bill_one['seller']))->find();
                $sunhao_w=$bill_one['begin_w']-$bill_one['end_w']>0?$bill_one['begin_w']-$bill_one['end_w']:0;
                //利用让损方式获取最终的损耗吨数用来计算
                switch ($price['debit_mode']){
                    case 0:break;//一分不减
                    case 1:$sunhao_w=$sunhao_w-$price['debit_w']>=0?$sunhao_w-$price['debit_w']:0;break;
                    case 2:$sunhao_w=$sunhao_w-$price['debit_w']>=0?$sunhao_w-$price['debit_w']:$sunhao_w;break;
                }
                $final_price=$price['price']*$bill_one['end_w']-$price['oli_price']-$price['road_toll']-$sunhao_w*$price['debit'];
                $rbill[$i]['sunhao']=$sunhao_w;
                $rbill[$i]['money']=$final_price;
                $data['check_money']=$final_price;
                $data['check_state']=1;
                $data['check_date']=date('Y-m-d');
                M('bill')->where(array('id'=>$value['id']))->save($data);
                $i++;
            }
        }

        alert('核算成功',200);
    }
    //已核算列表页面
    function listChecked(){
        $trucker_id=M('company_trucker')->where(array('company'=>session('company_id'),'status'=>1))->select();
        $i=0;
        //计算车主完成的车次和金额
        foreach ($trucker_id as $key =>$item) {
            $bill=D('Bill',"Logic")->history($item['trucker_id'],null,null,null,null,null,null,session('company_id'),null,null);
            foreach ($bill as $k=>$v){
                $bill[$k]=M('bill')->where(array('id'=>$v['id']))->find();
            }
            //计算金额
            if(count($bill)){
                $trucker[$i]['name']=M('users')->where(array('id'=>$item['trucker_id']))->getField('real_name');
                $acc=M('users')->where(array('id'=>$item['trucker_id']))->field('phone')->find();
                $trucker[$i]['phone']=$acc['phone'];
                $trucker[$i]['total_times']=count($bill);
                foreach ($bill as $kk =>$vv){
                    //查找单价
                    $price=M('information')->where(array('buyer_id'=>$vv['buyer'],'seller_id'=>$vv['seller'],'company_id'=>session('company_id')))->find();
                    if(!$price){
                        $buyer=M("company")->where(array('id'=>$vv['buyer']))->getField('name');
                        $seller=M("company")->where(array('id'=>$vv['seller']))->getField('name');
                        alert($seller."'->'".$buyer."'线路没有添加无法完成核算,请到“线路管理”->“添加路线”进行添加",300);
                    }
                }
            }
        }
        $this->display();
    }
    //已核算列表数据
    function getChecked(){
        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);
        //查找跟我合作的车主
        $trucker_id=M('company_trucker')->where(array('company'=>session('company_id'),'status'=>1))->select();
        $i=0;
        //计算车主完成的车次和金额
        foreach ($trucker_id as $key =>$item) {
            $bill=D('Bill',"Logic")->history($item['trucker_id'],null,null,null,null,null,null,session('company_id'),null,null);
            $j=0;
            $rbill=array();
            foreach ($bill as $v){
                $bill_one=M('bill')->where(array('id'=>$v['id']))->find();
                if($bill_one['state']==6 and $bill_one['check_state']==1){
                    $rbill[$j]=$bill_one;
                    $j++;
                }
            }
            //计算金额
            if(count($rbill)){
                $trucker[$i]['name']=M('users')->where(array('id'=>$item['trucker_id']))->getField('real_name');
                $acc=M('users')->where(array('id'=>$item['trucker_id']))->field('phone')->find();
                $trucker[$i]['phone']=$acc['phone'];
                $trucker[$i]['total_times']=count($bill);
                foreach ($rbill as $k =>$vv){
                    //查找单价
                    $price=M('information')->where(array('buyer_id'=>$vv['buyer'],'seller_id'=>$vv['seller'],'company_id'=>session('company_id')))->find();
                    //损耗吨数去零
                    $sunhao_w=$vv['begin_w']-$vv['end_w']>0?$vv['begin_w']-$vv['end_w']:0;
                    //利用让损方式获取最终的损耗吨数用来计算
                    switch ($price['debit_mode']){
                        case 0:break;//一分不减
                        case 1:$sunhao_w=$sunhao_w-$price['debit_w']>=0?$sunhao_w-$price['debit_w']:0;break;
                        case 2:$sunhao_w=$sunhao_w-$price['debit_w']>=0?$sunhao_w-$price['debit_w']:$sunhao_w;break;
                    }
                    $final_price=$price['price']*$vv['end_w']-$price['oli_price']-$price['road_toll']-$sunhao_w*$price['debit'];
                    $trucker[$i]['total_money']=$trucker[$i]['total_money']+$final_price;
                    //总吨数
                    $trucker[$i]['total_w']=$trucker[$i]['total_w']+$vv['end_w'];
                    $trucker[$i]['trucker_id']=$item['trucker_id'];
                }
                $i++;
            }
        }
        foreach ($trucker as $key =>$value){

            $option2 = "{
                id:'company_information_edit',
                type:'get',
                data:{id:'".$value['trucker_id']."'},
                url:'".U('checkedDetail')."',
                width:'1000',
                height:'600',
                }";
            $trucker[$key]['dostr'] = create_button($option2, 'dialog', '详情');
        }
        $count=$i;
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $trucker,
        );
        echo json_encode($info);
    }
    //已经核算详情
    function checkedDetail(){
        $this->id = I('id');
        $this->display();
    }
    //ajax已经待核算详情
    function getDetailed(){
        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);
        $bills=D('Bill','Logic')->history(I('get.id'),null,null,null,null,null,null,session('company_id'),null,null);
        $rbill=array();
        $i=0;
        foreach ($bills as $key=>$value){
            $bill_one=M('bill')->where(array('id'=>$value['id']))->find();
            if($bill_one['state']==6 and $bill_one['check_state']==1){
                $rbill[$i]=$bill_one;
                $rbill[$i]['seller']=M('company')->where(array('id'=>$bill_one['seller']))->getField('name');
                $rbill[$i]['buyer']=M('company')->where(array('id'=>$bill_one['buyer']))->getField('name');
                $rbill[$i]['sunhao']=$bill_one['begin_w']-$bill_one['end_w']>0?$bill_one['begin_w']-$bill_one['end_w']:0;
                //计算运费
                $price=M('information')->where(array('company_id'=>session('company_id'),'buyer_id'=>$bill_one['buyer'],'seller_id'=>$bill_one['seller']))->find();
                $sunhao_w=$bill_one['begin_w']-$bill_one['end_w']>0?$bill_one['begin_w']-$bill_one['end_w']:0;
                //利用让损方式获取最终的损耗吨数用来计算
                switch ($price['debit_mode']){
                    case 0:break;//一分不减
                    case 1:$sunhao_w=$sunhao_w-$price['debit_w']>=0?$sunhao_w-$price['debit_w']:0;break;
                    case 2:$sunhao_w=$sunhao_w-$price['debit_w']>=0?$sunhao_w-$price['debit_w']:$sunhao_w;break;
                }
                $final_price=$price['price']*$bill_one['end_w']-$price['oli_price']-$price['road_toll']-$sunhao_w*$price['debit'];
                $rbill[$i]['sunhao']=$sunhao_w;
                $rbill[$i]['money']=$final_price;
                $i++;
            }
        }
        //做分页
        $data=array();
        $n=0;
        for($j=$page_num*$page_size;$j<$page_size*($page_num+1);$j++){
            $data[$n]=$rbill[$j];
            $n++;
        }
        $count=$i;
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }
}