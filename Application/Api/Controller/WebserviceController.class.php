<?php
/**
 * Created by PhpStorm.
 * User: 王佩佩
 * Date: 2017/7/18
 * Time: 11:13
 */

namespace Api\Controller;
use Think\Controller;

class WebserviceController extends ApiController
{
    //进行类注册
     function server(){
         $server = new \SoapServer('mwt.wsdl');
         $server->setClass('Api\Controller\WebserviceController');
         $server->setPersistence(SOAP_PERSISTENCE_SESSION);
         $server->handle();
     }

    //退单
    function acceptRebill($bill_number) {

        if (!$bill_number) {
            $ret['code'] = 0;
            $ret['message'] = '提交参数有误';
            return $ret;
        }
        //跨控制器
        $controller=A('AccController');
        $res=$controller->acceptRebill();
        // 2017年7月2日13:41:31 zgw 为了实现车队只用打电话退单，系统就能自动获取到，不用手动再点，做此优化
        $yitai_bill = M('link_yitai')->where(array('bill_number'=>trim($bill_number)))->find();
        if (!$yitai_bill) {
            $ret['code'] = 0;
            $ret['message'] = '没有伊泰生成的单';
            return $ret;
        }
        $bill_id = $yitai_bill['bill_id'];

        // 2017年6月21日15:26:58 edit zgw 只做改动，没有记录。在reason做记录
        $rebill = M('driver_rbill')->where(array('bill_id' => $bill_id))->find();
        // 2、reason记录伊泰操作
        if ($rebill) {
            $res2 = M('driver_rbill')->save(array('id' => $rebill['id'], 'reason' => $rebill['reason'] . '（伊泰已经退单）'));
        } else {
            // 生成一个退单记录
            $data['return_time'] = date("Y-m-d H:i:s");
            $data['bill_id']      = $bill_id;
            $data['state']        = 5;
            $data['reason']       = '伊泰系统主动退单，系统已处理';
            $res2 = M('driver_rbill')->add($data);
        }
        // 1、正常废弃货单
        $res=D('Bill','Logic')->returnPass($bill_id);

        if($res && $res2 !== false){
            // 拿到单的车辆，判断预暂停
            $truck_id = M('bill')->where(array('id' => $bill_id))->getField('truck_id');
            is_truck_cron($truck_id, 'return');
            $ret['code']=1;
            $ret['message']='处理成功';
            return $ret;
        }else{
            $ret['code']=0;
            $ret['message']='处理失败';
            return $ret;
        }
    }

    //串矿
    /*buyer->买方
    seller->卖方
    coal_code->煤矿编号
    coal_type->煤种
    */
    function changeBure($buyer,$seller,$coal_code,$coal_type,$time)
    {
        return '退单';
    }


    //生成wsdl文件
    function make_wsdl(){
        vendor("Soap.Soap");
        $disc = new \SoapDiscovery('Home\Controller\WebserviceController', 'soap');
        $disc->getWSDL();
        echo '生成成功';
    }
}