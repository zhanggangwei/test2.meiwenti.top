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
 * 物流合作车主表基础model
 */
class CompanyTruckerModel extends BaseModel{

    // 自动验证
    protected $_validate=array(
        //必填验证
        // array('is_vip','require','认证类型必须',0,'',3),
        // array('name','require','名字必须',0,'',3 ),
        // array('lic_pic','require','证件图片必须',0,'',1 ),
        //唯一性验证
        //其他验证
    );

    // 自动完成
    protected $_auto=array(
        array('status','2',1), // 新增默认为2，（规则有待检验）
        array('codate','today_day',1,'function'), // 对date字段在新增的时候写入当前时间戳
    );
    /**
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
            return false;
        }else{
            // 验证通过
            $result=$this
                ->where(array($map))
                ->save($data);
            return $result;
        }
    }

}