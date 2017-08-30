<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/3/28
 * Time: 9:30
 */
namespace Common\Model;

class PowerInModel extends BaseModel {
    protected $tableName = 'power_in';
    // 自动验证
    protected $_validate=array(
        array('name','require','电厂名字必须',0,'',3), // 验证字段必填
        array('name','','电厂名字已存在',0,'unique',3), // 验证字段必填
//        array('password','checkPwd','密码格式不正确',0,'function'), // 验证字段必填
//        array('repassword','password','确认密码不正确',0,'confirm'), // 验证字段必填
    );

    // 自动完成
    protected $_auto=array(
        array('update_time','today_day',3,'function')
    );

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