<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Auth;

class CommonController extends Controller {

    function __construct(){
        parent::__construct();
        //最开始的所有操作
        //是否登录
        if(!session('sys_uid')){
//             $this->error('您没有登录后台，请登录!',U('Admin/Login/login'),1); // session失效后，error方法出错
            // A('Login')->logout();
            alert('您没有登录后台，请登录!',301);
        }
        //权限验证
        $auth = new Auth();
        $rule_name = MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME;
        $status = M('admin_auth_rule')->where(array('name' => $rule_name))->getField('status');
        if (1 == $status) {
            $result = $auth->check($rule_name, session('sys_uid'));
            if (!$result) {
                // echo $rule_name;exit;
                // addRule($rule_name, 0, 1); // 正式要去掉
                alert('您没有后台访问权限', 300);
            }
        }
    }
}