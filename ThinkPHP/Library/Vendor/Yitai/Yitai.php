<?php
/**
 * Created by PhpStorm.
 * User: 君王
 * Date: 2017-04-17
 * Time: 18:01
 */
class Yitai{
    function creatBill($seller,$buyer,$lic_number,$coal_type,$code_str,$bill_number){
        if(!$seller||!$buyer||!$lic_number||!$coal_type||!$code_str||!$bill_number){
            $ret['code']=0;
            $ret['message']='提交信息有误';
            return $ret;
            die();
        }

        //用提煤单号检查该订单是否派出过
        $ifbill=M('link_yitai')->where(array('bill_number'=>I('bill_number')))->find();
        if($ifbill){
            $ret['code']=0;
            $ret['message']='提交信息有误';
            return $ret;
            die();
        }
        //检测提交的车辆是否是通过审核的空车状态
        $truck=M('truck')->where(array('lic_number'=>$lic_number,'is_passed'=>1,'state'=>1))->find();
        if(!$truck){
            $ret['code']=0;
            $ret['message']='车辆状态异常';
            return $ret;
            die();
        }
        //检查司机
        $driver=M('driver')->where(array('truck_id'=>$truck['id']))->select();
        foreach ($driver as $key=>$value){
            if(!D('Driver','Logic')->is_work($value['uid'])){
                unset($driver[$key]);
            }
        }
        if(!count($driver)){
            $ret['code']=0;
            $ret['message']='车辆没有可用司机';
            return $ret;
            die();
        }
        //查看车辆的车主和物流公司
        $trucker=M('users')->where(array('id'=>$truck['owner_id']))->find();
        $company=M('company')->where(array('id'=>$truck['user_id']))->find();
        M()->startTrans();
        $res=1;
        $res1=1;
        $res2=1;
        $res3=1;
        $res4=1;
        $res5=1;
        $res6=1;
        $res8=1;
        //检测输入的买家卖家系统中是否已经存在
        $is_seller=M('company')->where(array('name'=>$seller))->find();
        if($is_seller){
            $order['seller_id']=$bill['seller']=$is_seller['id'];

        }else{
            //没有卖家则系统添加并且标记为需要后台编辑
            $data_seller['name']=$seller;
            $data_seller['is_vip']=0;
            $data_seller['lic_number']="YT".time().rand(1000,9999);
            $data_seller['type']=1;
            $res=D('Company')->addData($data_seller);

            $order['seller_id']=$bill['seller']=$res['id'];
        }


        $is_buyer=M('company')->where(array('name'=>$buyer))->find();
        if($is_buyer){
            $order['buyer_id']=$bill['buyer']=$is_buyer['id'];
        }else{
            //没有买家则系统添加并且标记为需要后台编辑
            $data_buyer['name']=$buyer;
            $data_buyer['is_vip']=0;
            $data_buyer['lic_number']="YT".time().rand(1000,9999);
            $data_buyer['type']=1;
            $res2=D('Company')->addData($data_buyer);

            $order['buyer_id']=$bill['buyer']=$res2['id'];
        }

        $yitai['coal_type']=$coal_type;
        $bill['order_id']=$order['order_id']=I('order_id')?I('order_id'):"YITAI".'T'.time().rand_num();
        $order['is_private']=0;
        $order['create_time']=date('Y-m-d');

        $bill['company']=$company['id'];
        $bill['trucker_id']=$truck['owner_id'];
        $bill['arrange_w']=I('arrange_w')?I('arrange_w'):40;
        $bill['state']=1;
        $bill['type']=0;
        $bill['create_type']='1';
        $bill['truck_id']=$truck['id'];
        $yitai['bill_number']=I('bill_number');
        $yitai['code_str']=$code_str;
        $yitai['bill_id']=$code_str;
        $yitai['gegin_gps']=NULL;
        $yitai['end_gps']=NULL;


        $res6=M('orders')->add($order);
        //计算派出滞后时间
        if(!I('dis_time')){
            $yitai['end_use_time']='未知';
        }else {
            $yitai['yitai_dis_time'] =I('dis_time');
            $dis_time = time();
            $begin_time = strtotime(I('dis_time'));
            $begin_use_time = $dis_time - $begin_time;

            $day = floor($begin_use_time / 86400);
            $hour = floor(($begin_use_time - ($day * 86400)) / 3600);
            $minit = floor(($begin_use_time - ($day * 86400) - $hour * 3600) / 60);
            if ($day) {
                $yitai['dis_use_time'] = $day . '天' . $hour . '小时' . $minit . '分钟';
            } elseif ($hour) {
                $yitai['dis_use_time'] = $hour . '小时' . $minit . '分钟';
            } elseif ($minit) {
                $yitai['dis_use_time'] = $minit . '分钟';
            } else {
                $yitai['dis_use_time'] = '不到一分钟';
            }
        }
        $res6=D('Bill')->addData($bill);
        $yitai['bill_id']=$res6['message'];
        $res7=M('truck')->where(array('id'=>$truck['id']))->save(array('state'=>2));
        $res8=M('link_yitai')->add($yitai);

        if($res&&$res1&&$res2&&$res3&&$res4&&$res5&&$res6&&$res7!==false&&$res8){
            noticeBill($truck['id']);
            M()->commit();
            $ret['code']=1;
            $ret['message']='成功';
            backJson($ret);
        }else{
            M()->rollback();
            $ret['code']=0;
            $ret['message']='失败';
            backJson($ret);
        }
    }
}