<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/3/28
 * Time: 9:30
 */
namespace Common\Model;

class OrdersModel extends BaseModel {

    // 自动验证
    protected $_validate=array(
        //必填验证
        array('order_id','require','订单号必须',0,'',3),
        array('buyer_id','require','买家必须',0,'',3),
        array('seller_id','require','卖家必须',0,'',3),
        //唯一性验证
        array('order_id','','订单号已存在',0,'unique',1),
//        array('account','','账号已经存在',0,'unique',1),
        //其他验证
        // array('buyer_id','get_user_id','',1,'function'),
        // array('seller_id','get_user_id','',1,'function'),
        // array('begin_gps','getGeo','',1,'function'),
        // array('end_gps','getGeo','',1,'function'),
//        array('phone','checkPhone','手机号格式不正确',0,'function'),
//        array('repassword','password','确认密码不正确',0,'confirm'),
    );

    // 自动完成
    protected $_auto=array(
        array('create_time','today_day',1,'function'),
    );

    public function addData($data){
        $gps = $data['gps'];
        $gps1 = $data['gps1'];
        // 对data数据进行验证
        if(!$data=$this->create($data)){
            // 验证不通过返回错误
            $res['code']=false;
            $res['message']=$this->getError();
        }else{
            // 验证通过
            $res['code']=true;
            M()->startTrans();
            // echo json_encode($data);exit;
            $res['message']=$this->add($data);
            $res1 = save_order_gps($gps, $gps1, $res['message']);
            if ($res['message'] && $res1) {
                M()->commit();
            } else {
                M()->rollback();
            }
        }
        return $res;
    }

    public function editData($map,$data){
        // 对data数据进行验证
        if(!$data=$this->create($data, 2)){
            // 验证不通过返回错误
            $res['code'] = false;
            $res['message'] = $this->getError();
        }else{
            // 验证通过
            $result=$this
                ->where(array($map))
                ->save($data);
            $res['code'] = true;
            $res['message'] = $result;
        }
        return $res;
    }

    // 获得订单总量
    public function quantity($order_id){
        return $this->where(array('order_id' => $order_id))->getField('quantity');
    }

    // 订单是否异常
    public function isAbnormal($order_id){
        $res = $this->where(array('order_id' => $order_id, 'assign_id' => session('company_id')))->find();
        return $res?false:true;
    }
}