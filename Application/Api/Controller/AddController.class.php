<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/30
 * Time: 13:44
 * 车主控制器
 */
namespace Api\Controller;
use Think\Controller;
class AddController extends ApiController {
    /********************************************注册*********************************************/
    public function getAprovince()
    {
        $ret['code']=1;
        $ret['data']=get_provice();
        echo $ret=json_encode($ret);
    }
    public function getShiqu(){
        if(!I('province')){
            $ret['code']=0;
            $ret['message']='提交数据为空';
            echo $ret=json_encode($ret);
        }else{
            $province=I('province');
            $ret['code']=1;
            $ret['data']=!get_shiqu($province)?array('请选择','其他'):get_shiqu($province);
            echo $ret=json_encode($ret);
        }
    }
    public function getDiqu(){
        if(!I('province')||!I('shiqu')){
            $ret['code']=0;
            $ret['message']='提交数据为空';
            echo $ret=json_encode($ret);
        }else {
            $province = I('province');
            $city = I('shiqu');
            $ret['code']=1;
            $ret['data'] = !get_diqu($province, $city)?array('请选择','其他'):get_diqu($province, $city);
            echo $ret = json_encode($ret);
        }
    }
}