<?php
/**
 * Created by PhpStorm.
 * 所有车辆控制器
 * User: zgw
 * Date: 2017-05-10
 * Time: 2:48
 */
namespace Vip\Controller;

class AllTrucksController extends CommonController {

    // 车辆列表页面
    public function truckList(){
        $cid = session('company_id');
        $this->sub_company = M('company')->field('id,name')->where('pid='.$cid)->select();
        $this->is_zong = is_parent_company($cid)?0:1;
        $this->display();
    }

    // 车辆列表
    public function getTrucks(){
        $db = M('truck t');

        $where = array(
            't.is_passed' => 1,
            't.is_comperation' => 1
        );

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        // 车牌号
        if ($lic_number = trim(I('lic_number', ''))) {
            $where['t.lic_number'] = array('like', '%'.$lic_number.'%');
        }
        // 车小号，内部编号
        if ($owner_order = trim(I('owner_order', ''))) {
            $where['t.owner_order'] = array('like', '%'.$owner_order.'%');
        }

        // 手机或司机名字
        if ($name = trim(I('driver_name', '')) || $phone = trim(I('driver_phone', ''))) {
            if ($name) {
                $users = M('users')->field('id')->where(array('real_name' => array('like', '%'.I('driver_name').'%')))->select();
            }
            if ($phone) {
                $users = M('users')->field('id')->where(array('phone' => array('like', '%'.I('driver_phone').'%')))->select();
            }

            $truck_ids = array(0);
            if ($users) {
                $uids = array(0);
                foreach ($users as $val) {
                    $uids[] = $val['id'];
                }
                $trucks = M('driver')->field('truck_id')->where(array('uid' => array('in',$uids)))->select();
                if ($trucks) {
                    foreach ($trucks as $val) {
                        if ($val['truck_id']) {
                            $truck_ids[] = $val['truck_id'];
                        }
                    }
                }
            }
            $where['t.id'] = array('in', $truck_ids);
        }

        // 总公司筛选
        $cid = session('company_id');
        $ids = array($cid);
        if (is_parent_company($cid)) {
            $sub_ids = get_subcompany_ids($cid);
            $ids = array_merge($ids, $sub_ids);
        }
        if ($company_id = I('sub_company', 0)) {
            $ids = $company_id;
        }
        $where['t.user_id'] = array('in', $ids);

        // 来源筛选
        $from = I('from',0);
        switch ($from) {
            case 1:
                // 车主
                $where['t.anchored_id'] = session('company_id');
                $where['t.owner_type'] = 1;
                break;
            case 2:
                // 合作公司
                $where['t.anchored_id'] = array('neq',session('company_id'));
                break;
            case 3:
                // 自有车辆
                $where['t.anchored_id'] = session('company_id');
                $where['t.owner_type'] = 2;
                break;
            default:
                break;
        }

        $count = $db
            ->where($where)
            ->count();

        $data = $db
            ->field('t.*')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->select();
        // sql();
        // dump_text($data);

        //集运站
        $jiyun_res = M('jiyun')->select();
        $jiyun_array = array();
        foreach ($jiyun_res as $val) {
            $jiyun_array[$val['order']] = $val['name'];
        }
        foreach ($data as $key => $value) {
            // 车的状态
            $data[$key]['state'] = getTruckType($value['state']);

            // 获得绑定的司机
            $data[$key]['driver'] = get_drivers($value['id']);

            // 来源
            $data[$key]['from'] = get_trcuk_from($value['id']);

            // 通过车辆id得到车主的名字
            $is_sub_company = '';
            if ($value['owner_type'] == 2 && in_array($value['owner_id'], $sub_ids)) {
                $is_sub_company = '(子公司)';
            }
            $data[$key]['owner'] = get_trucker_name($value['id']).$is_sub_company;

            // 集运站
            $jiyun_arr = explode(',',$value['jiyun']); // 分配了的集运站
            $jiyun_str = '';
            foreach ($jiyun_arr as $k => $v){
                if($jiyun_array[$v]){
                    $jiyun_str .= '&nbsp;'.$jiyun_array[$v];
                }
            }
            $data[$key]['jiyun'] = $jiyun_str==''?'没有分配集运站':$jiyun_str;

            // 车牌号+车小号
            $data[$key]['lic_number'] = $value['lic_number'].'<br>('.$value['owner_order'].')';

            // 合作时间，有点复杂，待做


            // 车辆今日拉运车次
            $hour = date('H');
            if ($hour >= 18) {
                $date_point = date('Y-m-d 18:00:00');
            } else {
                $date_point = date('Y-m-d 18:00:00',strtotime('- 1 day'));
            }
            $finish_times = M('bill b')
                ->join('left join coal_link_yitai y on y.bill_id = b.id')
                ->where(array('b.state' => 6,'b.truck_id' => $value['id'], 'y.dis_time' => array('egt', $date_point)))
                ->count();
            $doing_times = M('bill b')
                ->join('left join coal_link_yitai y on y.bill_id = b.id')
                ->where(array('b.state' => array('lt', 6),'b.truck_id' => $value['id'], 'y.dis_time' => array('egt', $date_point)))
                ->count();
            $times = $finish_times + $doing_times;
            if ($times > 0) {
                $data[$key]['today_times'] = '共计'.$times.'车（已完成：'.$finish_times.'，未完成：'.$doing_times.'）';
            } else {
                $data[$key]['today_times'] = '共计'.$times.'车';
            }

            $dostr = '';
            if ($value['state'] == 4) {
                $option = "{
                type:'get',
                data:{id:'".$value['id']."',state:1},
                url:'".U('stopTruck')."',
                confirmMsg:'确认操作吗？'
                }";
                $do_title = '启用车辆';
                $dostr .= create_button($option, 'doajax', $do_title);
            } else if ($value['state'] == 1) {
                $option = "{
                type:'get',
                data:{id:'".$value['id']."',state:4},
                url:'".U('stopTruck')."',
                confirmMsg:'确认操作吗？'
                }";
                $do_title = '暂停车辆';
                $dostr .= create_button($option, 'doajax', $do_title);
            } else {
                // state = 2 or state = 3

                $tmp = M('cron_change_truck_state')->where(array('truck_id' => $value['id'], 'state' => 0))->find();
                if ($tmp) {
                    $data[$key]['state'] .= '<br>(该单结束后会暂停车辆)';
                    $option = "{
                    type:'get',
                    data:{id:'".$value['id']."'},
                    url:'".U('delPreStop')."',
                    confirmMsg:'确认操作吗？'
                    }";
                    $do_title = '取消预暂停';
                    $dostr .= create_button($option, 'doajax', $do_title);
                } else {
                    $option = "{
                    type:'get',
                    data:{id:'".$value['id']."',dotype:1},
                    url:'".U('cronChangeTruckState')."',
                    confirmMsg:'确认操作吗？'
                    }";
                    $do_title = '预暂停车辆';
                    $dostr .= create_button($option, 'dialog', $do_title);
                }
            }

            $option1 = "{
                id:'AllTruck_appoint',
                data:{id:'".$value['id']."'},
                url:'".U('appoint')."'
                }";
            $dostr .= create_button($option1, 'dialog', '指定集运站');

            // 编辑
            $option2 = "{
                id:'AllTruck_editTruck',
                data:{id:'".$value['id']."'},
                url:'".U('editTruck')."',
                type:'get',
                width:'800',
                height:'600',
                }";
            $dostr .= create_button($option2, 'dialog', '编辑');
            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 车辆组织架构图
    public function truckOrgChart(){
            $data=array('name'=>'拥有车辆');
            //自有车辆
        {
            $owner_trucks=M('truck')
                ->where(array('owner_id'=>session('company_id'),'user_id'=>session('company_id'),'owner_type'=>2))
                ->field('id,lic_number as name')
                ->select();
            foreach ($owner_trucks as $key =>$value){
                $driver1=M('driver d')
                    ->join('coal_users u on d.uid=u.id')
                    ->where(array('d.truck_id'=>$value['id']))
                    ->field('u.real_name as name,u.phone as 手机号')
                    ->select();
                foreach ($driver1 as $k=>$v){
                    $driver1[$k]['name']='司机:'.$v['name'];
                }
                if($driver1){
                    $owner_trucks[$key]['children']=$driver1;
                }else{
                    $owner_trucks[$key]['children']=array(0=>array('name'=>'还未绑定司机'));
                }

            }
            $data['children'][0]=array('name'=>'自有车辆','children'=>$owner_trucks);
        }
            //合作的车主车辆
        {
            $anchored_truckers=M('company_trucker ct')
                ->join('coal_users u on ct.trucker_id=u.id')
                ->where(array('ct.company'=>session('company_id'),'status'=>1))
                ->field('u.real_name as name,u.id as id,u.phone as 手机号')
                ->select();
            foreach ($anchored_truckers as $key =>$value){
                $anchored_trucks=M('truck')->where(array('owner_id'=>$value['id'],'anchored_id'=>session('company_id'),'user_id'=>session('company_id'),'owner_type'=>1))->field('id,lic_number as name')->select();
                foreach ($anchored_trucks as $k =>$v){
                    $driver2=M('driver d')
                        ->join('coal_users u on d.uid=u.id')
                        ->where(array('d.truck_id'=>$v['id']))
                        ->field('u.real_name as name,u.phone as 手机号')
                        ->select();
                    foreach ($driver2 as $kk=>$vv){
                        $driver2[$kk]['name']='司机:'.$vv['name'];
                    }
                    if($driver2){
                        $anchored_trucks[$k]['children']=$driver2;
                    }else{
                        $anchored_trucks[$k]['children']=array(0=>array('name'=>'还未绑定司机'));
                    }
                }
                $anchored_truckers[$key]['children']=$anchored_trucks;
            }
            $data['children'][1]=array('name'=>'合作车主','children'=>$anchored_truckers);
        }
            //合作的公司的车辆
        {
            $comperation_company1 = M('company_company cc')
                ->join('left join coal_company c on cc.company2=c.id')
                ->join('right join coal_users u on u.company_id=cc.company1')
                ->where(array('cc.company1' => session('company_id'), 'cc.status' => 1, 'u.is_admin' => 0))
                ->field('c.name as name,cc.company2 as id,u.real_name as 管理员,u.phone as 手机号')
                ->select();
            foreach ($comperation_company1 as $key => $value) {
                $comperation_trucks1= M('truck')
                    ->where(array('owner_id' => $value['id'], 'anchored_id' => $value['id'], 'user_id' => session('company_id'), 'is_comperation' => 1))
                    ->field('id,lic_number as name')
                    ->select();
                //查找司机
                foreach ($comperation_trucks1 as $k =>$v){
                    $driver3=M('driver d')
                        ->join('coal_users u on d.uid=u.id')
                        ->where(array('d.truck_id'=>$v['id']))
                        ->field('u.real_name as name,u.phone as 手机号')
                        ->select();
                    foreach ($driver3 as $kk=>$vv){
                        $driver3[$kk]['name']='司机:'.$vv['name'];
                    }
                    if($driver3){
                        $comperation_trucks1[$k]['children']=$driver3;
                    }else{
                        $comperation_trucks1[$k]['children']=array(0=>array('name'=>'还未绑定司机'));
                    }
                }

                $comperation_company1[$key]['children'] =$comperation_trucks1;
            }
            $comperation_company2 = M('company_company cc')
                ->join('coal_company c on cc.company1=c.id')
                ->where(array('cc.company2' => session('company_id'), 'cc.status' => 1))
                ->field('c.name as name,cc.company1 as id')
                ->select();
            foreach ($comperation_company2 as $key => $value) {
                $comperation_trucks2=M('truck')
                    ->where(array('owner_id' => $value['id'], 'anchored_id' => $value['id'], 'user_id' => session('company_id'), 'is_comperation' => 1))
                    ->field('id,lic_number as name')
                    ->select();

                //查找司机
                foreach ($comperation_trucks2 as $k =>$v){
                    $driver4=M('driver d')
                        ->join('coal_users u on d.uid=u.id')
                        ->where(array('d.truck_id'=>$v['id']))
                        ->field('u.real_name as name,u.phone as 手机号')
                        ->select();
                    foreach ($driver4 as $kk=>$vv){
                        $driver4[$kk]['name']='司机:'.$vv['name'];
                    }
                    if($driver4){
                        $comperation_trucks2[$k]['children']=$driver4;
                    }else{
                        $comperation_trucks2[$k]['children']=array(0=>array('name'=>'还未绑定司机'));
                    }
                }
                $comperation_company2[$key]['children'] =$comperation_trucks2;

            }
            $comperation_company = array_merge($comperation_company1, $comperation_company2);
        }
            $data['children'][2]=array('name'=>'合作公司','children'=>$comperation_company);
            $this->assign('data',json_encode($data,false));
            $this->display();

    }
    public function carMap(){
        $this->display();
    }
    // 暂停车辆
    public function stopTruck(){
        $id = I('param.id');
        $state = I('param.state');
        if ($id) {
            $truck = M('truck')->find($id);
            if (!$truck) {
                alert_false('车辆异常');
            }
            $res = M('truck')->save(array('id' => $id, 'state' => $state));
            if ($res !== false) {
                after_alert(array('refresh' => true));
            } else {
                alert_false();
            }

        } else {
            alert_illegal();
        }
    }

    // 预暂停车辆
    public function cronChangeTruckState(){
        if (IS_POST) {
            $post = I('post.');
            $id = $post['id'];
            $truck = M('truck')->find($id);
            if ($truck['state'] == 2 || $truck['state'] == 3) {
                // 是否有正在进行的车辆任务
                $tmp = M('cron_change_truck_state')->where(array('truck_id' => $id, 'state' => 0))->find();
                if ($tmp) {
                    alert_false('该车还有未执行的计划');
                }
                // 正在进行中的车辆，暂停任务到本次订单结束后
                $data = array(
                    'truck_id'  => $id,
                    'truck_state'  => 4,
                    'reason'  => I('reason'),
                    'creator'  => session('user_id'),
                    'create_time'  => get_time(),
                );
                $res = M('cron_change_truck_state')->add($data);
                if ($res) {
                    after_alert(array('closeCurrent' => true, 'tabid' => 'AllTrucks_truckList'));
                } else {
                    alert_false();
                }
            } else {
                alert_false('参数错误');
            }
        }
        $id = I('id');
        $dotype = I('dotype');
        if (!$id || !$dotype) {
            alert_false('参数错误');
        }
        $this->id = I('id');
        $this->display();
    }

    // 取消预暂停
    public function delPreStop(){
        $id = I('id');
        if ($id) {
            $tmp = M('cron_change_truck_state')->where(array('truck_id' => $id, 'state' => 0))->find();
            if ($tmp) {
                $res = M('cron_change_truck_state')->delete($tmp['id']);
                if ($res) {
                   after_alert(array('reload' => true));
                } else {
                    alert_false();
                }
            } else {
                alert_false('没有任务记录');
            }
        } else {
            alert_illegal();
        }
    }

    // 指定集运站
    public function appoint(){
        //如果车辆正在拉货的时候就不能更改了
        $truck=M('truck')->where(array('id'=>I('id')))->find();
        if($truck['state']!=1){
            alert('车辆正在拉煤',300);
        }
        if (IS_POST) {
            $data['id']=I('id');
            $data['jiyun']=implode(',',I('jiyun'));
            $res=M('truck')->save($data);
            if($res!==false){
                after_alert(array('closeCurrent'=>true));
            }else{
                alert('编辑失败',300);
            }
        }
        $truck=M('truck')->where(array('id'=>I('id')))->find();
        $jiyun_arr=explode(',',$truck['jiyun']);
        $this->truck=$truck;
        $jiyun=M('jiyun')->select();
        foreach ($jiyun as $key=>$value){
            if(in_array($value['order'],$jiyun_arr)){
                $jiyun[$key]['is_use']=1;
            }else{
                $jiyun[$key]['is_use']=0;
            }
        }
        $this->jiyun=$jiyun;
        $this->display();
    }

    // 编辑
    public function editTruck(){
        if (IS_POST) {
            $post = I('post.');
            $data = array(
                'id'  => $post['id'],
                'owner_order'  => $post['owner_order'],
            );
            $res = M('truck')->save($data);
            if ($res !== false) {
                after_alert(array('closeCurrent' => true, 'tabid' => 'AllTrucks_truckList'));
            } else {
                alert_false();
            }
        }
        $id = I('get.id');
        $truck_info = M('truck')->find($id);
        $data = array(
            'truck_info' => $truck_info,
        );
        $this->assign($data);
        $this->display();
    }
}