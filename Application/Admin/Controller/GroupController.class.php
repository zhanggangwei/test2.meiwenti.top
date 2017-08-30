<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/3/24
 * Time: 15:34
 */
namespace Admin\Controller;

class GroupController extends CommonController {

    //管理组列表
    public function getGroupList(){
        $db = M('admin_auth_group g');
        $where = array();

        $page_num  = I('pageCurrent') - 1;
        $page_size = I('pageSize', 2);
        
        if ($title = I('title', '')) {
            $where['g.title'] = array('like', '%'.$title.'%');
        }

        $count = $db->where($where)->count();
        
        $data = $db
            ->field('g.id, g.title')
            // ->join('left join coal_admin_auth_group_access ga on ga.uid = a.id')
            // ->join('left join coal_admin_auth_group g on ga.group_id = g.id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('g.id desc')
            ->select();

        foreach ($data as $key => $value) {
            $data[$key] = $value;
            $option1 = "{
                id:'group_edit',
                url:'".U('Admin/Group/edit')."',
                data:{id:'".$value['id']."'},
                type:'get',
                width:600,
                height:800
            }";
            $option2 = "{
                type:'get',
                data:{id:'".$value['id']."'},
                url:'".U('Admin/Group/del')."',
                confirmMsg:'确定要删除吗？'
                }";

            $dostr = '<button type="button" class="btn-default" data-toggle="dialog" data-options="'.$option1.'">编辑</button>';
                if ($value['id'] != 1) {
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
    //加载添加管理组页面
    public function addGroup(){
        //获得所有菜单的节点
        $tmp_all = M('admin_auth_rule')->where('is_menu = 1')->select();
        $ruleids = array();
        foreach ($tmp_all as $val) {
            $ruleids[] = $val['id'];
        }
        $this->auth_info = $this->get_array($ruleids);
        $this->display();
    }
    //增加管理组
    public function add(){
        if (IS_POST) {
            $post = I('post.');

            $rules_arr = $this->get_all_rules($post['ruleids_level3']); 

            $post['rules'] = implode(',', $rules_arr);
            unset($post['ruleids_level3']);
            // dump($rules_arr);exit;
            M()->startTrans();
            $res  = D('AdminGroup')->addData($post);
            if ($res['code']){
                $res1 = M('admin_auth_group_access')->add(array('uid' => $res['info'], 'group_id' => I('post.group')));
            } else {
                $res1 = false;
            }
            if ($res && $res1){
                M()->commit();
                $array = array('statusCode' => 200, 'message' => '处理成功！', 'closeCurrent' => true, 'tabid' => 'glist');
                echo json_encode($array);exit;
            } else {
                M()->rollback();
                alert($res['info'],300);
            }
        } else {
            alert('非法访问',300);
        }
    }
    //编辑管理组
    public function edit(){
        $id = I('get.id');
        if (IS_POST) {
            $id   = I('post.id');
            $post = I('post.');
            // sort($post['ruleids']);
            // dump($post['ruleids']);
            // dump($post['ruleids_level3']);
            // dump(array_merge($post['ruleids'], $post['ruleids_level3']));
            // exit;
            
            //这是给过来的ruleids
            // $rules_arr = array_merge($post['ruleids'], $post['ruleids_level3']); // 先要数组，用array_unique
            $rules_arr = $this->get_all_rules($post['ruleids_level3']); 

            $post['rules'] = implode(',', $rules_arr);
            unset($post['ruleids_level3']);
            // dump($post);exit;
            $res = D('AdminGroup')->editData(array('id' => $id), $post);
            show_res($res);
        }
        $info = M('admin_auth_group')->find($id);// 要编辑的管理员组

        //获得所有菜单的节点
        $tmp_all = M('admin_auth_rule')->where('is_menu = 1')->select();
        $ruleids = array();
        foreach ($tmp_all as $val) {
            $ruleids[] = $val['id'];
        }
        $auth_info = $this->get_array($ruleids);
        //如果该管理员有权限，形成数组到前台验证
        if ($info) {
            $rules_array = explode(',', $info['rules']);
        }
        $data = array(
            'info' => $info,
            'auth_info' => $auth_info,
            'rules_array' => $rules_array,
        );
        $this->assign($data);
        $this->display();
    }
    //删除管理组
    public function del($id){
        $info = M('admin a')->join('left join coal_admin_auth_group_access b on a.id = b.uid')->where('b.group_id = '.$id)->find();
        if ($info) {
            alert('管理员有此组，不能删除！',300);
        }
        M()->startTrans();
        $res = M('admin_auth_group')->delete($id);
        $res2 = M('admin_auth_group_access')->where(array('group_id' => $id))->delete();
        if ($res && $res2) {
            M()->commit();
            alert('处理成功！');
        } else {
            M()->rollback();
            alert('处理失败！',300);
        }
    }

    private function get_array($where,$id = 0){
        $str = implode(',',$where);
        $sql = "SELECT * FROM `coal_admin_auth_rule` WHERE `pid` = $id and `is_menu`=1 and `id` in (".$str.") Order by `sort` DESC";
        $result = M()->query($sql);
        // print_r($result);exit;
        $arr = array();
        if($result){//如果有子类
            foreach($result as $rows){
                $rows['list'] = $this->get_array($where,$rows['id']); //调用函数，传入参数，继续查询下级
                $arr[] = $rows; //组合数组
            }
            return $arr;
        }
    }

    private function get_one_two_by_three($id){
        $db = M('admin_auth_rule');
        $pid = $db->getFieldByid($id, 'pid');
        $ppid = $db->getFieldByid($pid, 'pid');
        return array($pid, $ppid);
    }

    private function get_all_rules($array){
        $rules_arr = $array;
        // 循环给出3组菜单所附带的方法
        foreach ($array as $val) {
            // 得到一二级的菜单
            $menu12_ids = $this->get_one_two_by_three($val);
            // 得到三级下的所有
            $tmp2 = M('admin_auth_rule')->field('id')->where(array('pid' => $val))->select();
            $tmp2_arr = array();
            foreach ($tmp2 as $v) {
                $tmp2_arr[] =$v['id'];
            }
            $rules_arr = array_unique(array_merge($rules_arr, $menu12_ids, $tmp2_arr));
        }
        sort($rules_arr);
        return $rules_arr;
    }
}