<?php
namespace Api\Controller;
use Think\Controller;
class ApiController extends Controller {
    //做常规Api验证
    public function _initialize()
    {
        //参数coalt做使时间验证
        $coalt=I('coalt');
        $server_t=md5(date('Y-m-d'));
//        if($coalt!=$server_t){
//            $ret['code']=-1;
//            $ret=json_encode($ret);
//            die($ret);
//        }
    }
}