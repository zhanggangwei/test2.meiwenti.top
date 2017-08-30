<?php
//提煤单模型
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/3/22
 * Time: 17:33
 */
namespace Common\Logic;
use Common\Model\BaseModel;
class BillLogic extends \Think\Model{

    /**
     * 派单
     * @param int $logistics_id 物流id
     * @param int $truck_id 车辆id
     * @param $company_id  物流公司ID
     * @param float $arr_w  指派吨数
     * @return json
     */
  function arr($logistics_id,$truck_id,$company_id,$arr_w=40){
      //吨数是大于0
      //检测车辆
      //车辆状态
      //车辆使用者
      //车辆司机
      if ($arr_w + 0 <= 0) {
          $ret['code']=0;
          $ret['message']='指派吨数必须大于0';
      }
      $truck=M('truck')->where(array('id'=>$truck_id,'is_passed'=>1,'state'=>1,'is_work'=>1))->find();

      if (!$truck) {
          $ret['code']=0;
          $ret['message']='车辆状态异常';
          return $ret;
      } else {
          if ($truck['maximum']<$arr_w){
              $ret['code']=0;
              $ret['message']='指派吨数超出车辆载重';
              return $ret;
          }
          if ($truck['user_id']!=$company_id){
              $ret['code']=0;
              $ret['message']='车辆使用者与指派者不符合';
              return $ret;
          }
          if ($truck['anchored_id'] != $truck['user_id'] && $truck['is_comperation'] != 1) {
              $ret['code']=0;
              $ret['message']='合作车辆未确认';
              return $ret;
          }
      }
      
      $driver=M('driver')->where(array('truck_id'=>$truck_id))->select();
      foreach ($driver as $key=>$value){
          if(!D('Driver','Logic')->is_work($value['uid'])){
              unset($driver[$key]);
          }
      }
      if(!count($driver)){
          $ret['code']=0;
          $ret['message']='车辆没有可用司机';
          return $ret;
      }
      
      //检测计划
      //计划是否是派给$arr_id
      //吨数是否够派完
      $logistics=M('logistics')->where(array('id'=>$logistics_id))->find();
      if($logistics['assigned_id']!=$company_id){
          $ret['code']=0;
          $ret['message']='操作的计划不是派给自己的';
          return $ret;
      }
      if($logistics['res_quantity']<$arr_w){
          $ret['code']=0;
          $ret['message']='计划剩余吨数不够';
          return $ret;
      }
      //改变车辆状态为待司机确认
      //添加bill数据
      //原计划吨数减去指派吨数
      //发送短信,推送数据
      $order=M('orders')->where(array('order_id'=>$logistics['order_id']))->find();
      M()->startTrans();
      $data1['state']=2;
      $res1=M('truck')->where(array('id'=>$truck_id))->save($data1);
      $data2['buyer']=$order['buyer_id'];
      $data2['seller']=$order['seller_id'];
      $data2['order_id']=$logistics['order_id'];
      $data2['company']=$company_id;
      $data2['logistics_id']=$logistics_id;
      $data2['truck_id']=$truck_id;
      $data2['arrange_w']=$arr_w;
      $data2['trucker_id']=$truck['owner_id'];
      $data2['owner_type']=$truck['owner_type'];
      $data2['anchored_id']=$truck['anchored_id'];
      $res2=D('Bill')->addData($data2);
      $data3['res_quantity']=$logistics['res_quantity']-$arr_w;
      $res3=M('logistics')->where(array('id'=>$logistics_id))->save($data3);

      if($res1!==false and $res2['code']==1 and $res3!==false){
            //通知司机
          noticeBill($truck_id);
          M()->commit();
          $ret['code']=1;
          $ret['message']='派单成功';
          return $ret;
      }else{
          M()->rollback();
          $ret['code']=0;
          $ret['message']='派单失败';
          return $ret;
      }
  }

    /**
     * 重新派单
     * @param int $bill_id       提煤单id
     * @param int $truck_id      车辆id
     * @param int $company_id    物流公司ID
     * @param float $arr_w         指派吨数
     * return json
     */
    public function reArr($bill_id, $truck_id, $company_id){
        // 一、数据验证
        // 1、获取要替换的提煤单
        $bill = M('bill')->find($bill_id);
        if (!$bill) {
            $ret['code'] = 0;
            $ret['message'] = '数据有误';
            return $ret;
        }
        // 2、车辆是否可以派单
        if (!$res = D('Truck', 'Logic')->isDispatch($truck_id)) {
            $ret['code'] = 0;
            $ret['message'] = $res['message'];
            return $ret;
        }
        // 3、指派吨数是否超出车辆载重
        $truck = M('truck')->find($truck_id);
        // if ($truck['maximum'] < $bill['arrange_w']) {
        //     $ret['code'] = 0;
        //     $ret['message'] = '指派吨数超出车辆载重';
        //     return $ret;
        // }
        // 二、数据修改
        M()->startTrans();
        // 1、取消原来的提煤单
        $res1 = M('bill')->save(array('id' => $bill['id'], 'state' => 7));
        // 2、原车辆状态归为空车状态
        $res2 = M('truck')->save(array('id' => $bill['truck_id'], 'state' => 1));
        // 3、查看是否是退单重派单
        $rbill = M('driver_rbill')->where(array('bill_id'=>$bill['id'],'state'=>1))->find();
        if($rbill){
            $data['state']=2;
            $res3 = M('driver_rbill')->where(array('bill_id'=>$bill['id']))->save($data);
        }else{
            $res3 = true;
        }
        // 4、重新生成提煤单
        $data4 = array(
            'buyer'        => $bill['buyer'],
            'seller'       => $bill['seller'],
            'order_id'     => $bill['order_id'],
            'company'      => $company_id,
            'logistics_id'=> $bill['logistics_id'],
            'truck_id'     => $truck_id,
            'arrange_w'    => $bill['arrange_w'],
            'trucker_id'   => $truck['owner_id'],
            'owner_type'   => $truck['owner_type'],
            'anchored_id'  => $truck['anchored_id'],
        );
        $res4 = D('Bill')->addData($data4);
        // 5、车的状态
        $res5 = M('truck')->save(array('id' => $truck_id, 'state' => 2));

        if($res1!==false&&$res2!=false&&$res3!==false&&$res4!==false||$res5!==false){
            //通知司机
            noticeBill($truck_id);
            M()->commit();
            $ret['code']=1;
            $ret['message']='重新派单成功';
            return $ret;
        }else{
            M()->rollback();
            $ret['code']=0;
            $ret['message']='重新派单失败';
            return $ret;
        }
    }
    /**
     * 提煤单详情
     * @param int $bill_id 提煤单id
     * @return string buyer 买方
     * @return string seller 卖方
     * @return string company 物流公司
     * @return arr_w float 指派吨数
     * @return state int 状态
     */
    function detail($bill_id){
        $bill=M('bill')->where(array('id'=>$bill_id))->find();
        //提货地//送货地//煤种
        $buyer=M('company')->where(array('id'=>$bill['buyer']))->find();
        $arr['buyer']=$buyer['name'];
        $seller=M('company')->where(array('id'=>$bill['seller']))->find();
        $arr['seller']=$seller['name'];

        switch ($bill['create_type']){
            case 0:
                $order=M('orders')->where(array('order_id'=>$bill['order_id']))->find();
                $arr['coal_type']=coal_type($order['coal_type']);
                $arr['state']=$bill['state'];
                return $arr;
            case 1:
                $arr['coal_type']=M('link_yitai')->where(array('bill_id'=>$bill['id']))->getField('coal_type');
                return $arr;

        }

    }
    /**
     * 确认接单
     * @param int $bill_id 提煤单id
     * @return int $driver_id 司机ID
     * @return state int 状态
     */
    function accept($bill_id,$driver_id){
        //提煤单状态
        $bill=M('bill')->where(array('id'=>$bill_id,'state'=>1))->find();
        if(!$bill){
            $ret['code']=0;
            $ret['message']='提煤单状态异常';
            backJson($ret);
        }
        //检测车辆状态
        $truck=M('truck')->where(array('id'=>$bill['truck_id'],'state'=>2,'is_work'=>1))->find();
        if(!$truck){
            $ret['code']=0;
            $ret['message']='车辆状态异常';
            backJson($ret);
        }
        //检测司机状态
        if(!D('Driver',"Logic")->is_work($driver_id)){
            $ret['code']=0;
            $ret['message']='司机不在工作时间';
            backJson($ret);
        }

        //更改提煤单状态
        //更改车辆状态
        $data1['state']=2;
        $data1['do_time']=date('Y-m-d H:i:s');
        $data1['driver_id']=session('user_id');
        //查看是否是卡片刷出来的单，如果是则变成已经打印的状态
        $is_hand=M('link_yitai')->where(array('bill_id'=>$bill_id))->getField('is_hand');
        if($is_hand==1){
            $data1['is_print']=1;
//            $data1['use_type']=1;
        }
        $data2['state']=3;
        M()->startTrans();
        $res1=M('bill')->where(array('id'=>$bill_id))->save($data1);
        $res2=M('truck')->where(array('id'=>$truck['id']))->save($data2);
        if($res1!==false and $res2!==false){
            $ret['code']=1;
            $ret['message']='接单成功';
            M()->commit();
            backJson($ret);
        }else{
            $ret['code']=0;
            $ret['message']='操作失败';
            M()->rollback();
            backJson($ret);
        }
    }
    /**
     * 提煤单二维码
     * @param int $bill_id 提煤单id
     * @return int $driver_id 司机ID
     * @return state int 状态
     */
    function ewm($bill_id){
        //查看提煤单是不是派给自己的
        $bill=M('bill')->where(array('id'=>$bill_id))->find();
        $driver=M('driver')->where(array('uid'=>session('user_id')))->find();
        if(!$bill){
            $ret['code']=0;
            $ret['message']='参数传递有误1';
            backJson($ret);
        }else{
            if($bill['truck_id']!=$driver['truck_id']){
                $ret['code']=0;
                $ret['message']='参数传递有误2';
                backJson($ret);
            }elseif($bill['driver_id']!=$driver['uid']){
                //查看提煤单是不是自己领取的
                $ret['code']=0;
                $ret['message']='参数传递有误3';
                backJson($ret);
            }
        }
        //根据不同的对接系统处理不同的二维码
        switch ($bill['create_type']){
            //系统自我生成
            case 0://获取提煤单信息：提货地点，收货地点，卖方，买方，买方id，卖方id,吨数，下发时间，煤种
                $order=M('orders')->where(array('order_id'=>$bill['order_id']))->field('coal_type')->find();

                $buyer=M('company')->where(array('id'=>$bill['buyer']))->find();
                $seller=M('company')->where(array('id'=>$bill['seller']))->find();

                $truck=M('truck')->where(array('id'=>$bill['truck_id']))->find();
                $driver=M('driver')->where(array('aid'=>$bill['driver_id']))->find();

                //$resbill['seller']=$seller['name'];
                $resbill['seller_id']=$seller['id'];
                // $resbill['buyer']=$buyer['name'];
                $resbill['buyer_id']=$buyer['id'];
                $resbill['coal_type']=$order['coal_type'];
                $resbill['truck']=$truck['lic_number'];
                //            $resbill['driver_id']=$driver['aid'];

                $resbill['bill_id']=$bill['id'];
                //生成提煤单状态
                switch ($bill['state']){
                    case '4':$resbill['state']='已完成';break;
                    case '5':$resbill['state']='提煤单已失效';break;
                    case '6':$resbill['state']='提煤单已失效';break;
                    default:$resbill['state']='正常';break;
                }
                $data=json_encode($resbill);
                //加密
                vendor('Phpqrcode.Phpqrcode');
                vendor('Base64.Base64');
                $base64=new \base64();

                $value =  'MWT$$'.$base64->encode($data); //二维码内容

                $errorCorrectionLevel = 'L';//容错级别
                $matrixPointSize = 5;//生成图片大小
                //生成二维码图片
                \QRcode::png($value/*把加密关了,开启的时候直接把data换成value*/, false, $errorCorrectionLevel, $matrixPointSize, 0, false, array(255,255,255), array(0,0,0));
                break;
            case 1:
                //伊泰对接
                $value=M('link_yitai')->where(array('bill_id'=>$bill_id))->getField('code_str');
                //加密
                vendor('Phpqrcode.Phpqrcode');
                $errorCorrectionLevel = 'L';//容错级别
                $matrixPointSize = 5;//生成图片大小
                //生成二维码图片
                \QRcode::png($value, false, $errorCorrectionLevel, $matrixPointSize, 0, false, array(255,255,255), array(0,0,0));
                break;
        }
    }
    /**
     * 确认拉货
     * @param int $bill_id 提煤单id
     * @param int $begin_w 起始吨数
     * @param int $vip_id  操作的贸易商
     * @param int $type  处理方式1=>电子过绑定系统提交,2=>物流公司手动提交
     *
     */
    function sellerBill($bill_id,$begin_w,$vip_id,$type){
        switch ($type){
            //电子过磅系统提交
            case 1:
                //检测vip_id是否是bill的卖家
                //检测提煤单状态是不是司机已经接单
                $bill=M('bill')->where(array('id'=>$bill_id))->where(array('state'=>2))->find();
                if (!$bill or $bill['seller']!=$vip_id){
                    $ret['code']=0;
                    $ret['message']='提煤单状态异常';
                    backJson($ret);
                }
                break;
            case 2:
                //物流公司确定提货单
                //检测这个订单是不是派给物流公司的
                $bill=M('bill')->where(array('id'=>$bill_id))->where(array('state'=>2))->find();
                if (!$bill or $bill['company']!=$vip_id){
                    $ret['code']=0;
                    $ret['message']='提煤单状态异常';
                    return $ret;
                }
                break;
        }
        $data['state']=3;
        $data['begin_w']=$begin_w;
        $data['begin_time']=date('Y-m-d H:i:s');
        $res=M('bill')->where(array('id'=>$bill_id))->save($data);
        if($res!==false){
            $ret['code']=1;
            $ret['message']='操作成功';
            backJson($ret);
        }else{
            $ret['code']=0;
            $ret['message']='操作失败';
            backJson($ret);
        }
    }

    /**
     * 确认收货
     * @param int $bill_id 提煤单id
     * @param int $end_w 结束吨数
     * @param int $vip_id  操作的贸易商
     * @param int $type  处理方式1=>电子过绑定系统提交,2=>物流公司手动提交
     *
     */
    function buyerBill($bill_id,$end_w,$vip_id,$type){
        switch ($type){
            //电子过磅系统提交
            case 1:
                //检测vip_id是否是bill的卖家
                //检测提煤单状态是不是司机已经接单
                $bill=M('bill')->where(array('id'=>$bill_id))->where(array('state'=>3))->find();
                if (!$bill or $bill['buyer']!=$vip_id){
                    $ret['code']=0;
                    $ret['message']='提煤单状态异常';
                    backJson($ret);
                }
                break;
            case 2:
                //物流公司确定提货单
                //检测这个订单是不是派给物流公司的
                $bill=M('bill')->where(array('id'=>$bill_id))->where(array('state'=>3))->find();
                if (!$bill or $bill['company']!=$vip_id){
                    $ret['code']=0;
                    $ret['message']='提煤单状态异常';
                    return $ret;
                }
                break;

        }
        $data['state']=4;
        $data['end_w']=$end_w;
        $data['end_time']=date('Y-m-d H:i:s');
        $truck['state']=1;
        $truck['last_time']=date('Y-m-d H:i:s');
        M()->startTrans();
        $res=M('bill')->where(array('id'=>$bill_id))->save($data);
        $res1=M('truck')->where(array('id'=>$bill['truck_id']))->save($truck);
        if($res!==false and $res1!==false){
            M()->commit();
            $ret['code']=1;
            $ret['message']='操作成功';
            backJson($ret);
        }else{
            M()->rollback();
            $ret['code']=0;
            $ret['message']='操作失败';
            backJson($ret);
        }
    }
    /**
     * 扫描二维码信息
     * @param int $code 二维码内容
     *
     */
    function readEwm($code){
        $prefix=substr($code,0,5);
        if($prefix=='MWT$$'){
            $code_str=substr($code,5);
            //截取密钥
            $key=substr($code_str,0,4);
            $data=substr($code_str,4);
            $code=object_array(json_decode(decrypt($data,$key)));
            $ret['detail']=$code;
            $ret['code']=1;
            $ret['message']='正确获取信息';
            backJson($ret);
        }else{
            $ret['detail']=$code;
            $ret['code']=1;
            $ret['message']='伊泰提煤单';
            backJson($ret);
        }
    }
    /**
     * 提货点进矿扫描
     * @param int $vip_id 提交处理的贸易商ID
     * @param int $bill 提煤单内容(如果是系统生成的提煤单则提交提煤单ID,如果是伊泰的提煤单则提交bill_number
     * @param int $type 处理类型:0=>系统自动生成,1=>伊泰生成的提煤单
     */
    function beginFirstRead($vip_id,$bill,$type){
        switch ($type){
            case 0:
                //查看操作的公司是否是提货方
                //检测提煤单状态
                $billdata=M('bill')->where(array('id'=>$bill))->field('seller,buyer,dis_time,driver_id,state,arrange_w')->find();
                if($billdata['seller']!=$vip_id or ($billdata['state']!=2)){
                    $ret['code']=3;
                    $ret['message']='提煤单状态异常,可能是司机没接单';
                    backJson($ret);
                }else{
                    $data['state']=3;
                    $data['begin_first_time']=date('Y-m-d H:i:s');
                    $res=M('bill')->where(array('id'=>$bill))->save($data);
                    if($res!==false){
                        $billdata['seller']=M('company')->where(array('id'=>$billdata['seller']))->getField('name');
                        $billdata['buyer']=M('company')->where(array('id'=>$billdata['buyer']))->getField('name');
                        $billdata['truck_owner_id']=M('truck')->where(array('id'=>$billdata['truck_id']))->getField('owner_order');
                        $billdata['driver']=M('users')->where(array('id'=>$billdata['driver_id']))->getField('real_name');
                        unset($billdata['state']);
                        unset($billdata['driver_id']);
                        $ret['detail']=$billdata;
                        $ret['code']=1;
                        $ret['message']='确认成功';
                        backJson($ret);
                    }else{
                        $ret['code']=4;
                        $ret['message']='确认失败,可以再次尝试';
                        backJson($ret);
                    }
                }
                break;
            case 1:
                $bill_id=M('link_yitai')->where(array('bill_number'=>$bill))->getField('bill_id');
                $billdata=M('bill')->where(array('id'=>$bill_id))->find();
                if($billdata['seller']!=$vip_id or ($billdata['state']!=2)){
                    $ret['code']=3;
                    $ret['message']='提煤单状态异常,可能是司机没接单';
                    backJson($ret);
                }else{
                    $data['state']=3;
                    $data['begin_first_time']=date('Y-m-d H:i:s');
                    $res=M('bill')->where(array('id'=>$bill_id))->save($data);
                    if($res!==false){
                        $billdata['seller']=M('company')->where(array('id'=>$billdata['seller']))->getField('name');
                        $billdata['buyer']=M('company')->where(array('id'=>$billdata['buyer']))->getField('name');
                        $billdata['truck_owner_id']=M('truck')->where(array('id'=>$billdata['truck_id']))->getField('owner_order');
                        $billdata['driver']=M('users')->where(array('id'=>$billdata['driver_id']))->getField('real_name');
                        unset($billdata['state']);
                        unset($billdata['driver_id']);
                        $ret['detail']=$billdata;
                        $ret['code']=1;
                        $ret['message']='确认成功';
                        backJson($ret);
                    }else{
                        $ret['code']=4;
                        $ret['message']='确认失败,可以再次尝试';
                        backJson($ret);
                    }
                }
                break;
            default:
                $ret['code']=0;
                $ret['message']='信息提交有误';
                backJson($ret);
        }
    }
    /**
     * 提货点回批扫描
     * @param int $vip_id 提交处理的贸易商ID
     * @param int $bill 提煤单内容(如果是系统生成的提煤单则提交提煤单ID,如果是伊泰的提煤单则提交bill_number
     * @param int $type 处理类型:0=>系统自动生成,1=>伊泰生成的提煤单
     * @param int $begin_w 空车重量
     * @param int $end_w 重车重量
     */
    function beginSecondRead($vip_id,$bill,$type,$begin_w,$end_w){
        switch ($type){
            case 0:
                //查看操作的公司是否是提货方
                //检测提煤单状态
                $billdata=M('bill')->where(array('id'=>$bill))->field('seller,buyer,dis_time,driver_id,state,arrange_w')->find();
                if($billdata['seller']!=$vip_id or $billdata['state']!=3){
                    $ret['code']=3;
                    $ret['message']='提煤单状态异常';
                    backJson($ret);
                }else{
                    $data['state']=4;
                    $data['begin_second_time']=date('Y-m-d H:i:s');
                    $data['begin_first_w']=$begin_w;
                    $data['begin_second_w']=$end_w;
                    $data['begin_w']=$end_w-$begin_w;
                    $res=M('bill')->where(array('id'=>$bill))->save($data);
                    //变为重车状态
                    $truck['state']=3;
                    $res1=M('truck')->where(array('id'=>$billdata['truck_id']))->save($truck);
                    if($res!==false and $res1!==false){
                        $billdata['seller']=M('company')->where(array('id'=>$billdata['seller']))->getField('name');
                        $billdata['buyer']=M('company')->where(array('id'=>$billdata['buyer']))->getField('name');
                        $billdata['truck_owner_id']=M('truck')->where(array('id'=>$billdata['truck_id']))->getField('owner_order');
                        $billdata['driver']=M('users')->where(array('id'=>$billdata['driver_id']))->getField('real_name');
                        unset($billdata['state']);
                        unset($billdata['driver_id']);
                        $ret['detail']=$billdata;
                        $ret['code']=1;
                        $ret['message']='确认成功';
                        backJson($ret);
                    }else{
                        $ret['code']=4;
                        $ret['message']='确认失败,可以再次尝试';
                        backJson($ret);
                    }
                }
                break;
            case 1:
                $bill_id=M('link_yitai')->where(array('bill_number'=>$bill))->getField('bill_id');
                $billdata=M('bill')->where(array('id'=>$bill_id))->find();
                if($billdata['seller']!=$vip_id or $billdata['state']!=3){
                    $ret['code']=3;
                    $ret['message']='提煤单状态异常';
                    backJson($ret);
                }else{
                    $data['state']=4;
                    $data['begin_second_time']=date('Y-m-d H:i:s');
                    $data['begin_first_w']=$begin_w;
                    $data['begin_second_w']=$end_w;
                    $data['begin_w']=$end_w-$begin_w;
                    $res=M('bill')->where(array('id'=>$bill_id))->save($data);
                    //变为重车状态
                    $truck['state']=3;
                    $res1=M('truck')->where(array('id'=>$billdata['truck_id']))->save($truck);
                    if($res!==false and $res1!==false){
                        $billdata['seller']=M('company')->where(array('id'=>$billdata['seller']))->getField('name');
                        $billdata['buyer']=M('company')->where(array('id'=>$billdata['buyer']))->getField('name');
                        $billdata['truck_owner_id']=M('truck')->where(array('id'=>$billdata['truck_id']))->getField('owner_order');
                        $billdata['driver']=M('users')->where(array('id'=>$billdata['driver_id']))->getField('real_name');
                        unset($billdata['state']);
                        unset($billdata['driver_id']);
                        $ret['detail']=$billdata;
                        $ret['code']=1;
                        $ret['message']='确认成功';
                        backJson($ret);
                    }else{
                        $ret['code']=4;
                        $ret['message']='确认失败,可以再次尝试';
                        backJson($ret);
                    }
                }
                break;
            default:
                $ret['code']=0;
                $ret['message']='信息提交有误';
                backJson($ret);
        }
    }
    /**
     * 送货点进矿扫描
     * @param int $vip_id 提交处理的贸易商ID
     * @param int $bill 提煤单内容(如果是系统生成的提煤单则提交提煤单ID,如果是伊泰的提煤单则提交bill_number
     * @param int $type 处理类型:0=>系统自动生成,1=>伊泰生成的提煤单
     */
    function endFirstRead($vip_id,$bill,$type){
        switch ($type){
            case 0:
                //查看操作的公司是否是提货方
                //检测提煤单状态
                $billdata=M('bill')->where(array('id'=>$bill))->field('seller,buyer,dis_time,driver_id,state,arrange_w')->find();
                if($billdata['buyer']!=$vip_id or ($billdata['state']!=4)){
                    //如果说已经确认过就不能再次进入了
                    if($billdata['state']==5){
                        $ret['code']=6;
                        $ret['message']='已经提交过数据的车辆不能再次进入';
                        backJson($ret);
                    }
                    //这时候要检测提货点过磅单有没有提交过,如果有的话就返回过去
                    $balance=M('balance_bill')->where(array('bill_id'=>$bill,'type'=>1))->find();
                    if(!$balance){
                        $ret['code']=3;
                        $ret['message']='该提煤单还没有提交过提货点过磅单';
                        backJson($ret);
                    }else{
                        $ret['code']=4;
                        $billdata['seller']=M('company')->where(array('id'=>$billdata['seller']))->getField('name');
                        $billdata['buyer']=M('company')->where(array('id'=>$billdata['buyer']))->getField('name');
                        $billdata['truck_owner_id']=M('truck')->where(array('id'=>$billdata['truck_id']))->getField('owner_order');
                        $billdata['driver']=M('users')->where(array('id'=>$billdata['driver_id']))->getField('real_name');
                        unset($billdata['state']);
                        unset($billdata['driver_id']);
                        $ret['detail']=$billdata;
                        $ret['balance_img']=getTrueImgSrc($balance['img']);
                        $ret['balance_weight']=$balance['weight'];
                        $ret['message']='请确认提货点过磅单数据';
                        backJson($ret);
                    }
                }else{
                    $data['state']=5;
                    $data['end_first_time']=date('Y-m-d H:i:s');
                    $res=M('bill')->where(array('id'=>$bill))->save($data);
                    if($res!==false){
                        $billdata['seller']=M('company')->where(array('id'=>$billdata['seller']))->getField('name');
                        $billdata['buyer']=M('company')->where(array('id'=>$billdata['buyer']))->getField('name');
                        $billdata['truck_owner_id']=M('truck')->where(array('id'=>$billdata['truck_id']))->getField('owner_order');
                        $billdata['driver']=M('users')->where(array('id'=>$billdata['driver_id']))->getField('real_name');
                        unset($billdata['state']);
                        unset($billdata['driver_id']);
                        $ret['detail']=$billdata;
                        $ret['code']=1;
                        $ret['message']='确认成功';
                        backJson($ret);
                    }else{
                        $ret['code']=5;
                        $ret['message']='数据处理失败,稍后再试';
                        backJson($ret);
                    }
                }
                break;
            case 1:
                //伊泰逻辑处理
                $bill_id=M('link_yitai')->where(array('bill_number'=>$bill))->getField('bill_id');
                $billdata=M('bill')->where(array('id'=>$bill_id))->find();
                if($billdata['buyer']!=$vip_id or ($billdata['state']!=4)){
                    //如果说已经确认过就不能再次进入了
                    if($billdata['state']==5){
                        $ret['code']=6;
                        $ret['message']='已经提交过数据的车辆不能再次进入';
                        backJson($ret);
                    }
                    //这时候要检测提货点过磅单有没有提交过,如果有的话就返回过去
                    $balance=M('balance_bill')->where(array('bill_id'=>$bill_id,'type'=>1))->find();
                    if(!$balance){
                        $ret['code']=3;
                        $ret['message']='该提煤单还没有提交过提货点过磅单';
                        backJson($ret);
                    }else{
                        $ret['code']=4;
                        $billdata['seller']=M('company')->where(array('id'=>$billdata['seller']))->getField('name');
                        $billdata['buyer']=M('company')->where(array('id'=>$billdata['buyer']))->getField('name');
                        $billdata['truck_owner_id']=M('truck')->where(array('id'=>$billdata['truck_id']))->getField('owner_order');
                        $billdata['driver']=M('users')->where(array('id'=>$billdata['driver_id']))->getField('real_name');
                        unset($billdata['state']);
                        unset($billdata['driver_id']);
                        $ret['detail']=$billdata;
                        $ret['balance_img']=getTrueImgSrc($balance['img']);
                        $ret['balance_weight']=$balance['weight'];
                        $ret['message']='请确认提货点过磅单数据';
                        backJson($ret);
                    }
                }else{
                    $data['state']=5;
                    $data['end_first_time']=date('Y-m-d H:i:s');
                    $res=M('bill')->where(array('id'=>$bill_id))->save($data);
                    if($res!==false){
                        $billdata['seller']=M('company')->where(array('id'=>$billdata['seller']))->getField('name');
                        $billdata['buyer']=M('company')->where(array('id'=>$billdata['buyer']))->getField('name');
                        $billdata['truck_owner_id']=M('truck')->where(array('id'=>$billdata['truck_id']))->getField('owner_order');
                        $billdata['driver']=M('users')->where(array('id'=>$billdata['driver_id']))->getField('real_name');
                        unset($billdata['state']);
                        unset($billdata['driver_id']);
                        $ret['detail']=$billdata;
                        $ret['code']=1;
                        $ret['message']='确认成功';
                        backJson($ret);
                    }else{
                        $ret['code']=5;
                        $ret['message']='数据处理失败,稍后再试';
                        backJson($ret);
                    }
                }
                break;
            default:
                $ret['code']=0;
                $ret['message']='信息提交有误';
                backJson($ret);
        }
    }
    /**
     * 送货点完成
     * @param int $vip_id 提交处理的贸易商ID
     * @param int $bill 提煤单内容(如果是系统生成的提煤单则提交提煤单ID,如果是伊泰的提煤单则提交bill_number
     * @param int $type 处理类型:0=>系统自动生成,1=>伊泰生成的提煤单
     * @param int $begin_w 空车重量
     * @param int $end_w 重车重量
     */
    function endSecondRead($vip_id,$bill,$type,$begin_w,$end_w){
        switch ($type){
            case 0:
                //查看操作的公司是否是提货方
                //检测提煤单状态
                $billdata=M('bill')->where(array('id'=>$bill))->field('seller,buyer,dis_time,driver_id,state,arrange_w,truck_id')->find();
                if($billdata['buyer']!=$vip_id or $billdata['state']!=5){
                    $ret['code']=3;
                    $ret['message']='提煤单状态异常';
                    backJson($ret);
                }else{
                    $data['state']=6;
                    $data['end_second_time']=date('Y-m-d H:i:s');
                    $data['end_first_w']=$begin_w;
                    $data['end_second_w']=$end_w;
                    $data['end_w']=$begin_w-$end_w;
                    $res=M('bill')->where(array('id'=>$bill))->save($data);
                    $truck['state']=1;
                    $truck['last_time']=date('Y-m-d H:i:s');
                    $res1=M('truck')->where(array('id'=>$billdata['truck_id']))->save($truck);
                    if($res!==false and $res1!==false){
                        $billdata['seller']=M('company')->where(array('id'=>$billdata['seller']))->getField('name');
                        $billdata['buyer']=M('company')->where(array('id'=>$billdata['buyer']))->getField('name');
                        $billdata['truck_owner_id']=M('truck')->where(array('id'=>$billdata['truck_id']))->getField('owner_order');
                        $billdata['driver']=M('users')->where(array('id'=>$billdata['driver_id']))->getField('real_name');

                        $ret['detail']=$billdata;
                        $ret['code']=1;
                        $ret['message']='确认成功';
                        //推送司机完成
                        D('Push','Logic')->finishBill($billdata['driver_id']);
                        // 预暂停车辆
                        is_truck_cron($billdata['truck_id']);

                        unset($billdata['state']);
                        unset($billdata['driver_id']);
                        backJson($ret);
                    }else{
                        $ret['code']=4;
                        $ret['message']='确认失败,可以稍后再试';
                        backJson($ret);
                    }
                }
                break;
            case 1:
                $bill_id=M('link_yitai')->where(array('bill_number'=>$bill))->getField('bill_id');
                $billdata=M('bill')->where(array('id'=>$bill_id))->find();
                if($billdata['buyer']!=$vip_id or $billdata['state']!=5){
                    $ret['code']=3;
                    $ret['message']='提煤单状态异常';
                    backJson($ret);
                }else{
                    $data['state']=6;
                    $data['end_second_time']=date('Y-m-d H:i:s');
                    $data['end_first_w']=$begin_w;
                    $data['end_second_w']=$end_w;
                    $data['end_w']=$begin_w-$end_w;
                    $res=M('bill')->where(array('id'=>$bill_id))->save($data);
                    $truck['state']=1;
                    $truck['last_time']=date('Y-m-d H:i:s');
                    $res1=M('truck')->where(array('id'=>$billdata['truck_id']))->save($truck);
                    if($res!==false and $res1!==false){
                        $billdata['seller']=M('company')->where(array('id'=>$billdata['seller']))->getField('name');
                        $billdata['buyer']=M('company')->where(array('id'=>$billdata['buyer']))->getField('name');
                        $billdata['truck_owner_id']=M('truck')->where(array('id'=>$billdata['truck_id']))->getField('owner_order');
                        $billdata['driver']=M('users')->where(array('id'=>$billdata['driver_id']))->getField('real_name');

                        $ret['detail']=$billdata;
                        $ret['code']=1;
                        $ret['message']='确认成功';
                        //推送司机完成
                        D('Push','Logic')->finishBill($billdata['driver_id']);
                        // 预暂停车辆
                        is_truck_cron($billdata['truck_id']);

                        unset($billdata['state']);
                        unset($billdata['driver_id']);
                        backJson($ret);
                    }else{
                        $ret['code']=4;
                        $ret['message']='确认失败,可以稍后再试';
                        backJson($ret);
                    }
                }
                break;
            default:
                $ret['code']=0;
                $ret['message']='信息提交有误';
                backJson($ret);
        }
    }
    /**
     * 送货点确认提提货点的提煤单数据
     * @param int $vip_id 提交处理的贸易商ID
     * @param int $bill 提煤单内容(如果是系统生成的提煤单则提交提煤单ID,如果是伊泰的提煤单则提交bill_number
     * @param int $type 处理类型:0=>系统自动生成,1=>伊泰生成的提煤单
     * @param int $begin_w 过磅单提交的重量,如果数据不正确可以跟司机确认自行填写
     */
    function confirmFirstBalance($vip_id,$bill,$type,$begin_w){
        switch ($type){
            case 0:
                //查看操作的公司是否是提货方
                //检测提煤单状态
                $billdata=M('bill')->where(array('id'=>$bill))->field('seller,buyer,dis_time,driver_id,state,arrange_w')->find();
                $balance=M('balance_bill')->where(array('bill_id'=>$bill,'type'=>1))->find();
                if($billdata['buyer']!=$vip_id or $billdata['state']!=2){
                    $ret['code']=3;
                    $ret['message']='提煤单状态异常,可能是司机还没有接单';
                    backJson($ret);
                }else{
                    $data['state']=5;
                    $data['begin_first_time']=$balance['add_time'];
                    $data['begin_second_time']=$balance['add_time'];
                    $data['end_first_time']=date('Y-m-d H:i:s');
                    $data['begin_w']=$begin_w;
                    $res=M('bill')->where(array('id'=>$bill))->save($data);
                    $truck['state']=3;
                    $res1=M('truck')->where(array('id'=>$billdata['truck_id']))->save($truck);
                    $res2=M('balance_bill')->where(array('bill_id'=>$bill,'type'=>1))->save(array('state'=>1));
                    if($res!==false and $res1!==false and $res2!==false){
                        $billdata['seller']=M('company')->where(array('id'=>$billdata['seller']))->getField('name');
                        $billdata['buyer']=M('company')->where(array('id'=>$billdata['buyer']))->getField('name');
                        $billdata['truck_owner_id']=M('truck')->where(array('id'=>$billdata['truck_id']))->getField('owner_order');
                        $billdata['driver']=M('users')->where(array('id'=>$billdata['driver_id']))->getField('real_name');
                        unset($billdata['state']);
                        unset($billdata['driver_id']);
                        $ret['detail']=$billdata;
                        $ret['code']=1;
                        $ret['message']='确认成功,车辆可进入卸货';
                        backJson($ret);
                    }else{
                        $ret['code']=4;
                        $ret['message']='确认失败,可稍后再试';
                        backJson($ret);
                    }
                }
                break;
            case 1:
                $bill_id=M('link_yitai')->where(array('bill_number'=>$bill))->getField('bill_id');
                $billdata=M('bill')->where(array('id'=>$bill_id))->find();
                $balance=M('balance_bill')->where(array('bill_id'=>$bill_id,'type'=>1))->find();
                if($billdata['buyer']!=$vip_id or $billdata['state']!=2){
                    $ret['code']=3;
                    $ret['message']='提煤单状态异常,可能是司机还没有接单';
                    backJson($ret);
                }else{
                    $data['state']=5;
                    $data['begin_first_time']=$balance['add_time'];
                    $data['begin_second_time']=$balance['add_time'];
                    $data['end_first_time']=date('Y-m-d H:i:s');
                    $data['begin_w']=$begin_w;
                    $res=M('bill')->where(array('id'=>$bill_id))->save($data);
                    $truck['state']=3;
                    $res1=M('truck')->where(array('id'=>$billdata['truck_id']))->save($truck);
                    $res2=M('balance_bill')->where(array('bill_id'=>$bill_id,'type'=>1))->save(array('state'=>1));
                    if($res!==false and $res1!==false and $res2!==false){
                        $billdata['seller']=M('company')->where(array('id'=>$billdata['seller']))->getField('name');
                        $billdata['buyer']=M('company')->where(array('id'=>$billdata['buyer']))->getField('name');
                        $billdata['truck_owner_id']=M('truck')->where(array('id'=>$billdata['truck_id']))->getField('owner_order');
                        $billdata['driver']=M('users')->where(array('id'=>$billdata['driver_id']))->getField('real_name');
                        unset($billdata['state']);
                        unset($billdata['driver_id']);
                        $ret['detail']=$billdata;
                        $ret['code']=1;
                        $ret['message']='确认成功,车辆可进入卸货';
                        backJson($ret);
                    }else{
                        $ret['code']=4;
                        $ret['message']='确认失败,可稍后再试';
                        backJson($ret);
                    }
                }
                break;
            default:
                $ret['code']=0;
                $ret['message']='信息提交有误';
                backJson($ret);
        }
    }
    /**
     * 提煤单历史统计
     * @param int $trucker_id 车主ID搜索
     * @param int $driver_id 司机ID搜索
     * @param int $begin_time 开始时间
     * @param int $end_time 结束时间
     * @param int $lic_number  车牌号
     * @param int $company_id  物流公司
     * @param int $seller_id  提货点公司ID
     * @param int $buyer_id  送货点公司ID
     * @param int $anchored_id  车辆挂靠公司ID
     * @param int $anchored_id  $assign_id负责物流的贸易商ID
     * @param int $anchored_id  跟$assign_id配合使用如果为true则是负责物流如果为false则不负责物流
     */
    function history($trucker_id,$driver_id,$begin_time,$end_time,$lic_number,$seller_id,$buyer_id,$company_id,$anchored_id,$assign_id,$is_assign=true){
        //用车主筛选时先查看车主旗下的车辆
        {
            $trucks_arr=M('truck')->where(array('owner_id'=>$trucker_id,'owner_type'=>1))->field('id')->select();
            $trucks=array();
            foreach ($trucks_arr as $k=>$v){
                $trucks[$k]=$v['id'];
            }
            $trucks_str=implode(',',$trucks);
        }

        //从负责物流查看
        {
            if($is_assign){
                //负责物流
                $orders_arr=M('orders')->where("buyer_id ='".$assign_id."' or seller_id ='".$assign_id."'")->where(array('log_id'=>$assign_id))->field('order_id')->select();
            }else{
                //不负责物流
                $orders_arr=M('orders')->where("buyer_id ='".$assign_id."' or seller_id ='".$assign_id."'")->where(array('log_id'=>array('neq',$assign_id)))->field('order_id')->select();
            }
            $order=array();
            foreach ($orders_arr as $k=>$v){
                $order[$k]=$v['order_id'];
            }
            $order_str=implode(',',$order);
        }

        $truck_id=M('truck')->where(array('lic_number'=>$lic_number))->getField('id');
        $where1=array('state'=>6);
        if($truck_id){
            $where1=array_merge($where1,array('truck_id'=>$truck_id));
            $where3=array();
        }else{
            if($trucks_str){
                $where3['truck_id']  = array('in',$trucks_str);
            }else{
                $where3=array();
            }
        }

        if($seller_id){
            $where1=array_merge($where1,array('seller'=>$seller_id));
        }
        if($buyer_id){
            $where1=array_merge($where1,array('buyer'=>$buyer_id));
        }
        if($anchored_id){
            $where1=array_merge($where1,array('anchored_id'=>$anchored_id));
        }
        if($company_id){
            $where1=array_merge($where1,array('company'=>$company_id));
        }
        if($driver_id){
            $where1=array_merge($where1,array('driver_id'=>$driver_id));
        }
        if($begin_time and $end_time){
            $where2="end_second_time > '".$begin_time."' and end_second_time < '".$end_time."'";
        }else{
            $where2=array();
        }
        if($assign_id){
            $where4['order_id']  = array('in',$order_str);
        }else{
            $where4=array();
        }

        $rows=M('bill')
            ->where($where1)
            ->where($where2)
            ->where($where3)
            ->where($where4)
            ->field('dis_time,end_w,begin_w,order_id,id,end_second_time,end_second_time')
            ->select();
        //煤种
        //卖家,买家
        //耗时
        foreach ($rows as $key =>$value){
            $rows[$key]['sun_w']=$value['begin_w']-$value['end_w']>0?$value['begin_w']-$value['end_w']:0;
            $rows[$key]['sun_w']=round($rows[$key]['sun_w'],2);
            unset($rows[$key]['begin_w']);
            $order=M('orders')->where(array('order_id'=>$value['order_id']))->find();
            if(!$order){
                $rows[$key]['coal_type']='煤种未知';
                $rows[$key]['seller']='提货点未知';
                $rows[$key]['buyer']='送货点未知';
            }else{
                $rows[$key]['coal_type']=coal_type($order['coal_type']);
                $rows[$key]['seller']=M('company')->where(array('id'=>$order['seller_id']))->getField('name');
                $rows[$key]['buyer']=M('company')->where(array('id'=>$order['buyer_id']))->getField('name');
            }
            //耗时
            $time=strtotime($value['end_second_time'])-strtotime($value['dis_time']);
            $rows[$key]['time']=usetime($time);
        }
        return $rows;
    }
    /**
     * 获取有拉货记录的物流公司
     */
    function getCompanys(){
        $companys=M('bill b')->join('coal_company c on b.company=c.id')
            ->where(array('state'=>6))->field('c.name as name,c.id as id')
            ->group('id')
            ->select();
        return $companys;
    }

    /**
     * 手动结算提煤单
     * @param integer $bill_id     提煤单id
     * @param double $begin_weight 提货点净重
     * @param string $begin_time   进矿点时间
     * @param double $end_weight   送货点净重
     * @param string $end_time     送货点时间
     * @return bool
     */
    public function manualSettlement($bill_id, $begin_weight, $begin_time, $end_weight, $end_time){
        // 1、修改balance_bill.state
        $res1 = M('balance_bill')->where(array('bill_id' => $bill_id))->save(array('state' => 2));
        // echo M()->_sql();exit;die();
        // 2、bill表
        $data = array(
            'begin_first_time'  => $begin_time,
            'begin_second_time' => $begin_time,
            'begin_first_w'     => 15,
            'begin_second_w'     => 15 + $begin_weight,
            'begin_w'     => $begin_weight,
            'end_first_time'  => $end_time,
            'end_second_time'  => $end_time,
            'end_first_w'     => 15 + $end_weight,
            'end_second_w'     => 15,
            'end_w'  => $end_weight,
            'state'  => 6
        );
        $res2 = M('bill')->where(array('id' => $bill_id))->save($data);
        // 3、truck表
        $bill_info = M('bill')->find($bill_id);
        $res3 = M('truck')->where(array('id' => $bill_info['truck_id']))->save(array('state' => 1, 'last_time' => get_time()));
        if ($res1 !== false && $res2 !== false && $res3 !== false) {
            D('Push', 'Logic')->acceptBlance($bill_info['driver_id']);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 废弃货单，同意退单
     * @param $bill_id
     * @return bool
     */
    public function returnPass($bill_id){
        // 1、司机退单表
        $res1 = M('driver_rbill')->where(array('bill_id' => $bill_id))->save(array('state' => 2));
        $bill_info = M('bill')->find($bill_id);
        // 2、bill表状态变为废弃state = 7
        $res2 = M('bill')->where(array('id' => $bill_id))->save(array('state' => 7));
        // 3、车辆表，变成空车，上次时间
        $res3 = M('truck')->where(array('id' => $bill_info['truck_id']))->save(array('last_time' => get_time(), 'state' => 1));
        if ($bill_info['create_type'] != 1) {
            // 4、物流计划 剩余安排吨数回归。
            $res4 = M('logistics')->where(array('id' => $bill_info['logistics_id']))->setInc('res_quantity', $bill_info['arrange_w']);
        } else {
            $res4 = true;
        }
        if ($res1 !== false && $res2 !== false && $res3 !== false && $res4 !== false) {
            D('Push', 'Logic')->acceptRBill($bill_info['driver_id']);
            return true;
        } else {
            return false;
        }
    }

}