<?php
/**
 * Created by PhpStorm.
 * 消息控制器
 * User: zgw
 * Date: 2017-05-10
 * Time: 2:48
 */
namespace Vip\Controller;

class MessageController extends CommonController {

    // 消息推送
    public function sendMessage(){
        if (IS_POST) {
            // 接收要推送的内容及相关参数
            $company_id = I('company');
            $title = I('title');
            $notice = I('notice');
            $data = array(
                'uid' => session('user_id'),
                'company_id' => $company_id,
                'title' => $title,
                'notice' => $notice,
                'add_time' => get_time(),
            );
            $res = M('company_notice')->add($data);
            // 推送消息(公告)
            vendor('Emchat.Easemobclass');
            $group_id=M('company')->where(array('id'=>$company_id))->getField('group_id');
            $h=new \Easemob();
            $h->sendText(session('user_id'),'chatgroups', array($group_id), $notice);
            show_res($res);
        }
        $cid = session('company_id');
        $this->sub_company = M('company')->field('id,name')->where('pid='.$cid)->select();
        $this->is_zong = is_parent_company($cid)?0:1;
        $this->display();
    }

    // 消息列表
    public function getMessageList(){
        $tag = 'company_notice n';
        $db = M($tag);
        $where = array(
            'n.uid' => session('user_id')
        );

        $page_num  = I('pageCurrent', 1) - 1;
        $page_size = I('pageSize', 2);

        $count = $db
            ->join('left join coal_users u on u.id = n.uid')
            ->join('left join coal_company c on c.id = n.company_id')
            ->where($where)->count();

        $data = $db
            ->field('n.*, u.real_name as creator, c.name as cname')
            ->join('left join coal_users u on u.id = n.uid')
            ->join('left join coal_company c on c.id = n.company_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('n.add_time desc')
            ->select();

        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 招聘信息
    public function getRecruitmentInfo(){
        $tag = 'recruitment r';
        $db = M($tag);
        $where = array(
            'r.status' => 0,
        );
        // 判断总公司
        $ids = retrun_company_ids(session('company_id'));
        $where['company_id'] = array('in', $ids);

        $page_num  = I('pageCurrent', 1) - 1;
        $page_size = I('pageSize', 2);

        if ($title = trim(I('title', ''))) {
            $where['r.title'] = array('like', '%'.$title.'%');
        }

        $count = $db
            ->where($where)->count();

        $data = $db
            ->field('r.*, u.real_name')
            ->join('left join coal_users u on u.id = r.push_uid')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('r.push_time desc,r.id desc')
            ->select();
        foreach ($data as $key => $value) {
            $option1 = "{
                id:'msg_recuit_edit',
                data:{id:'".$value['id']."'},
                url:'".U('editRecuitment')."',
                width:'800',
                height:'600'
                }";
            $option3 = "{
                type:'get',
                data:{id:'".$value['id']."'},
                url:'".U('delRecuitment')."',
                confirmMsg:'确定要删除吗？'
                }";
            $dostr = create_button($option1, 'dialog', '编辑').create_button($option3, 'doajax', '删除');
            // echo $dostr;exit;
            $data[$key]['dostr'] = $dostr;
        }

        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 添加招聘信息
    public function addRecuitment(){
        if (IS_POST) {
            $post = I('post.');
            $post['push_uid'] = session('user_id');
            $post['phone'] = session('phone');
            try{
                $res = M('recruitment')->add($post);
                if ($res['code']) {
                    $array = array('statusCode' => 200, 'message' => '处理成功！', 'closeCurrent' => true, 'tabid' => 'Message_recruitmentInfo');
                    echo json_encode($array);exit;
                } else {
                    alert($res['info'], 300);
                }
            } catch (\Exception $e) {
                alert('添加异常', 300);
            }
        }
        $this->assign('province',get_provice());
        $this->display();
    }

    // 修改招聘信息
    public function editRecuitment(){
        if (IS_POST && I('post.id')) {
            $post = I('post.');
            $res = M('recruitment')->save($post);
            if ($res !== false) {
                after_alert(array('closeCurrent' => true, 'tabid' => 'Message_recruitmentInfo'));
            } else {
                alert_illegal();
            }
        }
        $id = I('id');
        $info = M('recruitment')->find($id);
        $info['is_negotiable'] = $info['is_negotiable'] + 0;
        $info['work_type'] = $info['work_type'] + 0;
        // 2017年5月11日16:19:23 zgw 待苹果端修改
        // $this->assign('province',get_provice());
        // $this->assign('shiqu',get_shiqu($info['sheng']));
        // $this->assign('diqu',get_diqu($info['shi'],$info['qu']));
        $this->info = $info;
        $this->display();
    }

    // 删除招聘信息
    public function delRecuitment(){
        $id = I('id');
        if ($id + 0 > 0) {
            $res = M('recruitment')->save(array('id' => $id, 'status' => 1));
            show_res($res);
        } else {
            alert_illegal();
        }
    }

}