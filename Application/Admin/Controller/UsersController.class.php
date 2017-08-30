<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/4/15
 * Time: 14:53
 */
namespace Admin\Controller;

class UsersController extends CommonController {
    // 未认证用户
    public function getUnauthorizedList(){
        $db = M('users u');
        $where = array('u.is_authentication' => 0);

        if ($account = I('account', '')) {
            $where['u.account'] = array('like', '%'.$account.'%');
        }
        if ($name = I('name', '')) {
            $where['u.real_name'] = array('like', '%'.$name.'%');
        }
        if ($phone = I('phone', '')) {
            $where['u.phone'] = array('like', '%'.$phone.'%');
        }

        $count = $db
            ->field('u.local_gps', true)
            ->where($where)
            ->count();
        $page_num  = I('pageCurrent') - 1;
        $page_size = I('pageSize', 2);

        $data = $db
            ->field('local_gps', true)
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('u.create_time desc, u.id desc')
            ->select();

        foreach ($data as $key => $value) {
            $option1 = "{
                id:'Users_unaudit',
                data:{id:'".$value['id']."'},
                url:'".U('Admin/Users/editUser')."',
                width:'800',
                height:'600',
                refresh:true
                }";

            $dostr = create_button($option1, 'dialog', '编辑');
            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }
    // 用户编辑
    public function editUser(){
        if (IS_POST) {
            $post = I('post.');
            // dump($post);exit;
            $data = array(
                'id' => $post['id'],
                'province' => $post['province'],
                'city' => $post['city'],
                'area' => $post['area'],
                'detail' => $post['detail'],
                'real_name' => $post['real_name'],
                'phone' => $post['phone'],
                'sex' => $post['sex'],
                'gesture_cipher' => $post['gesture_cipher'],
                'gesture_lock' => $post['gesture_lock'],
            );
            if ($post['photo']) {
                $data['photo'] = $post['photo'];
            }
            if ($post['password']) {
                $data['password'] = authcode($post['password']);
            }
            // dump($data);exit;
            $res = M('users')->save($data);
            if ($res !== false) {
                after_alert(array('closeCurrent' => true, 'reloadFlag' => 'Users_unauthorizedList，Users_authorizedList'));
            } else {
                after_false();
            }
        }
        $id = I('get.id');
        $info = M('users')->field('local_gps',true)->find($id);
        $info['local_gps'] = getComapnyGps($id);
        if(empty($info['local_gps']['x'])){
            $info['local_gps']=array(
                'x'=>'1',
                'y'=>'1'
            );
        }
        $data = array(
            'info'  => $info,
            'province'  => get_provice(),
            'cities'  => get_shiqu($info['province']),
            'area'  => get_diqu($info['province'],$info['city']),
        );
        $this->assign($data);
        $this->display();
    }
    // 已认证用户
    public function getUsers(){
        $db = M('users u');
        $where = array('u.is_authentication' => 1);

        if ($account = I('account', '')) {
            $where['u.account'] = array('like', '%'.$account.'%');
        }
        if ($name = I('name', '')) {
            $where['u.real_name'] = array('like', '%'.$name.'%');
        }
        if ($phone = I('phone', '')) {
            $where['u.phone'] = array('like', '%'.$phone.'%');
        }

        $count = $db
            ->field('u.local_gps', true)
            ->where($where)
            ->count();
        $page_num  = I('pageCurrent') - 1;
        $page_size = I('pageSize', 2);

        $data = $db
            ->field('local_gps', true)
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('u.create_time desc, u.id desc')
            ->select();

        foreach ($data as $key => $value) {
            // 是否审核通过, 写在操作里
            // $is_auth = '';
            // $res1 = M('truck')->where(array('order_type' => 1, 'owner_id' => $value['id'], 'is_passed' => 1))->find();
            // if ($res1) {
            //
            // }
            // $data[$key]['is_auth'] = $is_auth;

            $option1 = "{
                id:'Users_authorizedList',
                data:{id:'".$value['id']."'},
                url:'".U('Admin/Users/editUser')."',
                width:'800',
                height:'600',
                refresh:true
                }";
            $option2 = "{
                data:{id:'".$value['id']."'},
                url:'".U('Admin/Users/delUser')."',
                confirmMsg:'确定要删除吗？'
            }";
            $dostr = create_button($option1, 'dialog', '编辑');
            $dostr .= create_button($option2, 'doajax', '删除');
            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 删除面板
    public function delUsreRelation(){
        // 车辆表

        // 驾驶证表

        // 公司表

        // 用户表

        $data = array(

        );
        $this->display();
    }

    // 删除
    public function delUser(){
        $id = I('id');
        $res = M('users')->delete($id);
        show_res($res);
    }

    //----------------------------------车辆车主----------------------------------------------------------------
    //获取未审核的车辆列表
    public function getAuditTruck(){
        $db = M('truck t');
        $where = array('t.is_passed' => 0);

       if ($lic_number = I('lic_number', '')) {
           $where['t.lic_number'] = array('like', '%'.$lic_number.'%');
       }
       if ($name = I('name', '')) {
           $where['u.real_name'] = array('like', '%'.$name.'%');
       }

        $count = $db
            // ->join('left join coal_admin_auth_group_access ga on ga.uid = a.id')
            ->join('left join coal_users u on u.id = t.owner_id')
            ->where($where)
            ->count();

        $page_num  = I('pageCurrent') - 1;
        $page_size = I('pageSize', 2);

        $data = $db
            ->field('t.*, u.real_name as owner_name')
            // ->join('left join coal_admin_auth_group_access ga on ga.uid = a.id')
            ->join('left join coal_users u on u.id = t.owner_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('t.create_time desc, t.id desc')
            ->select();

        foreach ($data as $key => $value) {
            $option3 = "{
                id:'Users_editTruck',
                data:{id:'".$value['id']."'},
                url:'".U('Admin/Users/editTruck')."',
                width:'800',
                height:'600',
                refresh:true
                }";
            $option1 = "{
                data:{id:'".$value['id']."',type:1},
                url:'".U('Admin/Users/pass')."',
                confirmMsg:'确定要通过吗？'
                }";
            $option2 = "{
                data:{id:'".$value['id']."',type:1},
                url:'".U('Admin/Users/refuse')."',
                confirmMsg:'确定要拒绝吗？'
                }";

            $dostr = create_button($option3, 'dialog', '详情').create_button($option1, 'doajax', '通过') . create_button($option2, 'doajax', '拒绝');
            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 后台审核通过,通过的时候要人工选择地址!!未做
    public function pass() {
        $id = I('id');
        $type = I('type', 0);
        switch ($type) {
            case 1:
                // 车主（车辆）审核通过
                $res = D('Truck', 'Logic')->auditPass(array($id));
                if ($res) {
                    $truck = M('truck')->find($id);
                    D('Push','Logic')->noticeTrucker('车辆审核','恭喜您，您的车辆['.$truck['lic_number'].']已经通过审核',$truck['owner_id']);
                }
                break;
            case 2:
                // 司机审核通过
                $res = D('Driver', 'Logic')->auditPass(array($id));
                break;
            case 3:
                // 公司审核通过
                $res = D('Company', 'Logic')->auditPass(array($id));
                // 通知公司管理员，公司通过了审核
                $company_name = getCompanyName($id);
                $user = M('users')->where(array('company_id' => $id, 'is_admin' => 1))->find();
                D('Push','Logic')->noticeManager('公司审核','恭喜您，您的公司['.$company_name.']已经通过审核',$user['id']);
                // 短信通知管理员，待做


                //建群
                vendor('Emchat.Easemobclass');
                $h=new \Easemob();
                $options ['groupname'] = $company_name;
                $options ['desc'] = '这个群是'.$company_name.'的群';
                $options ['public'] = true;
                $options ['owner'] = M('users')->where(array('is_admin'=>1, 'company_id'=>$id))->getField('id');
                $group_id=$h->createGroup($options);

                $company['id'] = $id;
                $company['group_id']=$group_id['data']['groupid'];
                M('company')->save($company);
                break;
            default:
                $res = false;
                break;
        }

        show_res($res);
    }

    // 后台审核拒绝
    public function refuse(){
        $id = I('id');
        $type = I('type', 0);
        
        switch ($type) {
            case 1:
                // 车主（车辆）审核拒绝
                $info = M('truck')->find($id);
                $res = M('truck')->delete($id);
                //推送消息给车主？

                doLog('管理员'.session('sys_uid').'拒绝了车辆'.$info['lic_number']);
                break;
            case 2:
                // 司机审核拒绝
                $info = M('driver')->find($id);
                $res = M('driver')->delete($id);
                //推送消息给司机？

                doLog('管理员'.session('sys_uid').'拒绝了司机'.$info['lic_number']);
                break;
            case 3:
                // 公司审核拒绝
                $info = M('company')->find($id);
                $res = M('company')->delete($id);
                M('users')->where(array('company_id' => $id, 'is_admin' => 1))->limit(1)->save(array('company_id' => 0, 'is_authentication' => 0));

                // 通知公司管理员，公司通过了审核
                $user = M('users')->where(array('company_id' => $id, 'is_admin' => 1))->find();
                D('Push','Logic')->noticeManager('公司审核','很遗憾，您的公司['.$info['name'].']没有通过审核，请重新认证',$user['id']);
                // 短信通知管理员，待做



                // 日志
                doLog('管理员'.session('sys_uid').'拒绝了公司'.$info['name'].':'.$info['lic_number'].'管理员：'.$user['phone']);
                break;
            default:
                $res = false;
                break;
        }
        show_res($res);
    }

    //获取社会车主列表，获取已经成为车主的车和人的信息，行驶证表
    public function getTruckers(){
        $db = M('truck t');
        $where = array('owner_type' => 1,'is_passed' => 1);

        if ($lic_number = I('lic_number', '')) {
            $where['t.lic_number'] = array('like', '%'.$lic_number.'%');
        }
        if ($account = I('account', '')) {
            $where['u.account'] = array('like', '%'.$account.'%');
        }
        if ($real_name = I('real_name', '')) {
            $where['u.real_name'] = array('like', '%'.$real_name.'%');
        }
        if ($phone = I('phone', '')) {
            $where['u.phone'] = array('like', '%'.$phone.'%');
        }

        $group_res = $db
            ->join('left join coal_users u on u.id = t.owner_id')
            ->where($where)
            ->group('t.owner_id')
            ->select();
        $count = count($group_res);

        $page_num  = I('pageCurrent') - 1;
        $page_size = I('pageSize', 2);

        $data = $db
            ->field('u.id, u.account, u.real_name, u.phone, u.create_time, count(t.owner_id) num,
            u.province,u.city,u.area,u.detail')
            ->join('left join coal_users u on u.id = t.owner_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('t.create_time desc, t.id desc')
            ->group('t.owner_id')
            ->select();
        // sql();
        foreach ($data as $key => $value) {
            // 所有车辆车牌
            $lic_numbers = '';
            $trucks = M('truck')->where(array('owner_id' => $value['id'], 'owner_type' => 1))->select();
            foreach ($trucks as $k => $val) {
                $lic_numbers .= $val['lic_number'].'<br>';
            }
            $data[$key]['truck_lic_numbers'] = $lic_numbers;

            // 判断是不是车主（车为0或车为1且没审核的就不是车主）
            // $is_trucker = 1;
            // // if ($value['num'] == 0 || ($value['num'] == 1 && $value['is_passed'] == 0)) { // 不可能有0辆
            // if ($value['num'] == 1 && $value['is_passed'] == 0) {
            //     $is_trucker = 0;
            // }
            // $data[$key]['audit_state'] = $is_trucker;


            $option1 = "{
                data:{id:'".$value['id']."'},
                url:'".U('pass')."',
                confirmMsg:'确定要通过吗？'
                }";
            $option2 = "{
                data:{id:'".$value['id']."'},
                url:'".U('refuse')."',
                confirmMsg:'确定要删除吗？'
                }";
            $option3 = "{
                id:'trucker_detail',
                url:'".U('truckerDetail')."',
                data:{id:'".$value['id']."'},
                type:'get',
                height:'800',
                width: '500'
                }";
            // $dostr = create_button($option3, 'dialog', '详情'); // 只做查询
            $dostr = '';
            // if ($is_trucker == 0) {
            //     $dostr .= create_button($option1, 'doajax', '通过');
            // }
            $dostr .= create_button($option2, 'doajax', '删除');
            // $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }
    //获取公司车主列表，获取已经成为车主的车和人的信息，行驶证表
    public function getCompanyTruckers(){
        $db = M('truck t');
        $where = array('owner_type' => 2);

        if ($lic_number = I('lic_number', '')) {
            $where['t.lic_number'] = array('like', '%'.$lic_number.'%');
        }
        if ($account = I('account', '')) {
            $where['u.account'] = array('like', '%'.$account.'%');
        }
        if ($name = I('name', '')) {
            $where['c.name'] = array('like', '%'.$name.'%');
        }
        if ($real_name = I('real_name', '')) {
            $where['u.real_name'] = array('like', '%'.$real_name.'%');
        }
        if ($phone = I('phone', '')) {
            $where['u.phone'] = array('like', '%'.$phone.'%');
        }

        $group_res = $db
            ->join('left join coal_company c on c.id = t.owner_id')
            ->join('left join coal_users u on u.company_id = c.id and u.is_admin = 1') // 管理员
            ->where($where)
            ->group('t.owner_id')
            ->select();
        $count = count($group_res);

        $page_num  = I('pageCurrent') - 1;
        $page_size = I('pageSize', 2);

        $data = $db
            ->field('u.id, u.account, u.real_name, u.phone, u.create_time, count(t.owner_id) num, 
            c.name as company_name,c.province,c.city,c.area,c.detail')
            ->join('left join coal_company c on c.id = t.owner_id')
            ->join('left join coal_users u on u.company_id = c.id and u.is_admin = 1')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('t.create_time desc, t.id desc')
            ->group('t.owner_id')
            ->select();

        foreach ($data as $key => $value) {
            // 所有车辆车牌

            // $data[$key]['truck_lic_numbers'] = $lic_numbers;

            // 判断是不是车主（车为0或车为1且没审核的就不是车主）
            // $is_trucker = 1;
            // // if ($value['num'] == 0 || ($value['num'] == 1 && $value['is_passed'] == 0)) { // 不可能有0辆
            // if ($value['num'] == 1 && $value['is_passed'] == 0) {
            //     $is_trucker = 0;
            // }
            // $data[$key]['audit_state'] = $is_trucker;


            $option1 = "{
                data:{id:'".$value['id']."'},
                url:'".U('pass')."',
                confirmMsg:'确定要通过吗？'
                }";
            $option2 = "{
                data:{id:'".$value['id']."'},
                url:'".U('refuse')."',
                confirmMsg:'确定要删除吗？'
                }";
            $option3 = "{
                id:'trucker_detail',
                url:'".U('truckerDetail')."',
                data:{id:'".$value['id']."'},
                type:'get',
                height:'800',
                width: '500'
                }";
            // $dostr = create_button($option3, 'dialog', '详情');
            $dostr = '';
            // if ($is_trucker == 0) {
            //     $dostr .= create_button($option1, 'doajax', '通过');
            // }
            $dostr .= create_button($option2, 'doajax', '删除');
            // $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 获取已经车辆列表
    public function getTrucks(){
        $db = M('truck t');
        $where = array('t.is_passed' => 1);

        if ($lic_number = I('lic_number', '')) {
            $where['t.lic_number'] = array('like', '%'.$lic_number.'%');
        }
        // if ($name = I('name', '')) {
        //     $where['u.real_name'] = array('like', '%'.$name.'%');
        // }

        $count = $db
            // ->join('left join coal_admin_auth_group_access ga on ga.uid = a.id')
            ->join('left join coal_users u on u.id = t.owner_id')
            ->where($where)
            ->count();

        $page_num  = I('pageCurrent') - 1;
        $page_size = I('pageSize', 2);

        $data = $db
            ->field('t.*')
            // ->join('left join coal_admin_auth_group_access ga on ga.uid = a.id')
            ->join('left join coal_users u on u.id = t.owner_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('t.create_time desc, t.id desc')
            ->select();

        foreach ($data as $key => $value) {
            // 车牌号
            $data[$key]['lic_number'] = $value['lic_number'].'<br>内部编号：'.$value['owner_order'];
            // 车辆状态
            $data[$key]['state'] = getTruckType($value['state']);
            // 当前使用公司
            $data[$key]['use_company'] = getCompanyName($value['user_id']);

            // 得到车辆坐标
            $data[$key]['xy'] = getTruckGps($value['id']);
            $option1 = "{
                id:'Users_editTruck',
                data:{id:'".$value['id']."'},
                url:'".U('Admin/Users/editTruck')."',
                width:'800',
                height:'600'
                }";
            $option2 = "{
                data:{id:'".$value['id']."'},
                url:'".U('Admin/Users/delTruck')."',
                confirmMsg:'确定要操作吗？'
                }";

            $dostr = create_button($option1, 'dialog', '详情')
                . create_button($option2, 'doajax', '删除')
            ;
            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 编辑车辆信息
    public function editTruck(){
        if (IS_POST) {
            $post = I('post.');
            $res = M('truck')->save($post);
            if ($res !== false) {
                after_alert(array('closeCurrent' => true, 'tabid' => 'Users_trucks'));
            } else {
                alert_false();
            }
        }
        $id = I('get.id');
        $this->info = M('truck')->find($id);
        $this->display();
    }

    // 删除车辆,此功能可能涉及过大，暂时不做
    public function delTruck(){
        $id = I('id');
        // 删除之前的条件限制
        // 1、必须空车
        $res = M('truck')->where(array('state' => 1, 'id' => $id))->find();
        if (!$res) {
            alert_false('车辆必须是空车');
        }
        // 2、兼容可能的数据错误bill
        $res1 = M('bill')->where(array('truck_id' => $id, 'state' => array('in', '1,2,3,4,5')))->find();
        if ($res1) {
            alert_false('该车还有提煤单没有处理');
        }
        // 3、如果是社会车主，公司合作车主关系表删除，公司有合作不能删
        if ($res['owner_type'] == 1) {
            $tmp = M('company_trucker')->where(array('trucker_id' => $res['owner_id']))->find();
            if ($tmp) {
                alert_false('该车还有合作的公司，请解除与公司的合作，公司名字:'.getCompanyName($tmp['company']));
            }
        }

        // 删除车辆，2017年6月5日16:32:29 zgw 问题：已经拉的提煤单，车辆不能删。此逻辑待讨论
        $res2 = M('truck')->delete($id);
        // 司机回归待绑状态
        $res3 = M('driver')->where(array('truck_id' => $id))->save(array('truck_id' => 0));
        if ($res2 && $res3 !== false) {
            after_alert(array());
        } else {
            alert_false();
        }
    }

    // ----------------------------------司机---------------------------------------------------------------
    // 获取未审核的司机列表
    public function getAuditDriver(){
        $db = M('driver d');
        $where = array('d.is_passed' => 0);

        if ($lic_number = I('lic_number', '')) {
            $where['d.lic_number'] = array('like', '%'.$lic_number.'%');
        }
        if ($name = I('name', '')) {
            $where['u.real_name'] = array('like', '%'.$name.'%');
        }
        if ($phone = I('phone', '')) {
            $where['u.phone'] = array('like', '%'.$phone.'%');
        }

        $count = $db
            ->join('left join coal_users u on u.id = d.uid')
            ->join('left join coal_area a on a.relation_id = u.id and a.type = 1 and a.is_company = 0')
            ->where($where)
            ->count();

        $page_num  = I('pageCurrent') - 1;
        $page_size = I('pageSize', 2);

        $data = $db
            ->field('d.id, d.lic_number, u.real_name, u.phone, u.create_time,a.province,a.city,a.area,a.detail')
            ->join('left join coal_users u on u.id = d.uid')
            ->join('left join coal_area a on a.relation_id = u.id and a.type = 1 and a.is_company = 0')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('d.create_time desc, d.id desc')
            ->select();
        // sql();
        foreach ($data as $key => $value) {
            $data[$key] = $value;
            $option1 = "{
                data:{id:'".$value['id']."',type:2},
                url:'".U('Admin/Users/pass')."',
                confirmMsg:'确定要通过吗？'
                }";
            $option2 = "{
                data:{id:'".$value['id']."',type:2},
                url:'".U('Admin/Users/refuse')."',
                confirmMsg:'确定要拒绝吗？'
                }";

            $dostr = create_button($option1, 'doajax', '通过') . create_button($option2, 'doajax', '拒绝');
            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 获取司机列表
    public function getDrivers(){
        $db = M('driver d');
        $where = array();

        if ($lic_number = I('lic_number', '')) {
            $where['t.lic_number'] = array('like', '%'.$lic_number.'%');
        }
        if ($account = I('account', '')) {
            $where['u.account'] = array('like', '%'.$account.'%');
        }
        if ($real_name = I('name', '')) {
            $where['u.real_name'] = array('like', '%'.$real_name.'%');
        }
        if ($phone = I('phone', '')) {
            $where['u.phone'] = array('like', '%'.$phone.'%');
        }

        $group_res = $db
            ->join('left join coal_users u on u.id = d.uid')
            ->join('left join coal_truck t on t.id = d.truck_id')
            ->join('left join coal_company c on c.id = t.user_id')
            ->where($where)
            // ->group('t.owner_id')
            ->select();
        $count = count($group_res);

        $page_num  = I('pageCurrent') - 1;
        $page_size = I('pageSize', 2);

        $data = $db
            ->field('d.*, u.account, u.real_name, u.phone,u.province,u.city,u.area,u.detail, u.create_time, 
            t.lic_number as truck_lic_number,c.name as use_company')
            ->join('left join coal_users u on u.id = d.uid')
            ->join('left join coal_truck t on t.id = d.truck_id')
            ->join('left join coal_company c on c.id = t.user_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('d.create_time desc, d.id desc')
            // ->group('t.owner_id')
            ->select();
        // sql();
        foreach ($data as $key => $value) {
            // 拿到绑定的车牌号
            if (!$value['truck_id']) {
                $data[$key]['truck_lic_number'] = '没有绑定车辆';
            }
            // // 如果手势密码被锁，给解锁按钮
            // if ($value['gesture_lock'] == 1) {
            //     $value['gesture_lock'] = '锁定(<a href="'.U('Admin/Users/unlock_gesture').'" data-toggle="doajax" class="btn btn-default">解锁</a>)';
            // } else {
            //     $value['gesture_lock'] = '正常';
            // }

            // // 标明双身份
            // $is_d = M('Truck')->where(array('owner_id' => $value['uid'], 'owner_type' => 1, 'is_passed' => 1))->find();
            // // sql();
            // $is_double_identity = $is_d?'<br>(双身份)':'';
            // 拥有者
            // $data[$key]['owner_name'] = get_trucker_name($value['truck_id']).$is_double_identity;
            $data[$key]['owner_name'] = get_trucker_name($value['truck_id']);

            $option1 = "{
                id:'edit_driver',
                url:'".U('editDriver')."',
                data:{id:'".$value['id']."'},
                type:'get',
                height:'600',
                width: '800'
                }";
            $option2 = "{
                data:{id:'".$value['id']."'},
                url:'".U('delDriver')."',
                confirmMsg:'确定要删除吗？'
                }";
            $option3 = "{
                id:'driver_detail',
                url:'".U('driverDetail')."',
                data:{id:'".$value['id']."'},
                type:'get',
                height:'800',
                width: '500'
                }";
            $dostr = '';
            // $dostr = create_button($option3, 'dialog', '详情');
            $dostr .= create_button($option1, 'dialog', '编辑');
            $dostr .= create_button($option2, 'doajax', '删除');
            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 司机详情，编辑司机，编辑驾驶证
    public function editDriver(){
        if (IS_POST) {
            $post = I('post.');
            $map = array('id' => $post['id']);
            $res = D('Driver')->editData($map, $post);
            if ($res['code']) {
                if ($res['info'] !== false) {
                    $array = array('statusCode' => 200, 'message' => '处理成功！', 'closeCurrent' => true, 'tabid' => 'Users_drivers');
                    echo json_encode($array);exit;
                } else {
                    alert('处理失败！', 300);
                }
            } else {
                alert($res['info'], 300);
            }
        }
        $id = I('get.id');
        $info = M('driver')->find($id);
        $this->info = $info;
        $this->display();
    }

    // 删除司机，暂时保留
    public function delDriver(){
        $driver_id = I('id');
        if (!$driver_id) alert_illegal();
        $info = M('driver')->find($driver_id);
        // 1、删除之前的限制，不能是在运货途中
        $bill = M('bill')->where(array('driver_id' => $driver_id, 'state' => array('lt',6)))->find();
        if ($bill) {
            alert_false('该司机还有提煤单没有处理');
        }
        $res = M('driver')->delete($driver_id);
        $res1 = M('users')->where(array('id' => $info['uid']))->save(array('is_authentication' => 0));
        show_res($res && $res1);
    }
    // ----------------------------------公司---------------------------------------------------------------
    // 待审核公司
    public function getAuditCompany(){
        $db = M('company c');
        $where = array(
            'c.is_passed' => 0,
            '_string' => 'u.id is not null',
        );

        if ($account = I('account', '')) {
            $where['u.account'] = array('like', '%'.$account.'%');
        }
        if ($name = I('name', '')) {
            $where['c.name'] = array('like', '%'.$name.'%');
        }
        if ($phone = I('phone', '')) {
            $where['u.phone'] = array('like', '%'.$phone.'%');
        }
        if ($real_name = I('real_name', '')) {
            $where['u.real_name'] = array('like', '%'.$real_name.'%');
        }
        $count = $db
            ->join('left join coal_users u on u.company_id = c.id')
            ->where($where)
            ->count();

        $page_num  = I('pageCurrent') - 1;
        $page_size = I('pageSize', 2);

        $data = $db
            ->field('c.`id`,c.`is_vip`,c.`name`,c.`lic_number`,c.`lic_pic`,c.`pid`,c.`is_passed`,c.`status`,
            c.`create_time`,c.`auto_arrbill`,c.`capacity`,c.`is_produce`,c.`province`,c.`city`,c.`area`,c.`detail`,u.real_name,u.phone')
            ->join('left join coal_users u on u.company_id = c.id and u.is_admin = 1')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('c.create_time desc, c.id desc')
            ->select();
        foreach ($data as $key => $value) {
            if ($value['is_vip'] == 1) {
                $data[$key]['name'] = '<span style="color:red">V</span>'.$value['name'];
            }
            if (!$value['lic_number'] && $value['pid']) {
                $parent_company = M('company')->find($value['pid']);
                if ($parent_company) {
                    $value['lic_number'] = '总公司：'.$parent_company['name'];
                } else {
                    $value['lic_number'] = '数据有误';
                }
            }
            $option3 = "{
                id:'company_detail',
                url:'".U('editCompany')."',
                data:{id:'".$value['id']."'},
                type:'get',
                height:'800',
                width: '800'
                }";
            $option1 = "{
                data:{id:'".$value['id']."',type:3},
                url:'".U('Admin/Users/pass')."',
                confirmMsg:'确定要通过吗？'
                }";
            $option2 = "{
                data:{id:'".$value['id']."',type:3},
                url:'".U('Admin/Users/refuse')."',
                confirmMsg:'确定要拒绝吗？'
                }";

            $dostr = create_button($option3, 'dialog', '详情').create_button($option1, 'doajax', '通过') . create_button($option2, 'doajax', '拒绝');
            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }
    // 公司列表
    public function getCompanies(){
        $db = M('company c');
        $where = array(
            'c.is_passed' => 1
        );

        if ($lic_number = I('lic_number', '')) {
            $where['c.lic_number'] = array('like', '%'.$lic_number.'%');
        }
        if ($name = I('name', '')) {
            $where['c.name'] = array('like', '%'.$name.'%');
        }
        if ($real_name = I('real_name', '')) {
            $where['u.real_name'] = array('like', '%'.$real_name.'%');
        }
        if ($phone = I('phone', '')) {
            $where['u.phone'] = array('like', '%'.$phone.'%');
        }
        // 已认领和待认领
        if (I('is_auth') == 1) {
            // 待认领
            $where['_string'] = 'u.id is null';
            $this->is_auth = I('is_auth');
        } else if (I('is_auth') == 2) {
            // 已认领
            $where['_string'] = 'u.id is not null';
            $this->is_auth = I('is_auth');
        }

        $count = $db
            ->join('left join coal_users u on u.company_id = c.id and u.is_admin = 1')
            ->where($where)
            ->count();

        $page_num  = I('pageCurrent') - 1;
        $page_size = I('pageSize', 2);

        $data = $db
            ->field('c.`id`,c.`is_vip`,c.`name`,c.`lic_number`,c.`lic_pic`,c.`pid`,c.`is_passed`,c.`status`,
            c.`create_time`,c.`auto_arrbill`,c.`capacity`,c.`is_produce`,c.`province`,c.`city`,c.`area`,c.`detail`,u.id as uid,u.real_name,u.phone')
            ->join('left join coal_users u on u.company_id = c.id and u.is_admin = 1')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('c.create_time desc, c.id desc')
            ->select();
        // sql();
        foreach ($data as $key => $value) {
            // 加V标
            if ($value['is_vip'] == 1) {
                $data[$key]['name'] = '<span style="color:red">V</span>'.$value['name'];
            }
            // 是否是自己玩的
            if (!$value['uid']) {
                $data[$key]['real_name'] = '待认领';
            }

            $option3 = "{
                id:'company_detail',
                url:'".U('editCompany')."',
                data:{id:'".$value['id']."'},
                type:'get',
                height:'800',
                width: '800'
                }";
            $option4 = "{
                id:'company_auth_audit',
                url:'".U('authAuditDetail')."',
                data:{id:'".$value['id']."'},
                type:'get',
                height:'800',
                width: '800'
                }";
            $option5 = "{
                data:{id:'".$value['id']."'},
                url:'".U('delCompany')."',
                confirmMsg:'确定要删除吗？'
                }";
            $dostr = create_button($option3, 'dialog', '详情');
            $dostr .= create_button($option4, 'dialog', '认证审核');
            if ($value['status'] == 1) {
                $option2 = "{
                data:{id:'".$value['id']."',status:0},
                url:'".U('delCompany')."',
                confirmMsg:'确定操作吗？'
                }";
                $dostr .= create_button($option2, 'doajax', '禁用');
            } else {
                $option2 = "{
                data:{id:'".$value['id']."',status:1},
                url:'".U('delCompany')."',
                confirmMsg:'确定操作吗？'
                }";
                $dostr .= create_button($option2, 'doajax', '启用');
            }
            $dostr .= create_button($option5, 'doajax', '删除');
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

    // 编辑公司
    public function editCompany(){
        if (IS_POST) {
            $post = I('post.');
            $data = array(
                'id'  => $post['id'],
                'name'  => $post['name'],
                'lic_number'  => $post['lic_number'],
                'auto_arrbill'  => $post['auto_arrbill'],
                'tender'  => $post['tender'],
                'capacity'  => $post['capacity'],
                'is_produce'  => $post['is_produce'],
                'province'  => $post['province'],
                'city'  => $post['city'],
                'area'  => $post['area'],
                'detail'  => $post['detail'],
            );
            if(!empty($post['lic_pic'])){
                $data['lic_pic']=$post['lic_pic'];
            }
            $res = M('company')->save($data);
            if($post['gps']){
                update_company_gps($post['gps'], $post['id']);
            }
            if ($res !== false) {
                $array = array('statusCode' => 200, 'message' => '处理成功！', 'closeCurrent' => true, 'tabid' => 'Users_companies');
                echo json_encode($array);exit;
            } else {
                alert('处理失败！', 300);
            }
        }
        $id = I('get.id');
        $info = M('company')->field('add_gps', true)->where(array('id' => $id))->find();
        $info['add_gps'] = getComapnyGps($id);
        if(empty($info['add_gps']['x'])){
            $info['add_gps']=array(
                'x'=>'1',
                'y'=>'1'
            );
        }

        $user_info = M('users')->where(array('company'=>$id, 'is_admin' => 1))->find();
        $data = array(
            'info'  => $info,
            'user_info'  => $user_info,
            'province'  => get_provice(),
            'cities'  => get_shiqu($info['province']),
            'area'  => get_diqu($info['province'],$info['city']),
        );
//        var_dump($info);die();
        $this->assign($data);
        $this->display();
    }

    // 认证审核
    public function authAuditDetail(){
        if (IS_POST) {
            $post = I('post.');
            $data = array(
                'id'  => $post['id'],
            );
            if($post['deal1']){
                $data['ys_is_passed'] = $post['deal1'];
            }
            if($post['deal2']){
                $data['sf_is_passed'] = $post['deal2'];
            }
            $res = M('company')->save($data);
            if ($res !== false) {
                $array = array('statusCode' => 200, 'message' => '处理成功！', 'closeCurrent' => true, 'tabid' => 'Users_companies');
                echo json_encode($array);exit;
            } else {
                alert('处理失败！', 300);
            }
        }
        $id = I('get.id');
        $company = M('company')->field('add_gps', true)->where(array('id'=>$id))->find();
        $ret['lic_number']['src']        = check_remote_file_exists(getTrueImgSrc($company['lic_pic']))?getTrueImgSrc($company['lic_pic']):'';
        $ret['lic_number']['is_passed'] = $company['is_passed'];
        $ret['ys_lic']['src']             = check_remote_file_exists(getTrueImgSrc($company['ys_lic']))?getTrueImgSrc($company['ys_lic']):'';
        $ret['ys_lic']['is_passed']      = $company['ys_is_passed'];
        $ret['sf_lic']['src']             = check_remote_file_exists(getTrueImgSrc($company['sf_lic']))?getTrueImgSrc($company['sf_lic']):'';
        $ret['sf_lic']['is_passed']      = $company['sf_is_passed'];
        $this->info = $company;
        $this->assign($ret);
        $this->display();
    }

    // 删除公司
    public function delCompany(){
        $company_id = I('id');
        $status = I('status');
        if (!$company_id) alert_illegal();
        // $info = M('company')->find($company_id);

        // $res = M('company')->where(array('id' => $company_id))->save(array('status' => $status));
        $res = M('company')->delete($company_id);
        // $res1 = M('users')->where(array('id' => $info['uid']))->save(array('is_authentication' => 0));
        show_res($res);
    }
}