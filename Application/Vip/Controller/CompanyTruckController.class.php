<?php
/**
 * Created by PhpStorm.
 * 物流自有车辆控制器
 * User: zgw
 * Date: 2017-04-24
 * Time: 0:48
 */
namespace Vip\Controller;

class CompanyTruckController extends CommonController {

    // 添加自有车辆
    public function addCompanyTruck()
    {
        if (IS_POST) {//2017年4月24日0:57:22 问题：在后台添加 还是在vip，添加的流程是什么？是否默认挂靠在自己公司下面，使用者是自己？
            // dump($_POST);exit;
            //验证提交数据是否全面
            if(!I('maximum')||!I('lic_number')||!I('lic_pic')){
                alert('提交信息不完整', 300);
            }

            //检车车辆编号是否重复
            $owner_order = I('owner_order');
            $company_id = session('company_id');
            if ($owner_order && D('Truck')->isOwnerOrderRepeat($owner_order, $company_id)) {
                alert('车辆编号重复了', 300);
            }

            $post = I('post.');

            $post['owner_id']       = $company_id;
            $post['anchored_id']    = $company_id;
            $post['user_id']        = $company_id;
            $post['is_anchored']    = 1;
            $post['is_comperation'] = 1;
            $post['owner_type']     = 2; // 公司拥有
            $post['create_time']    = today_day();
            $post['is_passed']      = 1; // 不用审核了 2017年5月24日17:50:06  zjw 提出不用在添加的时候审，是在合作的时候审
            $post['state']           = 1;

            M()->startTrans();
            $res = D('Truck')->addData($post);
            if (!$owner_order) {
                $res1 = M('truck')->where(array('id' => $res['id']))->save(array('owner_order' => "MWT00".$res['id']));
            } else {
                $res1 = true;
            }
            if ($res['code'] == 1 && $res1 !== false) {
                M()->commit();
                after_alert(array('closeCurrent' => true, 'tabid' => 'CompanyTruck_companyTrucks'));
            } else {
                M()->rollback();
                if ($res['message']) {
                    alert($res['message'], 300);
                } else {
                    alert_false();
                }
            }
        }
        $this->display();
    }

    // 自有车辆审核中列表
    public function getNotConfirmCompanyTrucks(){
        $db = M('truck t');
        $where = array(
            't.is_passed'   => 0,
            't.owner_type'  => 2,
            't.owner_id'    => session('company_id'),
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
            ->join('left join coal_company c on c.id = t.owner_id')
            ->where($where)
            ->count();

        $data = $db
            ->field('t.*, c.name as company_name')
            ->join('left join coal_company c on c.id = t.owner_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->select();
        // sql();
        // dump_text($data);
        foreach ($data as $key => $value) {
            $data[$key]['state'] = getTruckType($value['state']);

            // $option1 = "{
            //     id:'com_truck_edit',
            //     data:{id:'".$value['id']."'},
            //     url:'".U('edit')."'
            //     }";
            $option2 = "{
                id:'company_truck_detail',
                data:{id:'".$value['id']."'},
                url:'".U('detail')."',
                width:'800',
                height:'600',
                }";
            $option3 = "{
                type:'get',
                data:{id:'".$value['id']."'},
                url:'".U('pass')."',
                confirmMsg:'确定要审核通过吗？'
                }";
            // $dostr = create_button($option1, 'dialog', '编辑').create_button($option2, 'dialog', '司机管理').create_button($option3, 'doajax', '删除');
            // // echo $dostr;exit;
            $data[$key]['dostr'] = create_button($option2, 'dialog', '详情').create_button($option3, 'doajax', '审核通过');
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 自有车辆列表
    public function getCompanyTrucks(){
        $db = M('truck t');
        $where = array(
            't.is_passed' => 1,
            't.owner_type'  => 2,
            't.owner_id'  => session('company_id'),
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
            ->join('left join coal_company c on c.id = t.owner_id')
            ->where($where)
            ->count();

        $data = $db
            ->field('t.*, c.name as company_name')
            ->join('left join coal_company c on c.id = t.owner_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->select();
        // sql();
        // dump_text($data);
        foreach ($data as $key => $value) {
            $data[$key]['state'] = getTruckType($value['state']);
            $tmp_info = M('driver')->where(array('truck_id' => $value['id']))->select();
            if (count($tmp_info) == 0) {
                $data[$key]['driver'] = '还没有绑定司机';
            } else if (count($tmp_info) == 1) {
                $data[$key]['driver'] = M('users')->where(array('id' => $tmp_info[0]['uid']))->getField('real_name');
            } else {
                $tmp_name = M('users')->where(array('id' => $tmp_info[0]['uid']))->getField('real_name');
                $data[$key]['driver'] = $tmp_name . '等';
            }
            $option1 = "{
                id:'com_truck_edit',
                data:{id:'".$value['id']."'},
                url:'".U('editCompanyTruck')."',
                width:'800',
                height:'600'
                }";
            $option2 = "{
                id:'com_truck_driver_manage',
                data:{id:'".$value['id']."'},
                url:'".U('drivers')."',
                width:'800',
                height:'600'
                }";
            $option3 = "{
                type:'get',
                data:{id:'".$value['id']."'},
                url:'".U('del')."',
                confirmMsg:'确定要删除吗？'
                }";
            $dostr = create_button($option1, 'dialog', '编辑').create_button($option2, 'dialog', '司机管理').create_button($option3, 'doajax', '删除');
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

    // 司机详情页面
    public function drivers(){
        $this->id = I('get.id');
        $this->display();
    }

    // 获得已绑定司机列表
    public function getDrivers(){
        $db = M('driver d');
        $where = array(
            'd.truck_id'   => I('get.id'),
            // 't.owner_type'  => 2,
            // 't.owner_id'    => session('company_id'),
        );
        // dump($where);exit;
        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        // if ($name = trim(I('real_name', ''))) {
        //     $where['u.real_name'] = array('like', '%'.$name.'%');
        // }
        // if ($phone = I('phone', '')) {
        //     $where['u.phone'] = $phone;
        // }

        $count = $db
            ->join('left join coal_users u on d.uid = u.id')
            ->where($where)
            ->count();

        $data = $db
            ->field('d.*, u.real_name as driver_name, u.phone, t.state')
            ->join('left join coal_users u on d.uid = u.id')
            ->join('left join coal_truck t on t.id = d.truck_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->select();
        // sql();
        // dump_text($data);
        foreach ($data as $key => $value) {
            $work_time = '';
            switch ($value['work_time']){
                case 1:
                    $work_time = '全天';
                    break;
                case 2:
                    $work_time = '白班';
                    break;
                case 3:
                    $work_time = '夜班';
                    break;
                default:
                    break;
            }
            $data[$key]['work_time'] = $work_time;
            $data[$key]['is_work'] = $value['is_work']?'正常':'休息';

            // 操作
            $dostr = '';
            // 解绑
            if ($value['state'] == 1) {
                $option3 = "{
                type:'get',
                data:{driver_id:'".$value['id']."',truck_id:'".I('get.id')."'},
                url:'".U('unband')."',
                confirmMsg:'确定要解绑吗？'
                }";
                // $dostr = create_button($option1, 'dialog', '编辑').create_button($option2, 'dialog', '司机管理').create_button($option3, 'doajax', '删除');
                // // echo $dostr;exit;
                $dostr .= create_button($option3, 'doajax', '解绑');
            } else {
                $dostr .= '车辆不是空车，不能解绑';
            }
            // 司机排班
            $option4 = "{
                id:'CompanyTruck_changeWTime',
                type:'get',
                data:{driver_id:'".$value['uid']."'},
                url:'".U('changeWTime')."'
                }";
            $dostr .= create_button($option4, 'dialog', '司机排班');
            $data[$key]['dostr'] = $dostr;

        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }
    // 绑定司机
    public function band(){
        if (IS_POST) {

            $phone = I('post.phone');
            $user_info = M('users')->field('local_gps', true)->where(array('phone' => $phone))->find();

            if (!$user_info) alert('没有这个用户', 300);
            $driver_info = M('driver')->where(array('uid' => $user_info['id']))->find();
            if (!$user_info) alert('用户还没有认证司机', 300);
            if ($driver_info['is_passed'] == 0) alert('司机还没有通过审核', 300);
            if ($driver_info['truck_id'] > 0) alert('司机已经绑定过车辆', 300);

            $truck_id = I('post.truck_id');
            $res = M('driver')->save(array('id' => $driver_info['id'], 'truck_id' => $truck_id));
            if ($res !== false) {
                //推送消息给司机
                // D('Push','Logic')->noticeDriverBind($user_info['id'],session('user_id'),$truck_id);
                $truck = M('truck')->where(array('id' => $truck_id))->find();
                $lic_number = $truck['lic_number'];
                $company_name = getCompanyName(session('company_id'));
                D('Push', 'Logic')->noticeDriver('[煤问题]绑定成功',"恭喜您,您的成功绑定在[$company_name]的[$lic_number]上面",$user_info['id']);
                // 拉司机进群
                vendor('Emchat.Easemobclass');
                $group_id=M('company')->where(array('id'=>session('company_id')))->getField('group_id');
                $h=new \Easemob();
                $h->addGroupMember($group_id,$user_info['id']);

                after_alert(array('closeCurrent' => true, 'dialog' => 'com_truck_driver_manage', 'tabid' => 'CompanyTruck_companyTrucks')); // 无效
            } else {
                alert_false();
            }
        }
        $this->truck_id = I('id');
        $this->display();
    }

    public function searchDriver(){
        $db = M('driver d');
        $where = array();
        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        if ($name = trim(I('real_name', ''))) {
            $where['u.real_name'] = array('like', '%'.$name.'%');
        }
        if ($phone = I('phone', '')) {
            $where['u.phone'] = array('like', $phone.'%');
        }
        if ($lic_number = trim(I('lic_number', ''))) {
            $where['d.lic_number'] = array('like', '%'.$lic_number.'%');
        }
        if ($lic_level = trim(I('lic_level', ''))) {
            $where['d.lic_level'] = array('like', '%'.$lic_level.'%');
        }
        if ($lic_endtime = trim(I('lic_endtime', ''))) {
            $where['d.lic_endtime'] = array('like', '%'.$lic_endtime.'%');
        }

        $count = $db
            ->join('left join coal_users u on u.id = d.uid')
            ->where($where)
            ->count();

        $data = $db
            ->field('d.lic_number, d.lic_endtime, d.lic_level, u.real_name, u.phone')
            ->join('left join coal_users u on u.id = d.uid')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->select();

        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 解绑司机
    public function unband(){
        $driver_id = I('driver_id');
        $truck_id = I('truck_id');
        $driver_info = M('driver')->where(array('id' => $driver_id, 'truck_id' => $truck_id))->find();
        if (!$driver_info) alert('司机车辆不对应');
        // $res = M('driver')->save(array('id' => $driver_info['id'], 'truck_id' => 0));
        $res = D('Driver', 'Logic')->fire(session('user_id'), $driver_info['uid']);
        if ($res['code'] == 1) {
            // 踢司机出群
            vendor('Emchat.Easemobclass');
            $group_id=M('company')->where(array('id'=>session('company_id')))->getField('group_id');
            $h=new \Easemob();
            $h->deleteGroupMember($group_id,$driver_info['uid']);

            after_alert(array('closeCurrent' => true, 'tabid' => 'CompanyTruck_companyTrucks'));
        } else {
            alert_false();
        }
    }

    // 自有车司机排班
    public function changeWTime(){
        if (IS_POST) {
            $driver_id = I('post.driver_id');
            $type = I('post.type');
            if ($driver_id && $type) {
                $res = D('Driver', 'Logic')->changeWorkTime($driver_id, $type);
                if ($res['code'] == 1) {
                    after_alert(array('closeCurrent' => true, 'dialog' => 'com_truck_driver_manage'));
                } else {
                    alert_false($res['message']);
                }
            } else {
                alert_illegal();
            }
        }
        $this->driver_id = I('get.driver_id');
        $this->display();
    }

    // 通过审核
    public function pass(){
        $truck_id = I('id');
        $res = D('Truck', 'Logic')->auditPass(array($truck_id));
        show_res($res);
    }

    // 编辑自有车辆信息
    public function editCompanyTruck(){
        if (IS_POST) {
            $post = I('post.');
            $info = M('truck')->find($post['id']);
            if (!edit_unique(M('truck'), $info['owner_order'], 'owner_order', $post['owner_order'])) {
                alert('车辆编号重复了', 300);
            }
            if (!edit_unique(M('truck'), $info['lic_number'], 'lic_number', $post['lic_number'])) {
                alert('行驶证号重复了', 300);
            }
            if (is_expire($post['ins_date']) || is_expire($post['check_date'])) {
                alert('提交的日期过期', 300);
            }
            $res = M('truck')->save($post);
            if ($res !== false) {
                after_alert(array('closeCurrent' => true, 'tabid' => 'CompanyTruck_companyTrucks'));
            } else {
                alert_false();
            }
        }
        $id = I('get.id');
        if ($id) {
            $this->truck_info = M('truck')->find($id);
            $this->display();
        } else {
            alert('非法访问', 300);
        }
    }

    // 查看车辆详情
    public function detail(){
        $id = I('get.id');
        if ($id) {
            $this->truck_info = M('truck')->find($id);
            $this->display();
        } else {
            alert('非法访问', 300);
        }
    }

    // 删除自有车辆
    public function del(){
        $id = I('id') + 0;
        if ($id) {
            //检查该车辆是否是当前公司的车辆
            $truck = M('truck')->where(array('id' => $id, 'owner_id' => session('company_id'), 'owner_type' => 2))->find();
            if(!$truck){
                alert('车辆信息有误', 300);
            }
            //检查该车辆是否有正在绑定的司机
            $trucker=M('driver')->where(array('truck_id'=>I('id')))->find();
            if($trucker){
                alert('该车辆绑定的还有司机，不能删除', 300);
            }
            $res = M('truck')->delete($id);
            show_res($res);
        } else {
            alert('操作有误', 300);
        }
    }

}