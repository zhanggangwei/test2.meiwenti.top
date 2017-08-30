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
class DriverLogic extends \Think\Model{
    //认证司机的权限
    //传入ID为司机id
    function checkAuth($id){
        $driver=M('driver')->where(array('uid'=>$id,'is_passed'=>1))->find();
        if($driver){
            $res['code']=1;
            $res['message']='认证成功';
            return $res;
        }else{
            $res['code']=0;
            $res['message']='司机身份没有认证';
            return $res;
        }
    }
    //认证
    function authentication(){
        $data=I('post.');
        $data['uid']=session('user_id');
        $data['user_name']=I('name');
        if(M('users')->where(array('id'=>session('user_id')))->getField('is_authentication')!=1){
                if(!I('name')){
                    return array('code'=>0,'message'=>'姓名不能为空');
                }
                $user['real_name']=I('name');
                $user['is_authentication']=1;
                $res=D('Driver')->addData($data);
                if($res['code']==1){
                    M('users')->where(array('id'=>session('user_id')))->save($user);
                    return array('code'=>1,'message'=>'司机认证成功');
                }else{
                    return $res;
                }

            }else{
                //检查是否已经认证过司机
                $driver=M('driver')->where(array('uid'=>session('user_id')))->find();
                if($driver){
                    return array('code'=>0,'message'=>'您已经认证过司机不能重复认证');
                }else{
                    $user['is_authentication']=1;
                    $res=D('Driver')->addData($data);
                    if($res['code']==1){
                        M('users')->where(array('id'=>session('user_id')))->save($user);
                        return array('code'=>1,'message'=>'司机认证成功');
                    }else{
                        return $res;
                    }
            }

        }
    }
    /**
     * 解雇司机
     * @param $employer_id  车主id或者公司车公司管理员的id,可以是子公司的吗？应该是可以，待测试 2017年5月9日18:50:59 zgw
     * @param $driver_id    司机user表的id
     */
    function fire($employer_id,$driver_id){
        //获取司机绑定的车辆
        $truck=M('truck')->join('__DRIVER__ ON __DRIVER__.truck_id=__TRUCK__.id')
            ->where(array('coal_driver.uid'=>$driver_id))
            ->field('coal_truck.id,coal_truck.owner_id,coal_truck.state,coal_truck.owner_type')->find();

        //查看雇主的类型是车主还是公司
        $user=M('users')->where(array('id'=>$employer_id))->find();
        //查看司机绑定的车辆是否是雇主的
        switch ($user['type']){
            case 1://车主身份
                $owner_name=M('users')->where(array('id'=>$employer_id))->getField('real_name');
                if($employer_id!=$truck['owner_id']or$truck['owner_type']!=1){
                    $res['code']=0;
                    $res['message']='车主身份异常无权解雇';
                    backJson($res);
                }
                break;
            case 2://企业身份
                $owner_name=M('company')->where(array('id'=>$user['company_id']))->getField('name');
                if($user['company_id']!=$truck['owner_id']or$truck['owner_type']!=2){
                    $res['code']=0;
                    $res['message']='车主身份异常无权解雇1';
                    backJson($res);
                }

        }
        //查看司机绑定的车辆是不是正在拉货
        // 2017年6月21日17:51:20 zgw edit 原因：小薛和老张讨论结果，1、司机有单(state >=2)，不能解绑。2、车辆有单时，只有一个司机时不能解绑。
        $bill = M('bill')->where(array('driver_id' => $driver_id, 'state' => array('lt', 6)))->find();
        if ($bill) {
            $res['code']=0;
            $res['message']='该司机有正在进行的单';
            backJson($res);
        }
        $bill1 = M('bill')->where(array('truck_id' => $truck['id'], 'state' => array('lt', 6)))->find(); // 车辆有没有单
        $drivers_count = M('driver')->where(array('truck_id' => $truck['id'], 'is_passed' =>1))->count();
        if((in_array($truck['state'],[2,3]) || $bill1) && $drivers_count == 1){ // 不管车辆处于何种状态，都能解雇司机吗？空车可以，坏车也可以吗？可以。
            $res['code']=0;
            $res['message']='车辆正在使用中且司机只有一个，不能解雇';
            backJson($res);
        }
        $data['truck_id']=0;
        $result=M('driver')->where(array('uid'=>$driver_id))->save($data);
        if($result!==false){
            //司机删除群聊
            $res['code']=1;
            $res['message']='司机解绑成功';
            switch ($user['type']){
                case 1://车主身份
                    $group_id=M('users')->where(array('id'=>$employer_id))->getField('group_id');
                    vendor('Emchat.Easemobclass');
                    $h=new \Easemob();
                    $h->deleteGroupMember($group_id,$driver_id);
                    D('Push','Logic')->noticeDriverFire($driver_id,$owner_name);
                    backJson($res);
                    break;
                case 2://企业身份
                    $group_id=M('company')->where(array('id'=>$employer_id))->getField('group_id');
                    vendor('Emchat.Easemobclass');
                    $h=new \Easemob();
                    $h->deleteGroupMember($group_id,$driver_id);
                    D('Push','Logic')->noticeDriverFire($driver_id,$owner_name);
                    return $res;
            }

        }else{
            $res['code']=0;
            $res['message']='司机解绑失败';
            backJson($res);
        }
    }
    //获取详情
    function getDetail($id){
        $driver=M('driver')->join('coal_users ON coal_users.id=coal_driver.uid')->where(array('coal_driver.uid'=>$id))->find();
        if(!$driver){
            $res['code']=0;
            $res['message']='参数有误';
            backJson($res);
        }
        $res['name']=$driver['real_name'];
        $res['lic_number']=$driver['lic_number'];
        $res['name']=$driver['real_name'];
        $res['phone']=$driver['phone'];
        $res['photo']=$driver['photo']==40?C('OSS_WEB_URL').'public/driver_logo.png':C('OSS_WEB_URL').$driver['photo'];
        $arr=array('1'=>'全天',2=>'白班',3=>'夜班');
        $res['work_time']=$arr[$driver['work_time']];
        return $res;
    }
    //查看司机是否处于上班状态
    //没有休息且处于上班时间
    //工作返回true
    function is_work($id){
        $driver=M('driver')->where(array('uid'=>$id))->find();
        if(!$driver['is_work']){
            return false;
        }else{
            switch ($driver['work_time']){
                case 1:return true;break;
                case 2:if(date('G')>8 and date('G')<20) return true;else return false;break;
                case 3:if(date('G')>8 and date('G')<20) return false;else return true;break;
            }
        }
    }

    // 后台审核通过
    function auditPass($ids){
        $res = M('driver')->where(array('id' => array('in', $ids)))->save(array('is_passed' => 1));
        if ($res !== false) {
            return true;
        } else {
            return false;
        }
    }
    // 司机排班
    function changeWorkTime($driver_id, $type){
        // 检验是否有正在拉的单
        $res1 = M('bill')->where(array('driver_id' => $driver_id, 'state' => array('lt', 5)))->find();
        if ($res1) {
            return array('code' => 0, 'message' => '司机还有没完成的提煤单，请处理后再操作');
        }
        // 改变数据
        $res = M('driver')->where(array('uid' => $driver_id))->save(array('work_time' => $type));
        if ($res !== false) {
            return array('code' => 1, 'message' => '处理成功');
        } else {
            return array('code' => 0, 'message' => '处理失败');
        }
    }
}