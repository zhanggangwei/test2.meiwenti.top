<?php
namespace Add\Controller;
/**
 * Created by PhpStorm.
 * User: 君王
 * Date: 2017-04-17
 * Time: 9:32
 */
use Think\Controller;
class ChatController extends Controller{
        function index(){
          echo 'chat';
        }
    public function get_info_by_id(){
        $userid  =  I('userid');
        empty($userid)?die(json_encode(array('code'=>0,'msg'=>'error'))):'';
        $data=M()->query("SELECT id,real_name,account,phone,CONCAT('http://img.meiwenti.top/',photo) as photo from coal_users  where id in (%s)",$userid);
        if(!empty($data)){
            $data=array(
                'code'=>1,
                'msg'=>'获取成功!',
                'data'=>$data
            );
            die(json_encode($data));
        }else{
            $data=array(
                'code'=>0,
                'msg'=>'获取不到信息',
                'data'=>array()
            );
            die(json_encode($data));
        }
    }
    public function search_friend()
    {
        $keyword = I('keyword');
        empty($keyword) ? die(json_encode(array('code' => 0, 'msg' => '请输入关键字'))) : '';
        if (is_numeric($keyword)) {
            $data = M()->query("SELECT id,real_name,account,phone,CONCAT('http://img.meiwenti.top/',photo) as photo from coal_users  where phone like '%$keyword%' and real_name !='' ");
            if (!empty($data)) {
                $data = array(
                    'code' => 1,
                    'msg' => '获取成功!',
                    'data' => $data
                );
                die(json_encode($data));
            } else {
                $data = array(
                    'code' => 0,
                    'msg' => '获取不到信息',
                    'data' => array()
                );
                die(json_encode($data));
            }
        } else {
            $data = M()->query("SELECT id,real_name,account,phone,CONCAT('http://img.meiwenti.top/',photo) as photo from coal_users  where real_name like '%$keyword%' and real_name !='' ");
            if (!empty($data)) {
                $data = array(
                    'code' => 1,
                    'msg' => '获取成功!',
                    'data' => $data
                );
                die(json_encode($data));
            } else {
                $data = array(
                    'code' => 0,
                    'msg' => '获取不到信息',
                    'data' => array()
                );
                die(json_encode($data));
            }

        }
    }
}
