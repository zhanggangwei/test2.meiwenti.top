<?php
namespace Add\Controller;
/**
 * Created by PhpStorm.
 * User: 君王
 * Date: 2017-04-17
 * Time: 9:32
 */
use Think\Controller;
use Vip\Controller\IndexController;

class OtherController extends Controller{
    public function __construct(){
        empty($_SESSION['user_id'])?die(json_encode(array('code'=>0,'msg'=>'没有登录的用户r32222是'))):'';
    }
    public function add_humaninfo(){
        $data  =  I('post.');
        $data['push_uid']=session('user_id');
        if(!empty($data)){
            // 验证手机号
            if (strlen(trim($data['phone'])) != 11) {
                $data1=array(
                    'code'=>0,
                    'msg'=>'手机号不正确',
                );
                die(json_encode($data1));
            }

            $recu = M("human_info");
            $info = $recu->where(array('phone' => $data['phone']))->find();
            if ($info) {
                $data1=array(
                    'code'=>0,
                    'msg'=>'该手机已有记录',
                );
                die(json_encode($data1));
            }
            $st=$recu->add($data);
            if($st){
                $data1=array(
                    'code'=>1,
                    'msg'=>'添加成功!',
                );
            }else{
                $data1=array(
                    'code'=>0,
                    'msg'=>'发生错误',
                );
            }
        }else{
            $data1=array(
                'code'=>0,
                'msg'=>'发生错误',
            );
        }
        die(json_encode($data1));
    }

    public function human_list(){
        $data = M()->query("select *,ifnull((select real_name from coal_users where coal_human_info.push_uid=coal_users.id),'') as push_name from coal_human_info where status=0 and is_passed = 1 order by push_time DESC ");
        if(!empty($data)){
            $data1=array(
                'code'=>1,
                'msg'=>'获取成功!',
                'data'=>$data
            );
            die(json_encode($data1));
        }else{
            $data1=array(
                'code'=>0,
                'msg'=>'无数据!',
            );
            die(json_encode($data1));
        }
    }
    public function human_details_byid(){
        $id  =  I('id');
        $data = M()->query("select *,(select real_name from coal_users where coal_human_info.push_uid=coal_users.id) as push_name from coal_human_info where status=0 and is_passed = 1 and id=$id ");
        if(!empty($data)){
            $data1=array(
                'code'=>1,
                'msg'=>'获取成功!',
                'data'=>$data
            );
            die(json_encode($data1));
        }else{
            $data1=array(
                'code'=>0,
                'msg'=>'无数据!',
            );
            die(json_encode($data1));
        }
    }

    public function recruitment_list(){
        $data = M()->query("select *,ifnull((select real_name from coal_users where coal_recruitment.push_uid=coal_users.id),'') as push_name from coal_recruitment where status=0 and is_passed = 1 order by push_time DESC ");
        if(!empty($data)){
            $data1=array(
                'code'=>1,
                'msg'=>'获取成功!',
                'data'=>$data
            );
            die(json_encode($data1));
        }else{
            $data1=array(
                'code'=>0,
                'msg'=>'无数据!',
            );
            die(json_encode($data1));
        }
    }
    public function get_recruitment_byid (){
        $id  =  I('id');
        $data = M()->query("select *,(select real_name from coal_users where coal_recruitment.push_uid=coal_users.id) as push_name from coal_recruitment where status=0 and is_passed = 1 and id=$id ");
        if(!empty($data)){
            $data1=array(
                'code'=>1,
                'msg'=>'获取成功!',
                'data'=>$data
            );
            backJson($data1);
        }else{
            $data1=array(
                'code'=>0,
                'msg'=>'无数据!',
            );
            backJson($data1);

        }
    }

    public function add_recruitment(){
        $data  =  I('post.');
        $data['push_uid']=session('user_id');
        $data['phone']=session('phone');
        if(!empty($data)){
            $recu = M("recruitment");
            $st=$recu->add($data);
            if($st){
                $data1=array(
                    'code'=>1,
                    'msg'=>'添加成功!',
                );
                die(json_encode($data1));
            }else{
                $data1=array(
                    'code'=>0,
                    'msg'=>'发生错误',
                );
                die(json_encode($data1));
            }
        }else{
            $data2=array(
                'code'=>0,
                'msg'=>'发生错误',
            );
            die(json_encode($data2));
        }
    }
    public function add_zone(){
        $data  =  I('post.');
        $data['user_id']=session('user_id');
        if(!empty($data)){
            $recu = M("zone");
            $st=$recu->add($data);
            if($st){
                $data1=array(
                    'code'=>1,
                    'msg'=>'添加成功!',
                );
                die(json_encode($data1));
            }else{
                $data1=array(
                    'code'=>0,
                    'msg'=>'发生错误',
                );
                die(json_encode($data1));
            }
        }else{
            $data2=array(
                'code'=>0,
                'msg'=>'发生错误',
            );
            die(json_encode($data2));
        }
    }
    public function zone_list()
    {
        $data = M()->query("select *,(select real_name from coal_users where coal_zone.user_id=coal_users.id) as `name`,CONCAT('http://img.meiwenti.top/',(select photo from coal_users where coal_zone.user_id=coal_users.id)) as user_photo,(UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(push_time))/60 as fenzhong from coal_zone where status=0 order by push_time DESC ");
        if (!empty($data)) {
            $data = array(
                'code' => 1,
                'msg' => '获取成功!',
                'data' => $data
            );
            backJson($data);

        } else {
            $data = array(
                'code' => 0,
                'msg' => '获取不到信息',
                'data' => array()
            );
            backJson($data);
        }
    }
    public function php(){
        phpinfo();
    }
}