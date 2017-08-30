<?php
/**
 * Created by PhpStorm.
 * 这个控制器的方法不做验证，不要增加其他方法。
 * User: zgw
 * Date: 2017/3/28
 * Time: 9:44
 */
namespace Admin\Controller;

use Think\Controller;

class IndexController extends Controller {
    private $uid;
    private $auth;
    private $menu1;

    function _initialize(){
        // 是否登录
        if(!session('sys_uid')){
            $this->redirect(U('Login/login','',''));
        }
        // 登录就取值
        $this->uid = session('sys_uid');
        // 加载auth类
        $this->auth = new \Think\Auth();
        // 得到一级菜单
        $data = array();
        $menu1 = M('menu')->where(array('is_admin' => 1, 'status' => 1, 'level' => 1, 'pid' => 0))->order('sort desc')->select();

        foreach ($menu1 as $key => $value){
            if ($this->auth->check($value['name'], $this->uid)) {
                $data[$key]['name']  = $value['title'];
                $data[$key]['id']    = $value['id'];
                $data[$key]['url']   = $value['name'];
            }
        }

        $data = array_values($data);
        // dump_text($data);
        $this->menu1 = $data;
    }

    public function index(){
        // $res = M('menu1')->select();
        // foreach ($res as $key => $val) {
        //     unset($val['id']);
        //     // dump($val);
        //     // exit;
        //     M('menu')->add($val);
        // }
        // // $dd = implode(',',range(1,200));
        // // dump_text($dd);
        // exit;
        //是否登录
        if(!session('sys_uid')){
            $this->redirect(U('Admin/Login/login','',''));
        }
        $this->assign('menu1',$this->menu1);
        //未认证用户
        $res['unauthorized']=M('users')->where(array('is_authentication'=>0))->count();
        //有证件待审认证用户
        $res['audit_auth']=M('users')->where(array('is_authentication'=>2))->count();
        //认证用户
        $res['authorized']=M('users')->where(array('is_authentication'=>1))->count();
        // 待审车辆
        $res['audit_truck']=M('truck')->where(array('is_authentication'=>1))->count();
        // 社会车主
        $res['trucker_truck']=M('truck')->where(array('owner_type'=>1))->count();
        // 合作公司车主
        $res['coo_company_truck']=M('truck')->where(array('owner_type'=>2))->count();

        // 空车
        $res['truck_state1']=M('truck')->where(array('state'=>1))->count();
        // 待接单
        $res['truck_state2']=M('truck')->where(array('state'=>2))->count();
        // 在路上
        $res['truck_state3']=M('truck')->where(array('state'=>3))->count();
        // 故障车
        $res['truck_state4']=M('truck')->where(array('state'=>4))->count();



        //公司
        $companies = M('company')->where(array('is_passed'=>0))->select();
        $company0 = 0;
        $company2 = 0;
        foreach ($companies as $key => $value) {
            $tmp = M('users')->where(array('company_id' => $value['id'], 'is_admin' => 1))->find();
            if ($tmp) {
                $company0++;
            } else {
                $company2++;
            }
        }
        $res['company0'] = $company0;
        $res['company2'] = $company2;
        $res['company1']=M('company')->where(array('is_passed'=>1))->count();
        //车主
        $res['trucker0']=M('truck')->where(array('is_passed'=>0))->count();
        $res['trucker1']=M('truck')->where(array('is_passed'=>1))->count();
        //司机
        $res['driver0']=M('driver')->where(array('is_passed'=>0))->count();
        $res['driver1']=M('driver')->where(array('is_passed'=>1))->count();
        //车辆
        $res['truck0']=M('truck')->where(array('is_passed'=>0))->count();
        $res['truck1']=M('truck')->where(array('is_passed'=>1))->count();

        // //长途信息
        // $res['long_haul0']=M('long_haul')->where(array('is_passed'=>0))->count();
        // $res['long_haul1']=M('long_haul')->where(array('is_passed'=>1))->count();
        // //火运信息
        // $res['train0']=M('train')->where(array('is_passed'=>0))->count();
        // $res['train1']=M('train')->where(array('is_passed'=>1))->count();
        // //船运信息
        // $res['ship0']=M('ship')->where(array('is_passed'=>0))->count();
        // $res['ship1']=M('ship')->where(array('is_passed'=>1))->count();
        //招聘信息
        $res['recruit1']=M('recruitment')->where(array('is_passed'=>0))->count();
        $res['recruit0']=M('recruitment')->where(array('is_passed'=>1))->count();
        //求职信息
        $res['apply1']=M('human_info')->where(array('is_passed'=>0))->count();
        $res['apply0']=M('human_info')->where(array('is_passed'=>1))->count();
        //货源信息
        $res['logistics_goods1']=M('logistics_goods')->where(array('is_passed'=>0))->count();
        $res['logistics_goods0']=M('logistics_goods')->where(array('is_passed'=>1))->count();
        //放空处理
        $res['empty_truck5']=M('re_goods')->where(array('state'=>5))->count();
        $res['empty_truck67']=M('re_goods')->where(array('state'=>array('in', array(6,7))))->count();
        //上次退出时间
        $login_time = M('admin')->where(array('account'=>session('sys_account')))->getField('login_time');
        $this->assign('loginout_time',$login_time);
        $this->assign('now_time',date("Y-m-d H:i:s"));
        $this->assign('num',$res);
        $this->display();
    }

    public function menu($id){
        // 传过来的一级菜单的id,通过id找到2级菜单
       // echo $id;
        $menu2 = M('menu')->where(array('is_admin' => 1, 'status' => 1, 'level' => 2, 'pid' => $id))->order('sort desc')->select();
        $data = array();
        foreach ($menu2 as $key => $value) {
            $data[$key]['name'] = $value['title'];
            $menu3 = M('menu')->where(array('is_admin' => 1, 'status' => 1, 'level' => 3, 'pid' => $value['id']))->order('sort desc')->select();
            foreach ($menu3 as $k =>  $val){
                //  验证权限
                $rule = 'Admin' . '/' . $value['name'] .'/' . $val['name'];
                if ($this->auth->check($rule, $this->uid)) {
                    $data[$key]['children'][$k]['name']   = $val['title'];
                    $data[$key]['children'][$k]['target'] = 'navtab';
                    $data[$key]['children'][$k]['fresh']  = true;
                    $data[$key]['children'][$k]['id']     = $value['name'] . '_' . $val['name'];
                    // $data[$key]['children'][$kk]['external']     = true;
                    // echo $vv['name'];
                    $data[$key]['children'][$k]['url']    = U($rule, '', '');
                } else {
                    addRule($rule, 1, 1);
                }
            }
        }
    	echo json_encode($data);exit;
    }

    // // 文章数据
    // public function article(){
    //     $title=I('title');
    //     $sql="title like '%".$title."%'";
    //     $page_num=I('pageCurrent')-1;
    //     $page_size = I('pageSize',2);
    //     $count = M('test')->where($sql)->count();
    //     $data=M('test')->where($sql)->limit($page_size*$page_num,$page_size)->select();
    //     foreach ($data as $key=>$value){
    //         $data[$key]['dostr']="<a href='www.baidu.com' target='_self' ><input type='button' value='编辑'/></a>";
    //     }
    //     $info = array(
    //         'totalRow' => $count,
    //         'pageSize' => $page_size,
    //         'list'      =>$data,
    //     );
    //     echo json_encode($info);
    // }

    // 查看调试log
    public function showLog(){
        $path = APP_PATH . '/Runtime/Logs/zgw_debug.txt';
        $content = file_get_contents($path);
        echo str_replace(PHP_EOL,'<br>',$content);
    }
}