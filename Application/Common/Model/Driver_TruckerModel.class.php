<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/3/22
 * Time: 17:33
 */
namespace Common\Model;
use Think\Model\RelationModel;

/**
 *司机model
 */
class Driver_TruckerModel extends RelationModel {
    protected $tableName = 'driver_trucker';
    // 自动验证
    protected $_validate=array(
        //必填验证
        array('trucker_id','require','车主ID必须',0,'',3),
        array('driver_id','require','司机ID必须',0,'',3 ),
    );

    // 自动完成
    protected $_auto=array(
        array('create_time','today_day',1,'function'), // 对date字段在新增的时候写入当前时间戳
    );

    /**
     * 添加数据
     */
    public function addData($data){
        // 对data数据进行验证
        if(!$data=$this->create($data,1)){
            // 验证不通过返回错误
            $res['code']=0;
            $res['message']=$this->getError();
            return $res;
        }else{
            // 验证通过
            $result=$this->add($data);
            $res['code']=1;
            $res['id']=$result;
            return $res;
        }
    }

}