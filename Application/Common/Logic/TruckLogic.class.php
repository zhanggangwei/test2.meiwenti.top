<?php
//司机模型
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/3/22
 * Time: 17:33
 */
namespace Common\Logic;
//use Common\Model\BaseModel;
class TruckLogic extends \Think\Model{
    //认证车主的权限
    //传入ID为司机id
    function checkAuth($id){
        //只要有一辆审核过的就是车主
        $truck=M('truck')->where(array('owner_id'=>$id,'is_passed'=>1))->find();
        if($truck){
            $res['code']=1;
            $res['message']='认证成功';
            return $res;
        }
        //有车辆但是没有审核过
        $truck1=M('truck')->where(array('owner_id'=>$id))->find();
        if(!$truck1){
            $res['code']=0;
            $res['message']='车主身份没有认证';
            return $res;
        }else{
            $res['code']=0;
            $res['message']='车主没有通过审核的车辆';
            return $res;
        }
    }
    //认证
    function authentication(){
        //进行车辆认证时候第一次认证的时候要建群
        $truck=M('truck')->where(array('owner_id'=>session('user_id'),'owner_type'=>1))->find();
        $user_id=session('user_id');
        $data=I('post.');
        $data['owner_id']=$user_id;
        $res=D('Truck')->addData($data);
        if($res['code']==1){
            if(M('users')->where(array('id'=>session('user_id')))->getField('is_authentication')!=1) {
                $user['real_name'] = I('name');
                $user['is_authentication'] = 1;
            }
            if(count($truck)==0){
                vendor('Emchat.Easemobclass');
                $h=new \Easemob();
                $options ['groupname'] = I('name').'车队';
                $options ['desc'] = '这个群是'.I('name').'车队长的车队群';
                $options ['public'] = true;
                $options ['owner'] = session('user_id');
                $group_id=$h->createGroup($options);
                $user['group_id'] = $group_id['data']['groupid'];
            }
            M('users')->where(array('id' => session('user_id')))->save($user);
            return array('code'=>1,'message'=>'车辆认证成功');
        }else{
            return $res;
        }
    }
    //绑定司机
    function bindDriver($truck_id,$driver_id,$user_id,$work_time){
        //验证车辆是否是自己拥有者的且已经通过审核
        //验证司机是否还没有绑定车辆，并且认证通过
        $truck=M('truck')->where(array('owner_id'=>$user_id,'is_passed'=>1,'id'=>$truck_id))->find();
        if(!$truck){
            $ret['code']=0;
            $ret['message']='该车辆不是你的,无权绑定';
            return $ret;
        }
        $driver=M('driver')->where(array('uid'=>$driver_id,'is_passed'=>1))->find();
        if(!$driver){
            $ret['code']=0;
            $ret['message']='司机信息有误';
            return $ret;
        }elseif($driver['truck_id']){
            $ret['code']=0;
            $ret['message']='司机信息有误';
            return $ret;
        }
        //进行绑定
        $data['truck_id']=$truck_id;
        $data['work_time']=$work_time;
        $res=M('driver')->where(array('uid'=>$driver_id))->save($data);
        //如果driver_trucker表里面有申请则进行删除
        $driver_trucker=M('driver_trucker')->where(array('driver_id'=>$driver_id,'trucker_id'=>$user_id,'status'=>1))->find();
        if($driver_trucker){
            M('driver_trucker')->delete($driver_trucker['id']);
        }
        if($res!==false){
            //推送消息给司机
            D('Push','Logic')->noticeDriverBind($driver_id,$user_id,$truck_id);
            //添加到司机到群里面
            $group_id=M('users')->where(array('id'=>session('user_id')))->getField('group_id');
            vendor('Emchat.Easemobclass');
            $h=new \Easemob();
            $h->addGroupMember($group_id,$driver_id);
            $ret['code']=1;
            $ret['message']=$group_id;
            return $ret;
        }
    }
    //获取车辆信息
    //包括车主,司机,挂靠物流公司,使用的物流公司,拉运吨数,次数,服役时间
    function getDetail($id){
        $truck=M('truck')->where(array('id'=>$id))->find();
        //车辆照片
        $data['photo']=$truck['photo']==40?C('OSS_WEB_URL').'public/truck_logo.png':C('OSS_WEB_URL').$truck['photo'];
        //司机
        $drivers=M('driver')->join('coal_users ON coal_driver.uid=coal_users.id')->where(array('truck_id'=>$id))->field('real_name as name,coal_users.id')->select();
        $data['drivers']=$drivers;
        //车主
        if($truck['owner_type']==1){
            $trucker=M('truck')->join('coal_users ON coal_truck.owner_id=coal_users.id')->where(array('coal_truck.id'=>$id))->field('real_name as name,coal_users.id')->find();
        }else{
            $trucker=M('truck')->join('coal_company ON coal_truck.owner_id=coal_company.id')->where(array('coal_truck.id'=>$id))->field('name,coal_company.id')->find();
        }
        $data['owner']=$trucker;
        //挂靠的物流公司
        $anchored=M('truck')->join('coal_company ON coal_truck.anchored_id=coal_company.id')->where(array('coal_truck.id'=>$id))->field('name,is_anchored,coal_company.id')->find();
        $data['anchored']=$anchored;
        //使用的物流公司
        $user=M('truck')->join('coal_company ON coal_truck.user_id=coal_company.id')->where(array('coal_truck.id'=>$id))->field('name,is_comperation,coal_company.id')->find();
        $data['user']=$user;
        //拉运吨数
        $bill=M('bill')->where(array('truck_id'=>$id,'state'=>6))->select();
        $data['total_times']=count($bill);
        $total_w=0;
        foreach ($bill as $key=>$value){
            $total_w=$total_w+$value['end_w'];
        }
        $data['total_w']=$total_w;
        //服役时间
        $data['work_long']=getday($truck['create_time']);
        return $data;
    }
    //查看车辆是否有司机可用
    function is_driver($id){
        $driver=M('driver')->where(array('truck_id'=>$id))->select();
        foreach ($driver as $key=>$value){
            if(D('Driver','Logic')->is_work($value['uid'])){
                return true;
            }
        }
        return false;
    }

    // +++++++ Truck Logic Model ++++++++++++++++++++++++++
    //zgw function start

    /**
     * 车辆审核通过
     * @param $ids 车辆id，多个，一维数组
     * @return bool
     */
    public function auditPass($ids = array()){
        if ($ids) {
            $res = M('truck')->where(array('id' => array('in', $ids)))->save(array('is_passed' => 1));
            // 得到手机号,推送
            foreach ($ids as $val) {
                $tmp = M('truck')->find($val);
                // $tmp = get_trucker_name_and_phone($val);// 2017年5月24日11:41:45 zgw 现在只有公司自有车要审核，所以只推送给总公司管理员
                if ($tmp['owner_type'] == 2) {
                    D('Push','Logic')->noticeManager('[煤问题]车辆审核结果', '您车牌为'.$tmp['lic_number'].'的车辆已经通过审核，请知悉', $tmp['owner_id']);
                }
            }

            if ($res !== false) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 是否可以接收车辆
     * @param $truck_id
     * @return bool
     */
    // public function isCanTakeOver($truck_id){
    //     $info = M('truck')->where(array('id' => $truck_id, 'is_passed' => 1, 'anchored_id' => 0, 'user_id' => 0, 'is_anchored' => 0))->find();
    //     return $info?true:false;
    // }


    public function takeOver($truck_id, $others = array()){
        $data = array(
            'id'      => $truck_id,
            'anchored_id'     => session('company_id'),
            'user_id'          => session('company_id'),
            'is_anchored'     => 1
        );
        $data = array_merge($data, $others);
        $res = D('Truck')->save($data);
        return $res?true:false;
    }

    /**
     * 车辆是否可以派单
     * @param int $truck_id    车辆id
     * @param int $company_id  公司id
     * @return array
     */
    public function isDispatch($truck_id,$company_id=0){
        if (!$company_id) $company_id = session('company_id');

        // 车辆可用
        $truck = M('truck')->find($truck_id);
        if (!$truck) {
            $code = 2;
            $message = '车辆非法';
            return array('code' =>  $code, 'message' => $message);
        }
        if ($truck['is_passed'] != 1) {
            $code = 3;
            $message = '车辆未审核';
            return array('code' =>  $code, 'message' => $message);
        }
        if ($truck['state'] != 1) {
            $code = 4;
            $message = '车辆不是空车';
            return array('code' =>  $code, 'message' => $message);
        }
        if ($truck['is_work'] != 1) {
            $code = 5;
            $message = '车辆处于非接单状态';
            return array('code' =>  $code, 'message' => $message);
        }
        if ($truck['user_id'] != $company_id) {
            $code = 6;
            $message = '['.$truck['lic_number'].']车不是['.getCompanyName($company_id).']的';
            return array('code' =>  $code, 'message' => $message);
        }
        if ($truck['is_comperation'] != 1) {
            $code = 7;
            $message = '合作车辆未确认';
            return array('code' =>  $code, 'message' => $message);
        }
        // 司机可用
        $driver = M('driver')->where(array('truck_id' => $truck_id, 'is_work' => 1, 'work_time' => array('in', array(1,get_work_time_type()))))->find();
        if (!$driver) {
            $code = 8;
            $message = '车辆没有可用司机,可能是没有在工作时间或休息';
            return array('code' =>  $code, 'message' => $message);
        }
        return array('code' => 1, 'message' => '车辆可用');
    }

    // 等待中的车辆列表
    public function getWaitTrucks($company){
        $trucks = M('truck')->where(array('is_passed' => 1, 'state' => 1, 'user_id' => $company,'is_comperation'=>1))->order('last_time asc')->select();
        $res = array();
        foreach ($trucks as $key => $value) {
            $is_dispatch = D('Truck', 'Logic')->isDispatch($value['id']);
            if ($is_dispatch['code'] == 1) {
                $res[] = $value;
            }
        }
        return $res;
    }
    //zgw function end
    // +++++++++++++++++++++++++++++++++
}