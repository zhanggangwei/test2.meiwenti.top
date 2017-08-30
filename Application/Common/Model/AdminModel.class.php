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
 *管理员表基础model
 */
class AdminModel extends BaseModel{
    // 自动验证
    protected $_validate=array(
        array('phone','require','手机号必须',0,'',3), // 验证字段必填
        array('phone','','手机号已存在',0,'unique',3), // 验证字段必填
        array('password','checkPwd','密码格式不正确',0,'function'), // 验证字段必填
        array('repassword','password','确认密码不正确',0,'confirm',1), // 验证字段必填
    );

    // 自动完成
    protected $_auto=array(
        array('create_time','get_time',1,'function'), // 对date字段在新增的时候写入当前时间戳
        array('password','authcode',1,'function'), //
        array('account','createAcc',1,'callback'),
    );

    /**
     * 添加用户
     */
    public function addData($data){
        // 对data数据进行验证
        if(!$data=$this->create($data)){
            // 验证不通过返回错误
            // 是使用它的false还是给出具体的错误？2017年3月24日0:08:05
//             return $this->getError();
            $res['code']=false;
            $res['info']=$this->getError();
        }else{
            // 验证通过
            $res['code']=true;
            $res['info']=$this->add($data);
        }
        return $res;
    }

    /**
     * 修改用户
     */
    public function editData($map,$data){
        // 对data数据进行验证
        if(!$data=$this->create($data)){
            // 验证不通过返回错误
            $res['code'] = false;
            $res['info'] = $this->getError();
        }else{
            // 验证通过
            $result=$this
                ->where(array($map))
                ->save($data);
            $res['code'] = true;
            $res['info'] = $result;
        }
        return $res;
    }

    /**
     * delData 删除用户,逻辑删除
     * @param  int $id 用户id,userid
     * @return boolean     
     */
     public function delData($id){
         //锁定用户(或将来增加一个字段is_delete)
//         $result = $this->where(array('id' => $id))->save(array('is_lock' => 1));
         //2017年3月27日16:11:12 zjw 确定可以删
         $result = $this->delete($id);
         return $result;
     }

    /**
     * getAdministrators 获取管理员，
     * @param int $map 条件
     * @return array
     */
//    public function getAdministrators($map){
//        $result = $this->where($map)->order('create_time desc, id desc')->select();
//        return $result;
//    }
//生成账号
    function createAcc(){
        do{
            $account = rand(666666,999999);
            $res = $this->where(array('account' => $account))->find();
        }while($res);
        return $account;
    }
}