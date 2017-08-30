<?php
//司机和车主的关系模型
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/3/22
 * Time: 17:33
 */
namespace Common\Logic;
//use Common\Model\BaseModel;
class DriverTruckerLogic extends \Think\Model{
  //司机车主绑定
    function band($driver_id,$trucker_id){
        //验证司机和车主的权限
        $is_driver=D('Driver','Logic')->checkAuth($driver_id);
        if(!$is_driver['code']){
            backJson($is_driver);
        }
        $is_trucker=D('Truck','Logic')->checkAuth($trucker_id);
        if(!$is_trucker['code']){
            backJson($is_trucker);
        }
        $data['trucker_id']=$trucker_id;
        $data['driver_id']=$driver_id;
        $exist=M('driver_trucker')->where(array('trucker_id'=>$trucker_id,'driver_id'=>$driver_id,'status'=>1))->find();
        if($exist){
            $ret['code']=0;
            $ret['message']='绑定申请已经提交,请勿重复提交';
            return $ret;
        }
        $res=D('Driver_Trucker')->addData($data);
        if($res){
            //发送推送给车主
            D('Push','Logic')->noticeTruckerBind($driver_id,$trucker_id);
            $ret['code']=1;
            $ret['message']='绑定申请已成功提交,等待车主通过申请';
            return $ret;
        }else{
            $ret['code']=0;
            $ret['message']='提交失败,请稍后再试';
            return $ret;
        }
    }
}