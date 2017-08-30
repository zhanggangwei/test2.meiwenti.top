<?php
namespace Admin\Controller;
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
       if(!check_verify($code)) $this->error('验证码错误', U('Login/login'));//暂不开启

        $account = I('post.account','','trim,string,htmlspecialchars');
        $passwd = I('post.password','','trim,authcode');
        $db = M('admin u');
        $userinfo = $db->where("(account = '$account' or phone = '$account') AND password = '$passwd'")->find();
        if(!$userinfo) $this->error('用户名或密码错误', U('Admin/Login/login'));
        if($userinfo['is_lock']) $this->error('该用户被锁定，暂时不可登录', U('Admin/Login/login'));
        //更新登录信息
        $db->save(array("id"=> $userinfo["id"], "login_time"=> get_time(), "login_ip" => get_client_ip()));
        //用户角色信息
        $roleinfo = $db->field('g.*')
            ->join('left join '.C('AUTH_CONFIG.AUTH_GROUP_ACCESS').' aga on aga.uid = u.id')
            ->join('left join '.C('AUTH_CONFIG.AUTH_GROUP').' g on g.id = aga.group_id')
            ->where(array('u.id' => $userinfo['id']))->find();
        //写入session值
        session('sys_uid', $userinfo["id"]);
        session("sys_account", $userinfo["account"]);
        session("sys_nickname", $userinfo["nickname"]);
        // session("email", $userinfo["email"]);
        session("sys_company", $userinfo["company"]);
        session("sys_logintime", $userinfo["login_time"]);
        session("sys_loginip",$userinfo["login_ip"]);
        session("sys_role",$roleinfo["id"]);
        session("sys_rolename",$roleinfo["title"]);

        $this->redirect('Admin/Index/index');exit;

    }

    //登出登录
    public function logout(){
        if(isset($_SESSION['sys_uid'])) {
            unset($_SESSION['sys_uid']);
            unset($_SESSION);
            session_destroy();
            $this->redirect('Admin/Login/login');exit;
        }else {
            $this->redirect('Admin/Login/login');exit;
        }
    }

    //修改密码(示例有完整的)
    public function changePasswordView(){
        $uid = session('user_id');
        $password = I('post.password','','trim,md5');;
        $repassword = I('post.repassword','','trim,md5');;
        if ($password == $repassword){
            M('users')->save(array(
                'id' => $uid,
                'password' => $password
            ));
        }else{
            return false;
        }
    }

    public function changePassword($password, $repassword){
        $uid = session('user_id');
        $password = I('post.password','','trim,md5');;
        $repassword = I('post.repassword','','trim,md5');;
        if ($password == $repassword){
            M('users')->save(array(
                'id' => $uid,
                'password' => $password
            ));
        }else{
            return false;
        }
    }

    //注册
    public function regist()
    {
        $_POST['phone'] = '1367671'.rand(1000,9999);
        $_POST['password'] = '123456';
        $_POST['repassword'] = '123456';
        $_POST['type'] = '1';

        $res = D('Admin')->addData($_POST);
        dump($res);exit;

    }
}
