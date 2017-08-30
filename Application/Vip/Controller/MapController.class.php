<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/4/10
 * Time: 14:21
 */
namespace Vip\Controller;

class MapController extends CommonController {

    public function map_view(){
        $uid  =  I('userid') ;
        $limit  =  I('limit') ;

        empty($uid)?die(json_encode(array('code'=>0,'msg'=>'参数错误'))):'';

        $data1=M()->query("SELECT  concat(x,',', y) as gps from coal_gps_history where uid = $uid  order by push_time desc  limit $limit");

        if(!empty($data1)){
            $data=array(
                'code'=>1,
                'msg'=>'获取成功!',
                'data'=>$data1,
                'uid'=>$uid
            );
            $this->assign('data',$data);
            $this->display();
        }else{
             echo "本车还没有产生轨迹!请关闭";die();
        }
    }

    // 车辆实时位置
    public function map_realTime(){
        $uid  =  I('userid') ;
        $bill_id  =  I('bill_id') ;
        $bill = M('bill')->find($bill_id);

        // 空重车状态
        if ($bill['state'] == 2 || $bill['state'] == 3) {
            $w_state = 'kc';
        } else if ($bill['state'] == 4 || $bill['state'] == 5) {
            $w_state = 'zc';
        } else {
            $w_state = 'car';
        }
        // 司机gps
        $data1=M()->query("SELECT concat(ST_X(local_gps),',', ST_Y(local_gps)) as gps from coal_users where id=$uid;");
        $gps = $data1[0]['gps'];
        // 历史gps
        $gps_hisory_data = M('gps_history')->where(array('uid' => $uid))->order('id desc')->find();
        $gps_hisory = $gps_hisory_data['x'].','.$gps_hisory_data['y'];
        if (!$gps) {
            // 没有gps,取$gps_hisory
            if ($gps_hisory_data['x']) {
                $x = $gps_hisory_data['x'];
                $y = $gps_hisory_data['y'];
            } else {
                $company_gps = getComapnyGps(session('company_id'));
                if ($company_gps['x']) {
                    $x = $company_gps['x'];
                    $y = $company_gps['y'];
                } else {
                    // 曹阳路32公里
                    $x = '110.594998';
                    $y = '39.576508';
                }
            }

            // 更新司机的gps
            $tmp1 = M()->execute("update coal_users set local_gps=ST_GeomFromText('POINT($x $y)') where id='$uid'");
            $gps_hisory = $x.','.$y;
        }
        if($gps != $gps_hisory){
            $gps = $gps_hisory;
        }
        $data=array(
            'code'    => 1,
            'msg'     => '获取成功!',
            'data'    => $data1,
            'uid'     => $uid,
            'w_state' => $w_state,
            'gps'     => $gps
        );
        $this->assign('data',$data);
        $this->display();
    }

}