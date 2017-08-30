<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/3/22
 * Time: 17:33
 */
namespace Common\Model;
use Think\Model\RelationModel; // 2017年4月19日11:46:16 zgw edit 继承的RelationModel 不知道可不可用，换成了BaseModel.以后如果有错，再改回来

/**
 *司机model
 */
class TruckModel extends BaseModel {

    // 自动验证
    protected $_validate=array(
        //必填验证
        array('lic_date','require','发证日期必须',1,'',3),
        array('owner_id','require','拥有者id必须',1,'',3 ),
        array('lic_pic','require','证件图片必须',1,'',1 ),
        array('lic_number','require','证件号必须',1,'',1 ),
        //唯一性验证
        array('lic_number','','证件号已存在',1,'unique',1),
        // array('owner_order','','内部编号已存在',1,'unique',1), // 2017年4月29日1:08:08 zgw 不启用原因是，内部编号还要根据公司来定。  问：是否要做到全部一致？
        //其他验证
    );

    // 自动完成
    protected $_auto=array(
        array('create_time','get_time',1,'function'), // 对date字段在新增的时候写入当前时间戳
    );

    /**
     * 添加车辆
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
     * 修改车辆
     */
    public function editData($map,$data){
        // 对data数据进行验证
        if(!$data=$this->create($data)){
            // 验证不通过返回错误
            $res['code'] = 0;
            $res['message'] = $this->getError();
            return $res;
        }else{
            // 验证通过
            $result=$this
                ->where(array($map))
                ->save($data);
            $res['code'] = 0;
            $res['info'] = $result;
            return $res;
        }
    }

    // 当前公司的车辆编号（车小号）是否有重复
    public function isOwnerOrderRepeat($number, $company_id){
        $res = $this->where(array('owner_order' => $number, 'owner_id' => $company_id))->find();
        return $res?true:false;
    }

}