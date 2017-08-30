<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/4/20
 * Time: 9:30
 */
namespace Common\Model;

class CoalTypeModel extends BaseModel {
    // 自动验证
    protected $_validate=array(
        //必填验证
        array('name','require','煤种名称必须',0,'',1),
        // array('account','require','账号必须',0,'',3 ),
        // array('password','require','密码必须',0,'',1 ),
        // array('repassword','require','确认密码必须',0,'',1 ),
        // //唯一性验证
        array('name','','煤种名称已存在',0,'unique',1),
        // array('account','','账号已经存在',0,'unique',1),
        // //其他验证
        // array('password','checkPwd','密码格式不正确',0,'function'),
        // array('phone','checkPhone','手机号格式不正确',0,'function'),
        // array('repassword','password','确认密码不正确',0,'confirm'),
        // array('sex',array(1,2),'类型提交有误',2,'in'), // 当值不为空的时候判断是否在一个范围内
    );
    //
    // // 自动完成
    // protected $_auto=array(
    //     array('update_time','today_day',3,'function')
    // );

    public function addData($data){
        // 对data数据进行验证
        if(!$data=$this->create($data)){
            // 验证不通过返回错误
            $res['code']=false;
            $res['message']=$this->getError();
        }else{
            // 验证通过
            $res['code']=true;
            $res['message']=$this->add($data);
        }
        return $res;
    }

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