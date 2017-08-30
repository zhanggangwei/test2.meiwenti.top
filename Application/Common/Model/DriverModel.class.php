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
class DriverModel extends RelationModel {

    // 自动验证
    protected $_validate=array(
        //必填验证
        array('uid','require','用户id必须',0,'',3),
        array('name','require','名字必须',0,'',3 ),
        array('lic_pic','require','证件图片必须',0,'',1 ),
        array('lic_number','require','证件号必须',0,'',1 ),
        array('lic_level','require','驾驶证级别必须',0,'',1 ),
        //唯一性验证
        array('lic_number','','证件号已存在',0,'unique',1),
        array('uid','','已经进行过司机认证',0,'unique',1),
        //其他验证
    );

    // 自动完成
    protected $_auto=array(
        array('create_time','get_time',1,'function'), // 对date字段在新增的时候写入当前时间戳
    );

    /**
     * 添加用户
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

    /**
     * 修改用户
     */
    public function editData($map,$data){
        // 对data数据进行验证
        if(!$data=$this->create($data)){
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
}