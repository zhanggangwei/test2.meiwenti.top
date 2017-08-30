<?php
/**
 * Created by PhpStorm.
 * 煤炭贸易
 * User: zgw
 * Date: 2017/5/10
 * Time: 15:16
 */
namespace Vip\Controller;

class SellerNewsController extends CommonController {
    // ---------------------煤炭贸易--------------------------------------
    // 获取煤炭贸易列表
    public function getSellerNewsList(){
        $db = M('seller_news');
        $where = array();

        // 总公司和子公司关系
        $ids = retrun_company_ids(session('company_id'));
        $where['writer_id'] = array('in', $ids);

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        // if ($name = trim(I('real_name', ''))) {
        //     $where['u.real_name'] = array('like', '%'.$name.'%');
        // }
        if ($title = I('title', '')) {
            $where['title'] = array('like', '%'.$title.'%');
        }

        $count = $db
            // ->join('left join coal_company c on c.id = t.owner_id')
            ->where($where)
            ->count();

        $data = $db
            // ->field('t.*, c.name as company_name')
            // ->join('left join coal_company c on c.id = t.owner_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('creat_time desc')
            ->select();
        // sql();
        // dump_text($data);
        foreach ($data as $key => $value) {

            // 交货地
            $data[$key]['buy_address'] = $value['del_address_p'].$value['del_address_s'].$value['del_address_q'];

            // 产地
            $data[$key]['sell_address'] = $value['pro_address_p'].$value['pro_address_s'].$value['pro_address_q'];

            // 煤炭种类
            $data[$key]['coal_type'] = coal_type($value['coal_type']);

            $option2 = "{
                id:'seller_news_detail',
                data:{id:'".$value['id']."'},
                url:'".U('sellerNewsDetail')."',
                width:'800',
                height:'600',
                }";
            $option3 = "{
                id:'seller_news_edit',
                data:{id:'".$value['id']."'},
                url:'".U('sellerNewsEdit')."',
                width:'800',
                height:'600',
                }";
            $option4 = "{
                type:'get',
                data:{id:'".$value['id']."'},
                url:'".U('sellerNewsDel')."',
                confirmMsg:'确定要删除吗？'
                }";
            $data[$key]['dostr'] = create_button($option2, 'dialog', '详情')
                // .create_button($option3, 'dialog', '编辑')  // 信息量大，以后做
                .create_button($option4, 'doajax', '删除')
            ;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }

    // 煤炭贸易详情
    public function sellerNewsDetail(){
        $id = I('id');
        $info = M('seller_news')->find($id);
        if ($info) {
            $this->info = $info;
            $this->display();
        } else {
            alert_illegal();
        }
    }

    // 煤炭贸易信息编辑
    public function sellerNewsEdit(){
        if (IS_POST) {
            $post = I('post.');
            dump($post);exit;
            $info = M('truck')->find($post['id']);
            if (!edit_unique(M('truck'), $info['owner_order'], 'owner_order', $post['owner_order'])) {
                alert('车辆编号重复了', 300);
            }
            if (!edit_unique(M('truck'), $info['lic_number'], 'lic_number', $post['lic_number'])) {
                alert('行驶证号重复了', 300);
            }
            if (is_expire($post['ins_date']) || is_expire($post['check_date'])) {
                alert('提交的日期过期', 300);
            }
            $res = M('truck')->save($post);
            if ($res !== false) {
                after_alert(array('closeCurrent' => true, 'tabid' => 'SellerNews_sellerNewsList'));
            } else {
                alert_false();
            }
        }
        $id = I('get.id');
        if ($id) {
            $this->info = M('seller_news')->find($id);
            $this->display();
        } else {
            alert_illegal();
        }
    }

    // 煤炭贸易信息删除
    public function sellerNewsDel(){
        $id = I('id') + 0;
        if ($id) {
            $res = M('seller_news')->delete($id);
            show_res($res);
        } else {
            alert('操作有误', 300);
        }
    }
}