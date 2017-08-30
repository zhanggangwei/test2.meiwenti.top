<?php
namespace Vip\Controller;
use Think\Controller;

class IndexController extends Controller {
    private $uid;
    private $auth;
    private $menu1;

    function _initialize(){
        //是否登录
//        if(!session('user_id')){
//            $this->redirect(U('Vip/Login/login','',''));
//        }
//
//        $this->uid = session('user_id');

        //通过menu的菜单角色分类（有值就用，没有就取默认），得到对应的一级菜单
        // if (!session('def_menu')) {
        //     $def_menu = M('users')->where('id = '.$this->uid)->find('default_set_menu');
        //     session('def_menu', $def_menu);
        // } else {
        //     $def_menu = session('def_menu');
        // }

        // $menu_type = I('menu_type', $def_menu); //无角色分类

        // $menu1 = $this->getMenu($menu_type);
        // $this->menu1 = $data;
    }

    //页面要先走这个方法。所以一些首页初始化的东西在这里加载
    public function index(){
        $is_auto = 1; // 是自动派单
        // $this->is_auto = $is_auto;

//        $current = I('current',5); // 当前页
//        $limit = 10; // 显示条数
//        $count  = M('truck')->where('is_passed=1')->count();// 查询满足要求的总记录数
//
//        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
//        $list = M('truck')->where('is_passed=1')->order('id desc')->limit(($current-1)*$limit.','.$limit)->select();

        $data = array(
            'is_auto' => $is_auto,
            'is_auto1' => $is_auto1,
//            'count' => $count,
//            'limit' => $limit,
//            'current' => $current,
//            'list' => $list,
        );
//        dump($data);exit;
        $this->assign($data);
        $this->display();
    }

    public function logistics(){
        $current = I('current',1); // 当前页
        $limit = 10; // 显示条数
        $count  = M('truck')->where('is_passed=1')->count();// 查询满足要求的总记录数

        // 进行分页数据查询 注意limit方法的参数要使用Page类的属性
        $list = M('truck')->where('is_passed=1')->order('id desc')->limit(($current-1)*$limit.','.$limit)->select();
        echo json_encode(array(
            'count' => $count,
            'limit' => $limit,
            'current' => $current,
            'list' => $list,
        ));
    }

    public function login(){

        $this->display();
    }

    //
    //登陆方法操作方法
    public function logining(){

        $data = I('post.');

        $is_user = false;
        $is_password = false;
        $is_captcha = false;
        $back_data = array("message"=>"error","type"=>"user");

        if($data['user'] == "sxq"){
            $is_user = true;
        };
        if($data['password'] == "123456"){
            $is_password = true;
        };
        if($data['captcha'] == "1111"){
            $is_captcha = true;
        };


        if(!$is_user){
            $back_data['type'] = "user";
        }else if(!$is_password){
            $back_data['type'] = "password";
        }else if(!$is_captcha){
            $back_data['type'] = "captcha";
        }else{
            $back_data['message'] = "success";
        };

        echo json_encode($back_data);


    }





}

