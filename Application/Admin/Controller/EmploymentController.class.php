<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/3/28
 * Time: 9:44
 */
namespace Admin\Controller;
class EmploymentController extends CommonController {
    //*************************求职**********************************
    public function getApply(){
        $tag = 'human_info';
        $db = M($tag);
        $where = array('status' => 0);

        $page_num  = I('pageCurrent') - 1;
        $page_size = I('pageSize', 2);

        if ($name = trim(I('name', ''))) {
            $where['name'] = array('like', '%'.$name.'%');
        }
        if ($phone = I('phone', '')) {
            $where['phone'] = $phone;
        }
        $count = $db
            ->where($where)->count();

        $data = $db
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('is_passed asc, push_time desc')
            ->select();
        // dump($data);exit;
       // echo M()->_sql();exit;
        foreach ($data as $key => $value) {
            $data[$key] = $value;
            $option = "{
                id:'".$tag."_edit',
                url:'".U('Admin/Employment/detail')."',
                data:{id:'".$value['id']."'},
                type:'get',
                height:'400',
                width:600
            }";
            $option1 = "{
                type:'post',
                data:{id:'".$value['id']."'},
                url:'".U('Admin/Employment/approve')."',
                confirmMsg:'确定要通过审核吗？'
                }";
            $option2 = "{
                type:'get',
                data:{id:'".$value['id']."'},
                url:'".U('Admin/Employment/delApply')."',
                confirmMsg:'确定要删除吗？'
                }";
            //如果已审核，不显示审核按钮
            $approve_str = '';
            if ($value['is_passed'] == 0) {
                $approve_str = '<button type="button" class="btn-default" data-toggle="doajax" data-options="'.$option1.'">审核通过</button>';
            }
            $dostr = $approve_str.
                // '<button type="button" class="btn-default" data-toggle="dialog" data-options="'.$option.'">详情</button>'.
                '<button type="button" class="btn-default" data-toggle="doajax" data-options="'.$option2.'">删除</button>';
            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    public function approve(){
        if (IS_POST) {
            $id = I('post.id');
            $res = M('human_info')->save(array('id' => $id, 'is_passed' => 1));
            show_res($res);
        } else {
            alert('非法访问！', 300);
        }
    }

    public function detail($id){
        if ($id + 0 > 0) {
            $this->info = M('human_info')->find($id);
            $this->display();
        } else {
            alert_illegal();
        }

    }

    public function delApply($id){
        if ($id + 0 > 0) {
            $res = M('human_info')->save(array('id' => $id, 'status' => 1));
            show_res($res);
        } else {
            alert_illegal();
        }
    }
    //*************************招聘**********************************

    public function getRecruit(){
        $tag = 'recruitment';
        $db = M($tag);
        $where = array('status' => 0);

        $page_num  = I('pageCurrent') - 1;
        $page_size = I('pageSize', 2);

        if ($name = trim(I('name', ''))) {
            $where['name'] = array('like', '%'.$name.'%');
        }
        if ($phone = I('phone', '')) {
            $where['phone'] = $phone;
        }

        $count = $db
            ->where($where)->count();

        $data = $db
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('is_passed asc, push_time desc')
            ->select();

        foreach ($data as $key => $value) {
            // $data[$key] = $value;
            // //获得公司名字
            // if ($value['is_mwt']) {
            //     $data[$key]['editor_company'] = '煤问题公司';
            //     $data[$key]['editor_name'] = M('admin')->where(array('id' => $value['editor_id']))->getField('account');
            // } else {
            //     $tmp = M('users u')->field('u.account, c.name')->join('left join coal_company c on c.id = u.company_id')->where(array('c.id' => $value['editor_id']))->find();
            //     $data[$key]['editor_name'] =$tmp['account'];
            //     $data[$key]['editor_company'] =$tmp['name'];
            // }

            //操作参数设置
            $option = "{
                id:'".$tag."_edit',
                url:'".U('Admin/Employment/detail1')."',
                data:{id:'".$value['id']."'},
                type:'get',
                height:600,
                width:600
            }";
            $option1 = "{
                type:'post',
                data:{id:'".$value['id']."'},
                url:'".U('Admin/Employment/approve1')."',
                confirmMsg:'确定要通过审核吗？'
                }";
            $option2 = "{
                type:'get',
                data:{id:'".$value['id']."'},
                url:'".U('Admin/Employment/delRecruit')."',
                confirmMsg:'确定要删除吗？'
                }";
            //如果已审核，不显示审核按钮
            $approve_str = '';
            if ($value['is_passed'] == 0) {
                $approve_str = '<button type="button" class="btn-default" data-toggle="doajax" data-options="'.$option1.'">审核通过</button>';
            }
            $dostr = $approve_str.
                // '<button type="button" class="btn-default" data-toggle="dialog" data-options="'.$option.'">详情</button>'.
                '<button type="button" class="btn-default" data-toggle="doajax" data-options="'.$option2.'">删除</button>';
            $data[$key]['dostr'] = $dostr;
        }

        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    public function addRecruit(){
        if (IS_POST) {
            $post = I('post.');
            $res = D('Recruit')->addData($post);
            if ($res['code']) {
                $array = array('statusCode' => 200, 'message' => '处理成功！', 'closeCurrent' => true, 'tabid' => 'recruit');
                echo json_encode($array);exit;
            } else {
                alert($res['info'], 300);
            }
        }
        $this->display();
    }

    public function editRecruit(){
        if (IS_POST) {
            $post = I('post.');
            $map = array('id' => $post['id']);
            $res = D('Recruit')->editData($map, $post);
            if ($res['code']) {
                if ($res['info'] !== false) {
                    $array = array('statusCode' => 200, 'message' => '处理成功！','closeCurrent' => true, 'tabid' => 'recruit');
                    echo json_encode($array);exit;
                } else {
                    alert('处理失败！', 300);
                }
            } else {
                alert($res['info'], 300);
            }
        }
        $id = I('id');
        $info = M('recruit')->find($id);
        //获得公司名字
        if ($info['is_mwt']) {
            $info['editor_company'] = '煤问题公司';
            $info['editor_name'] = M('admin')->where(array('id' => $info['editor_id']))->getField('account');
        } else {
            $tmp = M('users u')->field('u.account, c.name')->join('left join coal_company c on c.id = u.company_id')->where(array('c.id' => $info['editor_id']))->find();
            $info['editor_name']    = $tmp['account'];
            $info['editor_company'] = $tmp['name'];
        }
        $this->info = $info;
        $this->display();
    }

    public function detail1($id){
        $info = M('recruit')->find($id);
        //获得公司名字
        if ($info['is_mwt']) {
            $info['editor_company'] = '煤问题公司';
            $info['editor_name'] = M('admin')->where(array('id' => $info['editor_id']))->getField('account');
        } else {
            $tmp = M('users u')->field('u.account, c.name')->join('left join coal_company c on c.id = u.company_id')->where(array('c.id' => $info['editor_id']))->find();
            $info['editor_name']    = $tmp['account'];
            $info['editor_company'] = $tmp['name'];
        }
        $this->info = $info;
        $this->display();
    }
    // 招聘审核通过
    public function approve1(){
        if (IS_POST) {
            $id = I('post.id');
            $res = M('recruitment')->save(array('id' => $id, 'is_passed' => 1));
            show_res($res);
        } else {
            alert('非法访问！', 300);
        }
    }
    // 招聘信息删除
    public function delRecruit($id){
        if ($id + 0 > 0) {
            $res = M('recruitment')->save(array('id' => $id, 'status' => 1));
            show_res($res);
        } else {
            alert_illegal();
        }
    }
}