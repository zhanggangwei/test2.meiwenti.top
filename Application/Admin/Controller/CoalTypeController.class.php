<?php
/**
 * 煤种管理
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/5/16
 * Time: 17:45
 */

namespace Admin\Controller;

class CoalTypeController extends CommonController {

    //  获得煤种列表
    public function getCoalTypeList(){
        $db = M('coal_type c');
        $where = array();
        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 10);

        // if ($order_id = I('order_id', '')) {
        //     $where['o.order_id'] = array('like', '%'.$order_id.'%');
        // }
        // if ($account = I('account', '')) {
        //     $where['a.account'] = array('like', $account.'%');
        // }

        $count = $db
            // ->join('left join coal_company a on a.id = o.buyer_id')
            // ->join('left join coal_company b on b.id = o.seller_id')
            // ->join('left join coal_coal_type t on t.id = o.coal_type')
            ->where($where)->count();

        $data = $db
            // ->field('o.id, o.order_id, o.order_type, a.name as buyer_name, b.name as seller_name, t.name as coal_type_name,
            // o.total_money, o.quantity, o.create_time, o.buyer_id, o.seller_id, o.log_id')
            // ->join('left join coal_company a on a.id = o.buyer_id')
            // ->join('left join coal_company b on b.id = o.seller_id')
            // ->join('left join coal_coal_type t on t.id = o.coal_type')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->select();
        foreach ($data as $key => $value) {
            $option1 = "{
                id:'order_edit',
                url:'".U('Admin/CoalType/coalTypeEdit')."',
                data:{id:'".$value['id']."'},
                type:'get',
                width:'800',
                height:'600'
                }";
            $option2 = "{
                type:'get',
                data:{id:'".$value['id']."'},
                url:'".U('Admin/CoalType/del')."',
                confirmMsg:'确定要删除吗？'
                }";
            $dostr = '';
            $dostr .= create_button($option1, 'dialog', '编辑');
            if ($value['is_admin'] == 0) {
                $dostr .= create_button($option2, 'doajax', '删除');
            }
            $data[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        // 某个订单的状态
        echo json_encode($info);
    }

    // 添加煤种
    public function coalTypeAdd(){
        if (IS_POST) {
            $post = I('post.');
            $res = M('coal_type')->add($post);
            if ($res) {
                after_alert(array('closeCurrent' => true, 'tabid' => 'coalType_coalTypeList'));
            } else {
                alert_false();
            }
        }
        $this->display();
    }

    // 编辑煤种
    public function coalTypeEdit(){
        if (IS_POST) {
            $post = I('post.');
            if ($post['id']) {
                $res = M('coal_type')->save($post);
                show_res($res);
            } else {
                alert_illegal();
            }
        }
        $id = I('get.id');
        $this->info = M('coal_type')->find($id);
        $this->display();
    }
    // 删除煤种
    public function del(){
        $id = I('id');
        if ($id) {
            $res = M('coal_type')->delete($id);
            show_res($res);
        } else {
            alert_illegal();
        }
    }
}