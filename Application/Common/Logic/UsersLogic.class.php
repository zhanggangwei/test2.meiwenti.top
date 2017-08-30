<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/3/22
 * Time: 17:33
 */
namespace Common\Logic;
//use Common\Model\BaseModel;
class UsersLogic extends \Think\Model{
    /**
     * 账户登录
     * @param $account   用户名
     * @param $password  密码
     * @return array
     *      1  登录成功
     *      2  账户或密码不正确
     *      3  该用户被锁定，暂时不可登录
     */
    function login($account,$password){
        $users = M('users');
        //处理数据，防止sql注入
        $account = htmlspecialchars(trim($account));
        $password = authcode(htmlspecialchars(trim($password)));
        //进入数据库，验证账号和密码
        $user = $users->where("(account = '$account' or phone = '$account') AND password = '$password'")->find();
        if(!$user) return(array('code'=>0,'message'=>'账户或密码不正确'));
        if($user['is_lock']) return(array('code'=>0,'message'=>'该用户被锁定，暂时不可登录'));
        //更新登录信息
        $users->save(array("id"=> $user["id"], "login_time"=> get_time(), "login_ip" => get_client_ip()));

        //写入session
        session('user',$user['account']);
        session('phone',$user['phone']);
        session('user_id',$user['id']);
        //session跨域
        setcookie("session_id",session_id(),time()+3600*24*15,"/",".meiwenti.top");
        //更新登录时间
        $data['login_time']=date('Y-m-d H:i:s');
        M('users')->where(array('id'=>session('user_id')))->save($data);
        //返回用户注册入口
        return array('code'=>1,'message'=>'登录成功','type'=>$user['type'],'user_id'=>$user['id'],'is_authentication'=>$user['is_authentication']);
    }
    /**
     * 过磅系统登录,验证的密码不一样
     * @param $account   用户名
     * @param $password  密码
     * @return array
     *      1  登录成功
     *      2  账户或密码不正确
     *      3  该用户被锁定，暂时不可登录
     */
    function balanceLogin($account,$password){
        $users = M('users');
        //处理数据，防止sql注入
        $account = htmlspecialchars(trim($account));
        $password = authcode(htmlspecialchars(trim($password)));
        //进入数据库，验证账号和密码
        $user = $users->where("(account = '$account' or phone = '$account') AND balance_password = '$password'")->find();
        if(!$user) return(array('code'=>0,'message'=>'账户或密码不正确'));
        if($user['is_lock']) return(array('code'=>0,'message'=>'该用户被锁定，暂时不可登录'));
        //更新登录信息
        $users->save(array("id"=> $user["id"], "login_time"=> time(), "login_ip" => get_client_ip()));

        //写入session
        session('user',$user['account']);
        session('phone',$user['phone']);
        session('user_id',$user['id']);
        //session跨域
//        setcookie("session_id",session_id(),time()+3600*24*15,"/",".meiwenti.top");
        //更新登录时间
        $data['login_time']=date('Y-m-d H:i:s');
        M('users')->where(array('id'=>session('user_id')))->save($data);
        //返回用户注册入口
        return array('code'=>1,'message'=>'登录成功','type'=>$user['type'],'user_id'=>$user['id'],'is_authentication'=>$user['is_authentication']);
    }

    /**
     * 用户登出
     * @return array
     *      1  退出成功
     */
    function loginout(){
        session(null);
        unset($_SESSION);
        session_destroy();
        clearCache();
        return array('code'=>1,'message'=>'退出成功');
    }
    //注册
    /**
     * version 2.1
     * 1、为了配合页面调整，手机号和手机验证码一起验证。
     */
    function register(){
        //接受短信验证码是否成功
        //判断手机验证码是否正确
        if(I('ver_num')!==session('ver_num')){
            $ret['code']=4;
            $ret['message']='手机验证码不正确';
            die(json_encode($ret));
        }
        // 2017年7月18日16:25:38 zgw 增加手机验证
        if (session('ver_phone') != I('phone')) {
            $ret['code']=5;
            $ret['message']='注册手机号与接收验证码手机号不一致';
            die(json_encode($ret));
        }
        // 接收数据
        $data=I('post.');
        if($data['type']==2){
            $data['is_admin']=1;
        }
        $res=D('Users')->addData($data);

        if(!$res['code']){
            return $res;
        }else{
            //环信 start
            vendor('Emchat.Easemobclass');
            $h=new \Easemob();
            $hx_username=$res['id'];
            $hx_password=$data['password'];
            $h->createUser("$hx_username","$hx_password") ;
            //环信 end

            // 商城 start
            $this->regShopMember($data['phone'], $data['password']);
            // 商城 end

            // 2017年7月18日16:47:25 zgw 清除ver_phone
            session('ver_phone', null);

            //成功后直接登陆
            $user=M('users')->where(array('id'=>$res['id']))->find();
            $res=D('Users','Logic')->login(I('phone'),I('password'));
            if(!$res['code']){
                //账号密码验证失败
                backJson($res);
            }else{
                //如果是运输版则直接登录
                if($res['type']==1){
                    //查看是否是通过认证了
                    if($res['is_authentication']==0){
                        //没有认证的直接返回没有认证
                        backJson($res);
                    }else{
                        //认证过的返回认证了哪些内容
                        $ret=$res;
                        //查看是否认证了司机
                        $driver=M('driver')->where(array('uid'=>session('user_id'),'is_passed'=>1))->find();
                        if ($driver){
                            $ret['driver']=1;
                        }else{
                            $ret['driver']=0;
                        }
                        //查看是否认证了车辆
                        $truck=M('truck')->where(array('owner_id'=>session('user_id'),'owner_type'=>1,'is_passed'=>1))->find();
                        if($truck){
                            $ret['truck']=1;
                        }else{
                            $ret['truck']=0;
                        }
                        $ret['name']=M('users')->where(array('id'=>session('user_id')))->getField('real_name');
                        backJson($ret);
                    }

                }else{
                    //企业版只有管理员才能登录
                    $user=M('users')->where(array('id'=>session('user_id')))->getField('is_admin');
                    if ($user==0){
                        //后台添加的一般管理员不能给予登陆
                        $ret['code']=0;
                        $ret['message']='您无权登陆';
                        session(null);
                        unset($_SESSION);
                        session_destroy();
                        backJson($ret);
                    }else{
                        //查看是否是通过认证了
                        if($res['is_authentication']==0){
                            //没有认证的直接返回没有认证
                            backJson($res);
                        }else{
                            $ret=$res;
                            //查找公司的名字传到前台
                            $user=M('users')->where(array('id'=>session('user_id')))->find();
                            $company=M('company')->where(array('id'=>$user['company_id']))->find();
                            $ret['name']=$user['real_name'];
                            $ret['company']=$company['name'];
                            backJson($ret);
                        }
                        backJson($res);
                    }
                }
            }

            //写入session
            session('user',$user['account']);
            session('user_id',$user['id']);
            //session跨域
            setcookie("session_id",session_id(),time()+3600*24*15,"/",".meiwenti.top");

            //返回数据
            $ret['code']=1;
            $ret['message']='注册成功';
            return $ret;
        }
    }
    //认证
    function authentication(){
        //type：1=》企业认证，2=》司机认证，3=》车主认证
        switch (I('type')){
            case 1:
                $res=D('Company','Logic')->authentication();
                return $res;
                break;
            case 2:
                $res=D('Driver','Logic')->authentication();
                return $res;
                break;
            case 3:
                $res=D('Truck','Logic')->authentication();
                return $res;
                break;
            default:
                $res['code']=0;
                $res['message']='认证类型填写有误';
                backJson($res);
        }
    }
    /**
     * 得到用户的默认设置
     * @return array
     */
    // public function getDefaultSet($uid = -1){
    //     if ($uid == -1) {
    //         $uid = session('user_id');
    //     }
    //     $def_set_str = $this->where('id = '.$uid)->getField('default_set_menu');
    //     $def_set = unserialize($def_set_str);
    //     // dump($def_set);exit;
    //     return $def_set;
    // }

    /**
     * 获得默认的菜单展示
     * @return int
     *      1：贸易商
     *      2：物流公司
     */
    public function getDefaultMenuType(){
        $res = $this->where('id='.session('user_id'))->getField('default_set_menu');
        return $res;
    }

    public function regShopMember($mobile, $password){
        $url = "http://me.meiwenti.top/register.do?action=foreignRegister&mobile=".$mobile."&password=".$password;
        $res = file_get_contents($url);
        return $res;
    }
}