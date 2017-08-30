<?php
/**
 * Created by PhpStorm.
 * 简易菜单管理
 * User: zgw
 * Date: 2017/4/14
 * Time: 18:34
 */
namespace Admin\Controller;

class MenuController extends CommonController {
    // 不走验证
    public function index(){
        $this->display();
    }
    // 大的新增菜单
    public function add1(){
        if (IS_POST) {
            $post = I('post.');
            $res = D('menu')->addData($post);
        }
        // alert_illegal('后台代码没有开开启');
        $this->display();
    }
    //查询菜单
    public function getMenu(){
        $where = I('post.');
        $res = M('menu')->where($where)->select();
        echo json_encode($res);
    }

    //显示VIP菜单
    public function getVipMenuData(){
        $db = M('menu');

        $where = array('is_admin' => 0);
        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        $count = $db
            ->where($where)
            ->count();

        $data = $db
            ->where($where)
            // ->limit($page_size * $page_num, $page_size)
            ->select();
        // sql();
        // dump_text($data);
        // {"id":"super","name":null,"level":0,"order":0,"title":"超级管理员","parentid":null},
        foreach ($data as $key => $value) {
            $data[$key] = $value;
            $data[$key]['pid'] = $value['pid']?$value['pid']:null;

            $dostr = '';
            //     $option1 = "{
            //     type:'post',
            //     data:{id:'".$value['id']."'},
            //     url:'".U('takeOver')."',
            //     confirmMsg:'确定要接收吗？'
            //     }";
            //     $dostr = create_button($option1, 'doajax', '接收') . $dostr;
            // if ($value['level'] != 3) {
            //     $option2 = "{
            //     type:'get',
            //     data:{pid:'".$value['id']."', level:'".$value['level']."'},
            //     url:'".U('addMenu')."',
            //     height:'600'
            //     }";
            //     $dostr .= create_button($option2, 'dialog', '新增下级');
            // }

            // echo $dostr;exit;
            // $data[$key]['dostr'] = $dostr;
        }
        // dump($data);
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        // dump_text($data);
        echo json_encode($info);
    }
    //显示admin菜单
    public function getAdminMenuData(){
        $db = M('menu');

        $where = array('is_admin' => 1);
        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 10);

        $count = $db
            ->where($where)
            // ->limit($page_size * $page_num, $page_size)
            ->count();

        $data = $db
            ->where($where)
            // ->limit($page_size * $page_num, $page_size)
            ->select();
        // sql();
        // dump_text($data);
        // {"id":"super","name":null,"level":0,"order":0,"title":"超级管理员","parentid":null},
        foreach ($data as $key => $value) {
            $data[$key] = $value;
            $data[$key]['pid'] = $value['pid']?$value['pid']:null;

            $dostr = '';
            //     $option1 = "{
            //     type:'post',
            //     data:{id:'".$value['id']."'},
            //     url:'".U('takeOver')."',
            //     confirmMsg:'确定要接收吗？'
            //     }";
            //     $dostr = create_button($option1, 'doajax', '接收') . $dostr;
          
            // if ($value['level'] != 2) {
            //     $option2 = "{
            //     type:'get',
            //     data:{pid:'".$value['id']."', level:'".$value['level']."'},
            //     url:'".U('addMenu')."',
            //     height:'600'
            //     }";
            //     $dostr .= create_button($option2, 'dialog', '新增下级');
            // }

            // echo $dostr;exit;
            // $data[$key]['dostr'] = $dostr;
        }
        // dump($data);
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        // dump_text($data);
        echo json_encode($data);
    }
    // 新增菜单
    public function addMenu(){
        if (IS_POST){
            $post = I('post.');
            M('menu')->add($post);
            if ($post['level'] == 1) {
                addRule($post['name'], 1, 1);
            } else {
                if ($post['level'] == 3) {
                    // 增加rule表
                    $level2_name = M('menu')->where(array('level' => 2, 'id' => $post['pid']))->getField('name');
                    $rule_name = 'Admin/' . $level2_name . '/' . $post['name'];
                    addRule($rule_name, 1, 1);
                }
            }
            after_alert(array('closeCurrent' => true));
        }
        $this->display();
    }
    // 编辑
    public function edit(){
        $ids = json_decode($_POST['json'],true);
        $is_admin = I('is_admin',0);
        M()->startTrans();
        foreach ($ids as $key => $val) {
            if ($is_admin == 1) {

            } else {
                // vip端
                if (isset($val['id'])) {
                    // 修改
                    $data = array(
                        'id' => $val['id'],
                        'name' => $val['name'],
                        'title' => $val['title'],
                        'sort' => $val['sort']
                    );
                    $res = M('menu')->save($data);
                } else {
                    // 新增
                    if (isset($val['pid'])) {
                        // 新增二级菜单（有pid说明非一级菜单）
                        $val['is_admin'] = $is_admin;
                        $val['level'] += 1;
                        if ($val['level'] > 2) {
                            alert('vip层级不能大于3',300);
                        }
                        //如果是2级，要新增一条规则
                        $parent = M('menu')->find($val['pid']);
                        $rule_name = 'Vip/' . $parent['name'] . '/' . $val['name'];

                        $rule_res = D('Rule')->addData(array('name' => $rule_name, 'title' => '加载页面')); // 唯一性规则没有验证？？？？
                        if ($rule_res['code'] == 0) {
                            M()->rollback();
                            alert($rule_res['message'], 300);
                        }
                    } else {
                        // 新增一级菜单
                        $val['level'] = 1;
                        $val['pid'] = 0;
                    }
                    // 如果有一样的就报错
                    $is_exists = M('menu')->where(array('name' => $val['name'], 'level' => $val['level'], 'pid' => $val['pid'], 'is_admin' => 0))->find();
                    if ($is_exists) {
                        M()->rollback();
                        alert('已经有记录', 300);
                    }
                    $res = M('menu')->add($val);
                }
            }
            // if (isset($val['id'])) {
            //     $data = array(
            //         'id' => $val['id'],
            //         'name' => $val['name'],
            //         'title' => $val['title'],
            //         'sort' => $val['sort']
            //     );
            //     $res = M('menu')->save($data);
            // } else {
            //     $val['is_admin'] = $is_admin;
            //     $res = M('menu')->add($val);
            // }
        }
        M()->commit();
        after_alert(array('refresh' => true));
    }
    // 删除
    public function del(){
        $data = json_decode($_POST['json'], true);
        $is_admin = I('is_admin',0);
        M()->startTrans();
        $data_count = count($data);
        foreach ($data as $key => $val) {
            // 1、删除menu表数据
            if (isset($val['id'])) {
                $val_id = $val['id'];
            } else {
                $val_id = M('menu')->where(array('name' => $val['name'], 'pid' => $val['pid']))->getField('id');
            }
            $res1 = M('menu')->delete($val_id);
            if ($is_admin == 1) {

            } else {
                // 2、删除admin_auth_rule表
                switch ($val['level']) {
                    case 0:
                        // 删除rule表
                        $sub_menu = M('menu')->where(array('pid' => $val['id']))->select();
                        foreach ($sub_menu as $v) {
                            $sub[] = M('auth_rule')->where(array('name' => 'Vip/' . $val['name'] .'/'. $v['name']))->delete();
                        }
                        $res3[] = count($sub) == count($sub_menu);
                        // 一级把下面的menu都删除，
                        $res2[] = M('menu')->where(array('pid' => $val['id']))->delete();
                        break;
                    case 1:
                        // 二级就直接删除，
                        $res2[] = true;
                        $parent = M('menu')->find($val['pid']);
                        $res3[] = M('auth_rule')->where(array('name' => 'Vip/' . $parent['name'] .'/'. $val['name']))->delete();
                        break;
                    default:
                        break;
                }
                // 3、更新group表rules字段(可以不更新)
            }
            
        }
        // echo count($res1).'--';
        // echo count($res2).'--';
        // echo $data_count;exit;

        if (count($res1) == $data_count && count($res2) == $data_count && count($res3) == $data_count) {
            M()->commit();
            after_alert(array('refresh' => true));
            
        } else {
            M()->rollback();
            alert_false();
        }
        
    }
    // 拖动
    public function drop(){
        $ids = json_decode($_POST['json'], true);
        dump($ids);
    }

    /************************* 菜单组织架构图 *******************************************************/
    public function menu_manage(){

        // 总后台菜单
        $admin_menu_children = array();
        $admin_menu = M('menu')->where(array('is_admin' => 1, 'level' => 1))->select();
        foreach ($admin_menu as $key => $value) {
            $admin_menu_children[$key]['name'] = $value['title'];
            // 后台2级菜单
            $two = M('menu')->where(array('is_admin' => 1, 'level' => 2, 'pid' => $value['id']))->select();
            foreach ($two as $k => $val) {
                $admin_menu_children[$key]['children'][$k]['name'] = $val['title'];
                // 后台3级菜单
                $three = M('menu')->where(array('is_admin' => 1, 'level' => 3, 'pid' => $val['id']))->select();
                // dump($three);exit;
                foreach ($three as $kk => $v) {
                    $admin_menu_children[$key]['children'][$k]['children'][$kk]['name'] = $v['title'];
                }
            }
        }

        // PC端菜单
        $pc_menu_children = array();
        $pc_menu = M('menu')->where(array('is_admin' => 0, 'level' => 1))->order('menu_type asc')->select();
        $x = 0;
        $y = 0;
        $z = 0;
        foreach ($pc_menu as $key => $value) {
            $t = $value['menu_type'];
            switch ($t) {
                case 0:
                    $pc_menu_children[$t]['name'] = '公共';
                    $i = $x;
                    $x++;
                    break;
                case 1:
                    $pc_menu_children[$t]['name'] = '物流公司';
                    $i = $y;
                    $y++;
                    break;
                case 2:
                    $pc_menu_children[$t]['name'] = '贸易商';
                    $i = $z;
                    $z++;
                    break;
                default:
                    $pc_menu_children[$t]['name'] = '未知';
                    break;
            }
            $pc_menu_children[$t]['children'][$i]['name'] = $value['title'];
            // 前台2级菜单
            $two = M('menu')->where(array('is_admin' => 0, 'level' => 2, 'pid' => $value['id']))->select();
            foreach ($two as $k => $val) {
                $pc_menu_children[$t]['children'][$i]['children'][$k]['name'] = $val['title'];
            }
            // echo $key;
        }
        // dump($pc_menu_children);exit;

        $data = array(
            'name' => '煤问题',
            'children' => array(
                array('name' => '总后台', 'children' => $admin_menu_children),
                array('name' => 'PC端', 'children' => $pc_menu_children),
            )
        );
        $this->assign('data',json_encode($data,false));
        $this->display();
    }
}