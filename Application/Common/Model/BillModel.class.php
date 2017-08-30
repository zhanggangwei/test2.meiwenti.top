<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/3/22
 * Time: 17:33
 */
namespace Common\Model;
//use Common\Model\BaseModel;

/**
 *提煤单基础模型
 */
class BillModel extends BaseModel{
    // 自动验证
    protected $_validate=array(
        //必填验证
        array('buyer','require','送货方必须',0,'',3),
        array('seller','require','提货点必须',0,'',3),
        array('order_id','require','订单号必须',0,'',3),
        array('company','require','物流公司必须',0,'',3),
        array('logistics_id','require','计划id必须',0,'',3),
        array('truck_id','require','车辆必须',0,'',3),
        array('dis_time','require','下发时间必须',0,'',3),
        array('arrange_w','require','指派吨数必须',0,'',3),
        //唯一性验证
       // array('order_id','','订单号已存在',0,'unique',1),
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
        array('dis_time','get_time',1,'function'), // 对date字段在新增的时候写入当前时间戳
    );

    /**
     * 添加提煤单
     */
    public function addData($data){
        // 对data数据进行验证
        if(!$data=$this->create($data)){
            // 验证不通过返回错误
            // 是使用它的false还是给出具体的错误？2017年3月24日0:08:05
//             return $this->getError();
            $res['code']=false;
            $res['message']=$this->getError();
        }else{
            // 验证通过
            $res['code']=true;
            $res['message']=$this->add($data);
        }
        return $res;
    }
}