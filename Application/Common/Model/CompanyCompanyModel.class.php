<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/5/8
 * Time: 15:33
 */
namespace Common\Model;

/**
 * 合作公司表基础model
 */
class CompanyCompanyModel extends BaseModel{

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
     * 修改
     */
    public function editData($map,$data){
        // 对data数据进行验证
        if(!$data=$this->create($data)){
            // 验证不通过返回错误
            $res['code']=0;
            $res['message']=$this->getError();
            return $res;
        }else{
            // 验证通过
            $result=$this
                ->where(array($map))
                ->save($data);
            $res['code']=1;
            $res['message']=$result;
            return $res;
        }
    }

    /**
     * 传入的公司与当前是否可以合作
     * @param $company_id
     */
    public function isCanComperation($company_id){
        $current = session('company_id');

        // 公司是否存在
        $info = M('company')->find($company_id);
        if (!$info) {
            return array('code' => 0, 'message' => '公司不存在');
        }
        // 公司是否审核
        if ($info['is_passed'] == 0) {
            return array('code' => 0, 'message' => '公司未审核');
        }
        // 是否已经合作
        $sql = "select * from coal_company_company where (company1 = $company_id and company2 = $current) or (company1 = $current and company2 = $company_id)";
        // echo $sql;exit;
        $res = M()->query($sql);
        switch ($res[0]['status']) {
            case 1:
                return array('code' => 0, 'message' => '该公司已经与本公司合作');
                break;
            case 2:
                return array('code' => 0, 'message' => '该公司已经与本公司合作,待确认');
                break;
            case 3:
                return array('code' => 0, 'message' => '该公司已经与本公司合作,已拒绝');
                break;
            default:
                break;
        }
        return array('code' => 1, 'message' => '可以合作', 'info' => $res);
    }

    public function isExists($company_id){
        $info = $this->find($company_id);
        if ($info) {
            return array('code' => 1, 'message' => '存在');
        } else {
            return array('code' => 0, 'message' => '不存在');
        }
    }

}