<?php
namespace Vip\Controller;
use Think\Controller;

class CommonController extends Controller {

    function __construct(){
        parent::__construct();
        //最开始的所有操作
        //是否登录
        if(!session('user_id')){
            // header('status:999');exit;
            alert('您没有登录，请登录!',301);
        }
        //权限验证
        $rule_name = MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
        // echo $rule_name;
        if(!checkVipAuth($rule_name)){
            // addRule($rule_name);
            $this->error('您没有访问权限'.$rule_name);
        }
    }
}