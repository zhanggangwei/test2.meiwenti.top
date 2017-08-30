<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/4/7
 * Time: 8:57
 */
namespace Vip\Controller;

class UsersController extends CommonController {
    // 修改密码
    public function changePasswd(){
        if (IS_POST) {
            $post = I('post.');
            $info = M('users')->where(array('account' => session('account'), 'password' => authcode($post['oldpassword'])))->find();
            if ($info) {
                $res = M('users')->where(array('account' => session('account')))->save(array('password' => authcode(trim($post['password']))));
                // 修改环信密码
                vendor('Emchat.Easemobclass');
                $h=new \Easemob();
                $h->resetPassword($info['id'],trim($post['password']));
                show_res($res);
            } else {
                alert('旧密码不正确！', 300);
            }
        } else {
            alert('非法访问',300);
        }
    }
}
