<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/3/24
 * Time: 15:34
 */
namespace Admin\Controller;

class ManagerController extends CommonController {

    //加载添加管理员页面
    public function addManager(){
        $this->group_info = M('admin_auth_group')->field('id, title')->where(array('status' => 1))->select();
        $this->display();
    }
    //获取管理员列表
    public function getManageList(){

        $db = M('Admin a');
        $where = array();

        $page_num  = I('pageCurrent') - 1;
        $page_size = I('pageSize', 2);
        
        if ($title = I('title', '')) {
            $where['g.title'] = array('like', '%'.$title.'%');
        }
        if ($account = I('account', '')) {
            $where['a.account'] = array('like', $account.'%');
        }

        $count = $db
            ->join('left join coal_admin_auth_group_access ga on ga.uid = a.id')
            ->join('left join coal_admin_auth_group g on ga.group_id = g.id')
            ->where($where)->count();
        
        $data = $db
            ->field('a.id, a.account, a.nickname, a.phone, a.is_lock, a.create_time, g.title')
            ->join('inner join coal_admin_auth_group_access ga on ga.uid = a.id')
            ->join('left join coal_admin_auth_group g on ga.group_id = g.id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('a.create_time desc, a.id desc')
            ->select();
//        echo M()->_sql();exit;
        foreach ($data as $key => $value) {
            $data[$key] = $value;
            $option1 = "{
                id:'maneger_edit',
                url:'".U('Admin/Manager/edit')."',
                data:{id:'".$value['id']."'},
                type:'get',
                height:'400'
            }";
            $option2 = "{
                type:'get',
                data:{id:'".$value['id']."'},
                url:'".U('Admin/Manager/del')."',
                confirmMsg:'确定要删除吗？'
                }";

            $dostr = '<button type="button" class="btn-default" data-toggle="dialog" data-options="'.$option1.'">编辑</button>';
                if ($value['account'] != 'admin') {
                    $dostr .= '<button type="button" class="btn-default" data-toggle="doajax" data-options="'.$option2.'">删除</button>';
                }
            ;
            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }
    //增加管理员
    public function add(){
        if (IS_POST) {
//            dump(I('post.'));
            $group_id = I('post.group');
            M()->startTrans();
            $res  = D('Admin')->addData(I('post.'));
            if ($res['code']) {
                if ($res > 0 && $group_id > 0) {
                    $res1 = M('admin_auth_group_access')->add(array('uid' => $res, 'group_id' => $group_id));
                } else {
                    $res['info'] = '用户id和组id都要大于0';
                }
            } else {
                $res1 = false;
            }

            if ($res && $res1){
                M()->commit();
                $array = array('statusCode' => 200, 'message' => '处理成功！', 'closeCurrent' => true, 'tabid' => 'index');
                echo json_encode($array);exit;
            } else {
                M()->rollback();
                alert($res['info'],300);
            }
        } else {
            alert('非法访问',300);
        }
    }
    //编辑管理员
    public function edit(){
        $id = I('get.id');
        if (IS_POST) {
            $id = I('post.id');
            $map = array('id' => $id);
            $post = I('post.');
            if (!$post['password']) {
                unset($post['password']);
            } else {
                if ($post['password']  != $post['repassword']) alert('两次密码不一致！',300);
                $post['password'] = authcode(trim($post['password']));
            }
            $group_id = I('post.group');
            if ($group_id > 0 && $id > 0) {
                M()->startTrans();
                $res = D('Admin')->editData($map, $post);
                $res1 = M('admin_auth_group_access')->where(array('uid' => $id))->save(array('group_id' => $group_id));
                if ($res['code'] && $res1 !== false) {
                    M()->commit();
                    $array = array('statusCode' => 200, 'message' => '处理成功！', 'closeCurrent' => true, 'tabid' => 'index');
                    echo json_encode($array);exit;
                } else {
                    M()->rollback();
                    alert($res['info'],300);
                }
            } else {
                alert('用户id和组id都要大于0',300);
            }
        }
        if ($id) {
            $info = M('admin a')->field('a.*, b.group_id')->join('left join coal_admin_auth_group_access b on a.id = b.uid')->where('a.id='.$id)->find();
            $group_info = M('admin_auth_group')->field('id, title')->select();
            $data = array(
                'info'  => $info,
                'group_info'  => $group_info,
            );
            $this->assign($data);
        }
        // dump($info);exit; // 当改一个自己没有的权限时，就会出错
        $this->display();
    }
    //删除管理员
    public function del($id){
        $res = D('Admin')->delData($id);
        show_res($res);
    }
    public function is_mobile_exists($mobile){
        $res = M('admin')->where(array('phone' => $mobile))->find();
        if ($res) {
            alert($res);
        } else {
            alert();
        }
    }

    public function changePasswd(){
        if (IS_POST) {
            $post = I('post.');
            $info = M('admin')->where(array('account' => session('sys_account'), 'password' => $post['oldpassword']))->find();
            if ($info) {
                $res = M('admin')->where(array('account' => session('sys_account')))->save(array('password' => $post['password']));
                show_res($res);
            } else {
                alert('旧密码不正确！', 300);
            }
        } else {
            alert('非法访问',300);
        }
    }
}