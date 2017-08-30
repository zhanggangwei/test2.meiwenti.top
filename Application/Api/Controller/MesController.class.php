<?php
namespace Api\Controller;
use Think\Controller;
class MesController extends ApiController {
    /**************************************验证码*****************************/
    public function regSendCode(){
        if(!I('tel_num')){
            $ret['code']=0;
            $ret['message']='必要参数不能为空';
            die(json_encode($ret));
        }
        //生产随机码
        $ver_num=rand_num(6);
        session('ver_num',$ver_num);
        $phone_num=I('tel_num');
        session('ver_phone',$phone_num);
        //引入类库
          vendor('TopSdk.TopSdk');
          $mes=new \Message();
        if($mes->regSendCode($phone_num,$ver_num)){
            $ret['code']=1;
            $ret['message']='发送成功';
            $ret['ver_num']=session('ver_num');
            die(json_encode($ret));
        }else{
            $ret['code']=0;
            $ret['ver_num']=session('ver_num');
            $ret['message']='发送失败';
            die(json_encode($ret));
        }
    }
    public function sendcode_for_hzf(){
        //黄宗富专用
        $ver_num='888888';
        $phone_num='13682341896';
        //引入类库
        vendor('TopSdk.TopSdk');
        $mes=new \Message();
        if($mes->regSendCode($phone_num,$ver_num)){
            $ret['code']=1;
            $ret['message']='发送成功';
            $ret['ver_num']=session('ver_num');
            die(json_encode($ret));
        }else{
            $ret['code']=0;
            $ret['ver_num']=session('ver_num');
            $ret['message']='发送失败';
            die(json_encode($ret));
        }
    }
}