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
class UsersModel extends BaseModel{

    // 自动验证
    protected $_validate=array(
        //必填验证
        array('phone','require','手机号必须',0,'',1),
        array('account','require','账号必须',0,'',3 ),
        array('password','require','密码必须',0,'',1 ),
        array('repassword','require','确认密码必须',0,'',1 ),
        //唯一性验证
        array('phone','','手机号已存在',0,'unique',1),
        array('account','','账号已经存在',0,'unique',1),
        //其他验证
        array('password','checkPwd','密码格式不正确',0,'function'),
        array('phone','checkPhone','手机号格式不正确',0,'function'),
        array('repassword','password','确认密码不正确',0,'confirm'),
        array('sex',array(1,2),'类型提交有误',2,'in'), // 当值不为空的时候判断是否在一个范围内
    );

    // 自动完成
    protected $_auto=array(
        array('create_time','get_time',1,'function'), // 对date字段在新增的时候写入当前时间戳
        array('password','authcode',1,'function'), //
        array('account','createAcc',1,'callback'),
        array('login_ip','getIpAddress',1,'function'),//注册ip填写成login_ip
    );
    /**
     * 添加用户
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
        if(!$data=$this->create($data,2)){
            // 验证不通过返回错误
            $res['code']=0;
            $res['message']=$this->getError();
            return $res;
        }else{
            // 验证通过
            $result=$this
                ->where($map)
                ->save($data);
            $res['code']=1;
            $res['message']=$result;
            return $res;
        }
    }
    //生成账号
    function createAcc(){
        do{
            $account = rand(666666,999999);
            $res = $this->where(array('account' => $account))->find();
        }while($res);
        return $account;
    }
    //手机号是否存在
    function isPhoneUnique($phone){
        $res = $this->where(array('phone' => $phone))->find();
        return $res?true:false;
    }
}