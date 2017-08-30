<?php
/**
 * 合作公司控制器
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/5/8
 * Time: 11:56
 */
namespace Vip\Controller;

class ComperationCompanyController extends CommonController {

    // 搜索可合作的公司
    public function searchComperationCompany(){
        $db = M('company c');
        $where = array(
            // 't.owner_type' => 1,
            'c.is_passed'  => 1,
            'c.id' => array('neq', session('company_id'))
        );

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);
        if ($name = trim(I('company_name', ''))) {
            $where['c.name'] = array('like', '%'.$name.'%');
        }
        if ($phone = I('phone', '')) {
            $where['u.phone'] = array('like', '%'.$phone.'%');
        }

        $count = $db
            ->join('left join coal_users u on u.company_id = c.id and u.is_admin = 1')
            ->where($where)
            // ->group('u.id')
            ->count();

        $data = $db
            ->field('c.id,c.name,c.is_vip,c.lic_number,u.phone')
            ->join('left join coal_users u on u.company_id = c.id and u.is_admin = 1')
            ->where($where)
            // ->group('u.id')
            ->limit($page_size * $page_num, $page_size)
            ->select();
        //     sql();
        foreach ($data as $key => $val) {
            // 公司显示V
            $data[$key]['company_name'] = show_vip_icon($val['name'], $val['is_vip']);


            // $tmp_res = M('company_trucker')->where(array('company' => session('company_id'), 'trucker_id' => $val['id'], 'status' => array('in', array(1,2,3))))->find();
            // if ($tmp_res) {
            //     unset($data[$key]);
            // }
        }
        // dump_text($data);
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 绑定合作公司，添加合作公司
    public function add(){
        if (IS_POST) {
            $company_id = I('post.company_id');
            $data = array(
                'company1'   => session('company_id'),
                'company2'   => $company_id,
                'source'     => session('company_id')
            );
            // 公司是否可以合作
            $is_can_comperation = D('CompanyCompany')->isCanComperation($company_id);
            if ($is_can_comperation['code'] == 0) {
                alert_false($is_can_comperation['message']);
            }
            if ($is_can_comperation['info']) {
                // dump($is_can_comperation['info']);exit;
                if ($is_can_comperation['info'][0]['status'] == 4 || $is_can_comperation['info'][0]['status'] == 5 || $is_can_comperation['info'][0]['status'] == 6) {
                    $res = D('CompanyCompany')->save(array('id' => $is_can_comperation['info'][0]['id'], 'source' => session('company_id'), 'status' => 2));
                    // 通知合作公司管理员有公司合作申请
                    D('Push', 'Logic')->noticeManager('公司合作申请',getCompanyName(session('company_id')).'向您的公司发起合作，请相关人员登录后台处理', $company_id);
                } else {
                    alert('该公司已与本公司合作', 300);
                }
            } else {
                $res = D('CompanyCompany')->addData($data);
                // 通知合作公司管理员有公司合作申请
                D('Push', 'Logic')->noticeManager('公司合作申请',getCompanyName(session('company_id')).'向您的公司发起合作，请相关人员登录后台处理', $company_id);
            }
            show_res($res);
        }
        $this->display();
    }

    // 待确认合作的列表
    public function getNotConfirmCompany(){
        $db = M('company_company cc');
        $where = array(
            'cc.status'   => array('in', '2,3'),
            '_string' => 'cc.company1 = '.session('company_id').' or cc.company2 = '.session('company_id'),
        );

        $page_num  = I('pageCurrent',1) - 1;
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
            // ->join('left join coal_truck tr on tr.owner_id = u.id')
            ->where($where)
            // ->group('u.id')
            ->count();

        $data = $db
            // ->field('t.*, u.account, u.real_name, u.phone, a.province, a.city, a.area, count(tr.id) num')
            // ->join('left join coal_users u on u.id = t.trucker_id')
            // ->join('left join coal_area a on a.relation_id = t.trucker_id')
            // ->join('left join coal_truck tr on tr.owner_id = u.id')
            ->where($where)
            // ->group('u.id')
            ->limit($page_size * $page_num, $page_size)
            ->order('cc.codate desc')
            ->select();
        // sql();
        // dump_text($data);
        foreach ($data as $key => $value) {
            // 合作表的状态表述
            // $show_status = '';
            $dostr = '';
            if ($value['company1'] == session('company_id')) {
                $data[$key]['name'] = M('company')->where(array('id' => $value['company2']))->getField('name');
                if ($value['status'] == 2) {
                    $show_status = '待对方确认';
                } else {
                    $show_status = '对方拒绝合作';
                }
                $option = "{
                type:'get',
                data:{id:'".$value['id']."'},
                url:'".U('del')."',
                confirmMsg:'确认操作吗？'
                }";
                $dostr .= create_button($option, 'doajax', '删除合作');
            } else {
                $data[$key]['name'] = M('company')->where(array('id' => $value['company1']))->getField('name');
                if ($value['status'] == 2) {
                    $show_status = '待我方确认';
                    $option = "{
                type:'get',
                data:{id:'".$value['id']."',status:1},
                url:'".U('edit')."',
                confirmMsg:'确认操作吗？'
                }";
                    $option1 = "{
                type:'get',
                data:{id:'".$value['id']."',status:3},
                url:'".U('edit')."',
                confirmMsg:'确认操作吗？'
                }";
                    $dostr .= create_button($option, 'doajax', '确认合作').create_button($option1, 'doajax', '拒绝合作');
                } else {
                    $show_status = '已拒绝合作';
                }
            }
            $data[$key]['status'] = $show_status;
            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 处理物流公司之间的合作
    public function edit(){
        $id = I('id');
        $status = I('status');
        $res = M('company_company')->where(array('id' => $id))->save(array('status' => $status));
        $info = M('company_company')->find($id);
        if ($status == 1) {
            // 同意合作
            $message = getCompanyName($info['company2']).'同意了贵公司发起的合作，请知悉';
        } else {
            // 拒绝合作
            $message = getCompanyName($info['company2']).'拒绝了贵公司发起的合作，请知悉';
        }
        // 通知合作公司管理员有公司合作申请
        D('Push', 'Logic')->noticeManager('公司合作申请结果',$message, $info['company1']);
        show_res($res);
    }

    // 删除合作记录
    public function del(){
        $id = I('id');
        // 合作之前的删除
        // 2017年6月12日10:51:23 zgw 不能物理删除，这样承运历史就不存在了。
        $res = M('company_company')->where(array('id' => $id, 'status' => array('in', array(2,3))))->save(array('status' => 6));
        show_res($res);
    }

    // 合作中的公司
    public function getCooCompany(){
        $db = M('company_company cc');
        $where = array(
            'cc.status' => array('in', array(1,4,5)),
            '_string' => 'cc.company1 = '.session('company_id').' or cc.company2 = '.session('company_id'),
        );

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        if ($name = trim(I('real_name', ''))) {
            $where['u.real_name'] = array('like', '%'.$name.'%');
        }
        if ($phone = I('phone', '')) {
            $where['u.phone'] = $phone;
        }

        $count_res = $db
            ->where($where)
            ->count();
        $count = count($count_res);
        $data = $db
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->select();
        foreach ($data as $key => $value) {
            if ($value['status'] == 1) {
                $status = '合作中';
            }
            $delConfirm = 0;

            // 公司名和管理员名和管理员手机
            if ($value['company1'] == session('company_id')) {
                $cid = $value['company2'];
                if ($value['status'] == 4) {
                    $status = '待对方确认解除合作';
                } else if ($value['status'] == 5) {
                    $status = '待我方确认解除合作';
                    $delConfirm = 1;
                }
            } else {
                $cid = $value['company1'];
                if ($value['status'] == 4) {
                    $status = '待我方确认解除合作';
                    $delConfirm = 1;
                } else if ($value['status'] == 5) {
                    $status = '待对方确认解除合作';
                }
            }
            $company_info = M('company c')
                ->field('c.name, u.real_name, u.phone')
                ->join('left join coal_users u on u.company_id = c.id and u.is_admin = 1')
                ->where(array('c.id' => $cid))
                ->find();
            $data[$key]['company_name'] = $company_info['name'].'<br>('.$company_info['real_name'].' '.$company_info['phone'].')';

            // 状态
            $data[$key]['state'] = $status;

            // 获取公司合作车主合作中的车辆

            $num = M('truck')->where('((anchored_id = '.session('company_id').' and user_id = '.$cid.') or (anchored_id = '.$cid.' and user_id = '.session('company_id').')) and is_comperation = 1')->count();
            $data[$key]['num'] = $num;

                // 获取本月已经拉的吨数----------
            // $beg_time=date('Y-m-01 00:00:00', strtotime(date("Y-m-d")));//本月第一天
            // $end_time=date('Y-m-d 00:00:00', strtotime("$beg_time +1 month -1 day"));//本月最后一天
            //
            // $where="unix_timestamp(dis_time) >unix_timestamp('".$beg_time."') and unix_timestamp(dis_time) <unix_timestamp('".$end_time."')";
            //
            // $bill_num=M('bill')->where(array('trucker_id'=>$value['trucker_id'],'state'=>4))->where($where)->select();
            //
            // $ret['rows'][$key]['bill_num']=0;
            // if($bill_num){
            //     foreach ($bill_num as $k=>$vo){
            //         $ret['rows'][$key]['bill_num']=$ret['rows'][$key]['bill_num']+$vo['end_w'];
            //     }
            // }
            // ----------------------

            // 生成操作链接
            if ($value['status'] == 1) {
                $option = "{
                id:'coo_company_push_truck',
                url:'".U('pushTruck')."',
                data:{company_id:'".$cid."'},
                type:'get',
                fresh:true,
                height:'600',
                width:'800'
            }";
                $option1 = "{
                id:'coo_company_detail',
                url:'".U('cooTrucks')."',
                data:{company_id:'".$cid."'},
                type:'get',
                fresh:true,
                height:'600',
                width:'800'
            }";
                $option2 = "{
                type:'post',
                data:{id:'".$value['id']."', company_id:'".$cid."'},
                url:'".U('delCoo')."',
                confirmMsg:'确定要解除合作吗？'
                }";
                $dostr = create_button($option, 'dialog', '推送车辆') . create_button($option1, 'dialog', '详情') . create_button($option2, 'doajax', '解除合作');
            } else {
                if ($delConfirm == 1) {
                    $option2 = "{
                type:'post',
                data:{id:'".$value['id']."', cid:'".$cid."'},
                url:'".U('delConfirm')."',
                confirmMsg:'确定操作吗？'
                }";
                    $dostr = create_button($option2, 'doajax', '确认解除合作');
                } else {
                    $dostr = '';
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

    // 推送车辆
    public function pushTruck(){
        if (IS_POST) {
            $coo_company = I('post.coo_company');
            $ids = I('post.truck_ids');
            // dump($_POST);exit;
            $res = M('truck')
                ->where(array('id' => array('in', $ids)))
                ->save(array('user_id' => $coo_company, 'is_comperation' => 0));
            if ($res !== false) {
                after_alert(array('closeCurrent' => true, 'tabid' => 'ComperationCompany_cooCompany'));
            } else {
                alert_false();
            }
        }
        $this->coo_company = I('company_id');
        $trucks = M('truck')->where(array('anchored_id' => session('company_id'), 'user_id' => session('company_id'), 'state' => 1))->select();
        $trucks1 = array();
        $i = 0;
        foreach ($trucks as $key => $value) {
            $drivers = get_drivers($value['id']);
            if ($drivers != '还没有绑定司机') {
                $trucks1[$i] = $value;
                $trucks1[$i]['drivers'] = $drivers;
                $i++;
            }
        }
        $this->trucks = $trucks1;
        $this->display();
    }

    // 某个合作公司的车辆详情页面
    public function cooTrucks(){
        // 根据车主id得到车主的个人信息
        $company_id = I('company_id', 0);

        if ($company_id) {
            $this->info = M('company c')
                ->field('c.*, u.real_name, u.phone')
                ->join('left join coal_users u on u.company_id and u.is_admin = 1')
                ->where(array('c.id' => $company_id))
                ->find();
            $this->display();
        } else {
            after_alert(array('closeCurrent' => true));
        }
    }

    // 某个合作公司的车辆详情
    public function getCooTrucks(){
        $type = I('get.type');
        $cid  = I('get.cid');
        $db = M('truck');
        $where = array();
        switch ($type) {
            case 1:
                // 合作中
                $where['_string'] = '(anchored_id = '.session('company_id').' and user_id = '.$cid.') or (anchored_id = '.$cid.' and user_id = '.session('company_id').')';
                $where['is_comperation'] = 1;
                break;
            case 2:
                // 待我方接收
                $where['is_comperation'] = 0;
                $where['user_id'] = session('company_id');
                $where['anchored_id'] = $cid;
                break;
            case 3:
                // 待对方接收
                $where['is_comperation'] = 0;
                $where['anchored_id'] = session('company_id');
                $where['user_id'] = $cid;
                break;
            default:
                alert_illegal();
                break;
        }

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        // if ($name = trim(I('real_name', ''))) {
        //     $where['u.real_name'] = array('like', '%'.$name.'%');
        // }
        // if ($phone = I('phone', '')) {
        //     $where['u.phone'] = $phone;
        // }

        $count = $db
            ->where($where)
            ->count();
        $data = $db
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->select();
        // dump($data);
        // sql();
        foreach ($data as $key => $value) {
            // 每辆车绑定的司机
            $data[$key]['driver'] = get_drivers($value['id']);
            // 得到车的状态
            $data[$key]['state'] = getTruckType($value['state']);


            // 操作
            $dostr = '';
            switch ($type){
                case 1:
                    $user_company_name = M('company')->where(array('id' => $value['user_id']))->getField('name');
                    $anchored_company_name = M('company')->where(array('id' => $value['anchored_id']))->getField('name');
                    // 合作关系
                    if ($value['user_id'] == session('company_id')) {
                        // 挂靠者推送
                        $relation_str = $anchored_company_name.'<br>=><br>'.$user_company_name;
                    } else {
                        // 本公司推送
                        $relation_str = $anchored_company_name.'<br>=><br>'.$user_company_name;
                    }
                    $data[$key]['relation'] = $relation_str;
                    // 合作中，移除车辆
                    $option2 = "{
                    type:'post',
                    data:{id:'".$value['id']."'},
                    url:'".U('remove')."',
                    confirmMsg:'确定操作吗？'
                    }";
                    $dostr .= create_button($option2,'doajax','移除车辆');
                    break;
                case 2:
                    // 待我方接收，接收、拒绝
                    $option2 = "{
                    type:'post',
                    data:{id:'".$value['id']."',cid:".$cid."},
                    url:'".U('recieve')."',
                    confirmMsg:'确定操作吗？'
                    }";
                    $option3 = "{
                    type:'post',
                    data:{id:'".$value['id']."',cid:".$cid."},
                    url:'".U('refuse')."',
                    confirmMsg:'确定操作吗？'
                    }";
                    $dostr .= create_button($option2,'doajax','接收').create_button($option3,'doajax','拒绝');
                    break;
                case 3:
                    // 待对方接收,撤回推送
                    $option2 = "{
                    type:'post',
                    data:{id:'".$value['id']."'},
                    url:'".U('back')."',
                    confirmMsg:'确定操作吗？'
                    }";
                    $dostr .= create_button($option2,'doajax','撤回推送');
                    break;
                default:
                    break;
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

    // 移除车辆
    public function remove(){
        $truck_id = I('id');
        $truck_info = M('truck')->find($truck_id);
        if ($truck_info['state'] != 1) {
            alert_false('车辆不是空车');
        }
        if ($truck_info['user_id'] == session('company_id')){
            // 对方给我的,user_id = session（'company_id'）
            $res = M('truck')->save(array('id' => $truck_id, 'is_comperation' => 1, 'user_id' => $truck_info['anchored_id']));
        } else {
            // 推送给对方的,anchored_id = session（'company_id'）
            $res = M('truck')->save(array('id' => $truck_id, 'is_comperation' => 1, 'user_id' => session('company_id')));
        }
        if ($res !== false) {
            // 踢司机出群
            vendor('Emchat.Easemobclass');
            $group_id=M('company')->where(array('id'=>session('company_id')))->getField('group_id');
            $driver_id=M('driver')->where(array('truck_id'=>$truck_id))->field('uid')->select();
            $h=new \Easemob();
            foreach ($driver_id as $value){
                $h->deleteGroupMember($group_id,$value['uid']);
            }

            after_alert(array('closeCurrent' => true));
        } else {
            alert_false();
        }
    }
    // 接收
    public function recieve(){
        $truck_id = I('id');
        $truck = M('truck')->find($truck_id);
        if ($truck['state'] != 1) {
            alert_false('车辆不是空车');
        }
        $res = M('truck')->save(array('id' => $truck_id, 'is_comperation' => 1));
        if ($res !== false) {
            // 拉司机进群
            vendor('Emchat.Easemobclass');
            $group_id=M('company')->where(array('id'=>session('company_id')))->getField('group_id');
            $driver_id=M('driver')->where(array('truck_id'=>$truck_id))->field('uid')->select();
            $h=new \Easemob();
            foreach ($driver_id as $value){
                $h->addGroupMember($group_id,$value['uid']);
            }
            after_alert(array('closeCurrent' => true));
        } else {
            alert_false();
        }
    }
    // 拒绝
    public function refuse(){
        $truck_id = I('id');
        $cid = I('cid');
        $res = M('truck')->save(array('id' => $truck_id, 'is_comperation' => 1, 'user_id' => $cid));
        if ($res !== false) {
            after_alert(array('closeCurrent' => true));
        } else {
            alert_false();
        }
    }
    // 撤回推送
    public function back(){
        $truck_id = I('id');
        $res = M('truck')->save(array('id' => $truck_id, 'is_comperation' => 1, 'user_id' => session('company_id')));
        if ($res !== false) {
            after_alert(array('closeCurrent' => true));
        } else {
            alert_false();
        }
    }

    // 公司解除合作
    public function delCoo(){
        if (IS_POST && I('post.id')) {
            $id = I('post.id');
            $cid = I('post.company_id');
            // 看有没有待接收的车辆和已接收的车辆
            $num = M('truck')->where('(anchored_id = '.session('company_id').' and user_id = '.$cid.') or (anchored_id = '.$cid.' and user_id = '.session('company_id').')')->count();
            if ($num > 0) {
                alert('该公司还有车辆在接收或待接收状态，请在详情中移除所有车辆', 300);
            }
            $coo_info = M('company_company')->find($id);
            if ($coo_info['company1'] == session('company_id')) {
                $status = 4; // 发起者主动
                $relation = '发起者';
            } else if ($coo_info['company2'] == session('company_id')){
                $status = 5; // 接受者主动
                $relation = '接受者';
            } else {
                alert_illegal('数据非法');
            }
            // 更新company_company表的状态
            $data = array(
                'id'      => $id,
                'status'  => $status,
            );
            $res = D('CompanyCompany')->save($data);
            doLog($relation.'公司'.session('company_id').'解除了与公司'.$cid.'合作');
            // 通知公司管理员？？？？有公司解除合作
            // D('Push', 'Logic')->noticeTruckerCompanyBreakCooperation($owner_id, session('company_id'));
            after_alert(array('reload' => true));
        } else {
            alert_illegal();
        }
    }

    // 最终确认解除合作
    public function delConfirm(){
        if (IS_POST && I('post.id')) {
            $id = I('post.id');
            $cid = I('post.cid');
            $info = M('company_company')->find($id);
            if (($info['company1'] == session('company_id') && $info['company2'] == $cid) || ($info['company2'] == session('company_id') && $info['company1'] == $cid)) {
                $res = M('company_company')->save(array('id' => $id, 'status' => 6));
                if ($res !== false) {
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

    // -------获得合作公司分派历史的统计
    public function getFenpaiHistory(){
        $db = M('company_company cc');
        $where = array(
            '_string' => 'cc.company1 = '.session('company_id').' or cc.company2 = '.session('company_id'),
        );
        $cc_info = $db->where($where)->select();
        $data= array();
        $i = 0;
        foreach ($cc_info as $key => $value) {
            //公司名字
            //管理员手机
            if ($value['company1'] != session('company_id')) {
                $another_comapny = $value['company1'];
            } else {
                $another_comapny = $value['company2'];
            }

            //公司名字
            //管理员手机
            $data[$i]['name'] = M('company')->where(array('id' => $another_comapny))->getField('name');
            $data[$i]['phone'] = M('users')->where(array('company_id' => $another_comapny, 'is_admin' => 1))->getField('phone');

            //已拉车次
            $data[$i]['times'] = M('bill')->where(array('company' => session('company_id'), 'anchored_id' => $another_comapny))->count();
            //已拉吨数
            $end_w = M('bill')->where(array('company' => session('company_id'), 'anchored_id' => $another_comapny))->sum('end_w');
            $data[$i]['end_w'] = $end_w?$end_w:0;

            //合作状态
            $arr = array(1=>'双方已经确认',2=>'待对方确认',3=>'对方拒绝',4=>'我方解除合作',5=>'对方解除合作',6=>'已解除');
            $data[$i]['status'] = $arr[$value['status']];

            // 操作
            if ($end_w > 0) {
                $option1 = "{
                    id:'coo_company_statics_detail',
                    url:'".U('fenpaiList')."',
                    data:{id:'".$another_comapny."'},
                    type:'get',
                    fresh:true,
                    height:'600',
                    width:'800'
                }";
                $data[$i]['dostr'] = create_button($option1,'dialog','详情');
            }
            $i++;
        }
        echo json_encode($data);
    }

    public function fenpaiList(){
        $this->id = I('get.id');
        $this->display();
    }

    // 获得某个合作公司分派历史的订单统计
    public function getFenpaiList(){
        $db = M('bill b');
        $another_comapny = I('get.id');
        $where = array('company' => session('company_id'), 'anchored_id' => $another_comapny, 'state' => 6);
        // // 总公司和子公司关系
        // $ids = retrun_company_ids(session('company_id'));
        // $where['writer_id'] = array('in', $ids);

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        // 要做时间的筛选 2017年5月19日15:41:51

        // if ($name = trim(I('real_name', ''))) {
        //     $where['u.real_name'] = array('like', '%'.$name.'%');
        // }
        // if ($title = I('title', '')) {
        //     $where['title'] = array('like', '%'.$title.'%');
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
            // ->order('creat_time desc')
            ->select();
        // sql();
        foreach ($data as $key => $value) {
            // 车牌号
            $data[$key]['lic_number'] = M('truck')->where(array('id'=>$value['truck_id']))->getField('lic_number');;

            // 司机
            $driver = M('users')->where(array('id'=>$value['driver_id']))->find();
            $data[$key]['driver'] = $driver['real_name'];

            // 损耗吨数
            $data[$key]['use_w'] = $value['begin_w'] - $value['end_w'];

        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // ----------获得合作公司承运历史的统计
    public function getChengyunHistory(){
        $db = M('company_company cc');
        $where = array(
            '_string' => 'cc.company1 = '.session('company_id').' or cc.company2 = '.session('company_id'),
        );
        $cc_info = $db->where($where)->select();
        $data= array();
        $i = 0;
        foreach ($cc_info as $key => $value) {
            //公司名字
            //管理员手机
            if ($value['company1'] != session('company_id')) {
                $another_comapny = $value['company1'];
            } else {
                $another_comapny = $value['company2'];
            }

            //公司名字
            //管理员手机
            $data[$i]['name'] = M('company')->where(array('id' => $another_comapny))->getField('name');
            $data[$i]['phone'] = M('users')->where(array('company_id' => $another_comapny, 'is_admin' => 1))->getField('phone');

            //已拉车次
            $data[$i]['times'] = M('bill')->where(array('company' => $another_comapny, 'anchored_id' => session('company_id')))->count();
            //已拉吨数
            $end_w = M('bill')->where(array('company' => $another_comapny, 'anchored_id' => session('company_id')))->sum('end_w');
            $data[$i]['end_w'] = $end_w?$end_w:0;

            //合作状态
            $arr = array(1=>'双方已经确认',2=>'待对方确认',3=>'对方拒绝',4=>'我方解除合作',5=>'对方解除合作',6=>'已解除');
            $data[$i]['status'] = $arr[$value['status']];

            // 操作
            if ($end_w > 0) {
                $option1 = "{
                    id:'coo_company_statics_detail',
                    url:'".U('chengyunList')."',
                    data:{id:'".$another_comapny."'},
                    type:'get',
                    fresh:true,
                    height:'600',
                    width:'800'
                }";
                $data[$i]['dostr'] = create_button($option1,'dialog','详情');
            }
            $i++;
        }
        echo json_encode($data);
    }

    public function chengyunList(){
        $this->id = I('get.id');
        $this->display();
    }

    // 获得某个合作公司承运历史的订单统计
    public function getChengyunList(){
        $db = M('bill b');
        $another_comapny = I('get.id');
        $where = array('company' => $another_comapny, 'anchored_id' => session('company_id'), 'state' => 6);
        // // 总公司和子公司关系
        // $ids = retrun_company_ids(session('company_id'));
        // $where['writer_id'] = array('in', $ids);

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        // 要做时间的筛选 2017年5月19日15:41:51

        // if ($name = trim(I('real_name', ''))) {
        //     $where['u.real_name'] = array('like', '%'.$name.'%');
        // }
        // if ($title = I('title', '')) {
        //     $where['title'] = array('like', '%'.$title.'%');
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
            // ->order('creat_time desc')
            ->select();
        // sql();
        foreach ($data as $key => $value) {
            // 车牌号
            $data[$key]['lic_number'] = M('truck')->where(array('id'=>$value['truck_id']))->getField('lic_number');;

            // 司机
            $driver = M('users')->where(array('id'=>$value['driver_id']))->find();
            $data[$key]['driver'] = $driver['real_name'];

            // 损耗吨数
            $data[$key]['use_w'] = $value['begin_w'] - $value['end_w'];

        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }


}