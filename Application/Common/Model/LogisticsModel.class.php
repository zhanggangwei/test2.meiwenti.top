<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/4/10
 * Time: 14:06
 */

namespace Common\Model;

/**
 * 物流安排
 */
class LogisticsModel extends BaseModel {

    protected $_validate = array();

    protected $_auto = array(
        array('create_time','get_time',1,'function')
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

    public function editData($map,$data)
    {
        // 对data数据进行验证
        if (!$data = $this->create($data)) {
            // 验证不通过返回错误
            $res['code'] = false;
            $res['message'] = $this->getError();
        } else {
            // 验证通过
            $result = $this
                ->where(array($map))
                ->save($data);
            $res['code'] = true;
            $res['message'] = $result;
        }
        return $res;
    }

    // 上次物流计划是否完成
    public function isLastFinshed($assigned_id, $order_id){
        $where = array(
            'order_id'      => $order_id, 
            'assigned_id'   => $assigned_id, 
            'state'         => 1,
            'res_quantity'  => array('gt', 0)
            );
        $logistics = M('logistics')->where($where)->find();
        // sql();
        return $logistics?false:true;
    }

    // 物流计划是否收回正常
    public function isBackNormal($logis_id){
        $res = $this->where(array('id' => $logis_id, 'state' => 1))->find();
        return $res?$res:false;
    }


}