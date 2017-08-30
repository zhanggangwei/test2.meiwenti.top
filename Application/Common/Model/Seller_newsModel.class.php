<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/3/22
 * Time: 17:33
 */
namespace Common\Model;
//use Common\Model\BaseModel;

/**
 *用户表基础model
 */
class Seller_newsModel extends BaseModel{

    // 自动验证
    protected $_validate=array(
        //必填验证
        array('title','require','标题必须填写',1,'',1),
        array('quantity','require','供应量必须填写',1,'',1),
        array('del_time_from','require','交货起始时间必须填写',1,'',1),
        array('del_time_to','require','交货结束时间必须填写',1,'',1),
        array('coal_type','require','煤种必须填写',1,'',1),
        array('dwrz','require','地位热值必须填写',1,'',1),
        array('sdjlf','require','收到基硫分必须填写',1,'',1),
        array('payment_mode','require','收款方式必须填写',1,'',1),
        array('del_mode','require','交货方式必须填写',1,'',1),
        array('del_price','require','价格必须填写',1,'',1)
    );
    // 自动完成
    protected $_auto=array(
        array('creat_time','today_day',1,'function'), // 对date字段在新增的时候写入当前时间戳
    );
    /*
     * 添加
     */
    public function addData($data){
        // 对data数据进行验证
        if(!$data=$this->create($data)){
            $res['code']=0;
            $res['message']=$this->getError();
            return $res;
        }else{
            // 验证通过
            $result=$this->add($data);
            $res['code']=1;
            $res['message']='发布成功';
            return $res;
        }
    }
}