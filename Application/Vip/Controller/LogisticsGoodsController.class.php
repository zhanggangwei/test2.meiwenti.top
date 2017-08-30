<?php
/**
 * Created by PhpStorm.
 * 煤炭贸易
 * User: zgw
 * Date: 2017/5/10
 * Time: 15:16
 */
namespace Vip\Controller;

class LogisticsGoodsController extends CommonController {
    // ---------------------货源大厅--------------------------------------
    // 获取货源大厅列表
    public function getLogisticsGoodsList(){
        $db = M('logistics_goods');
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
            ->order('create_time desc')
            ->select();
        // sql();
        // dump_text($data);
        foreach ($data as $key => $value) {
            // 出发地
            $data[$key]['sell_address'] = $value['from_province'].$value['from_city'].$value['from_area'];

            // 到达地
            $data[$key]['buy_address'] = $value['to_province'].$value['to_city'].$value['to_area'];

            // 交费
            if ($value['tip_type'] == 1) {
                $tip_type = '信息费';
            } else if ($value['tip_type'] == 2) {
                $tip_type = '保证金';
            }
            $data[$key]['tip'] = $tip_type.':'.$value['tip'].'元';

            // 煤炭种类
            $data[$key]['coal_type'] = coal_type($value['coal_type']);

            // 是否审核
            $data[$key]['is_passed'] = $value['is_passed']?'已审核':'未审核';

            $option2 = "{
                id:'seller_news_detail',
                data:{id:'".$value['id']."'},
                url:'".U('logisticsGoodsDetail')."',
                width:'800',
                height:'600',
                }";
            $option3 = "{
                id:'seller_news_edit',
                data:{id:'".$value['id']."'},
                url:'".U('logisticsGoodsEdit')."',
                width:'800',
                height:'600',
                }";
            $option4 = "{
                type:'get',
                data:{id:'".$value['id']."'},
                url:'".U('logisticsGoodsDel')."',
                confirmMsg:'确定要删除吗？'
                }";
            $data[$key]['dostr'] = create_button($option2, 'dialog', '详情')
                .create_button($option3, 'dialog', '编辑')
                .create_button($option4, 'doajax', '删除')
            ;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        // dump($info);exit;
        echo json_encode($info);
    }

    // 货源大厅详情
    public function logisticsGoodsDetail(){
        $id = I('id');
        $info = M('logistics_goods')->find($id);
        if ($info) {
            $this->info = $info;
            $this->display();
        } else {
            alert_illegal();
        }
    }

    // 货源大厅信息编辑
    public function logisticsGoodsEdit(){
        if (IS_POST) {
            $post = I('post.');
            $res = M('logistics_goods')->save($post);
            if ($res !== false) {
                after_alert(array('closeCurrent' => true, 'tabid' => 'LogisticsGoods_mySupply'));
            } else {
                alert_false();
            }
        }
        $id = I('get.id');
        if ($id) {
            // 货源信息
            $this->info = M('logistics_goods')->find($id);
            // 煤种
            $this->coal_info = M('coal_type')->select();
            // 运输费类型
            $this->tip_type = array(array(1 => '信息费'),array(2 => '保证金'));
            $this->display();
        } else {
            alert_illegal();
        }
    }

    // 货源大厅信息删除
    public function logisticsGoodsDel(){
        $id = I('id') + 0;
        if ($id) {
            // 已经拉货的不能删，没有re_goods没有记录才可以删。
            $re_goods_info = M('re_goods')->where(array('uid' => $id))->find();
            if ($re_goods_info) {
                alert_false('货单已经确认，不能删除');
            }
            $res = M('logistics_goods')->delete($id);
            show_res($res);
        } else {
            alert('操作有误', 300);
        }
    }

    // 待确认列表
    public function getLogisticsGoodsAudit(){
        $db = M('logistics_goods l');
        $where = array('re_goods.state' => array('in', array(0,1,2,3)));

        // 总公司和子公司关系,只能是当前公司的
        $where['l.writer_id'] = session('company_id');

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        // if ($name = trim(I('real_name', ''))) {
        //     $where['u.real_name'] = array('like', '%'.$name.'%');
        // }
        if ($title = I('l.title', '')) {
            $where['l.title'] = array('like', '%'.$title.'%');
        }

        $count = $db
            ->join('right join coal_re_goods re_goods on re_goods.uid = l.id')
            ->join('left join coal_users u on u.id = re_goods.writer_id')
            ->where($where)
            ->count();

        $data = $db
            ->field('re_goods.id, l.coal_type, l.price, l.quantity, l.from_province,l.from_city,l.from_area,
            l.to_province,l.to_city,l.to_area,l.tip_type,l.tip,re_goods.state, l.comment, u.real_name as driver_name, u.phone as driver_phone')
            ->join('right join coal_re_goods re_goods on re_goods.uid = l.id')
            ->join('left join coal_users u on u.id = re_goods.writer_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('re_goods.create_time desc')
            ->select();
        // sql();
        // dump_text($data);
        foreach ($data as $key => $value) {
            // 出发地
            $data[$key]['sell_address'] = $value['from_province'].$value['from_city'].$value['from_area'];

            // 到达地
            $data[$key]['buy_address'] = $value['to_province'].$value['to_city'].$value['to_area'];

            // 交费
            if ($value['tip_type'] == 1) {
                $tip_type = '信息费';
            } else if ($value['tip_type'] == 2) {
                $tip_type = '保证金';
            }
            $data[$key]['tip'] = $tip_type.':'.$value['tip'].'元';

            // 煤炭种类
            $data[$key]['coal_type'] = coal_type($value['coal_type']);

            // 承运状态
            $state = $value['state'];
            $data[$key]['state'] = getReGoodsType($value['state']);

            // 接单车主
            $data[$key]['driver'] = $value['driver_name'].'<br>('.$value['driver_phone'].')';

            // 操作
            switch ($state) {
                case 0:
                    // 确认发货
                    $option = "{
                    type:'get',
                    data:{id:'".$value['id']."',state:1},
                    url:'".U('confirm')."',
                    confirmMsg:'确定操作吗？'
                    }";
                    $dostr = create_button($option, 'doajax', '确认发货');
                    break;
                case 3:
                    // 确认到货
                    $option = "{
                    type:'get',
                    data:{id:'".$value['id']."',state:4},
                    url:'".U('confirm')."',
                    confirmMsg:'确定操作吗？'
                    }";
                    $dostr = create_button($option, 'doajax', '确认到货');
                    break;
                default:
                    $dostr = '';
                    break;
            }
            $data[$key]['dostr'] = $dostr;

            // $option2 = "{
            //     id:'seller_news_detail',
            //     data:{id:'".$value['id']."'},
            //     url:'".U('logisticsGoodsDetail')."',
            //     width:'800',
            //     height:'600',
            //     }";
            // $option3 = "{
            //     id:'seller_news_edit',
            //     data:{id:'".$value['id']."'},
            //     url:'".U('logisticsGoodsEdit')."',
            //     width:'800',
            //     height:'600',
            //     }";
            // $option4 = "{
            //     type:'get',
            //     data:{id:'".$value['id']."'},
            //     url:'".U('logisticsGoodsDel')."',
            //     confirmMsg:'确定要删除吗？'
            //     }";
            // $data[$key]['dostr'] = create_button($option2, 'doajax', '确')
                // .create_button($option3, 'dialog', '编辑')
                // .create_button($option4, 'doajax', '删除')
            ;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        // dump($info);exit;
        echo json_encode($info);
    }

    // 确认发货和确认到货
    public function confirm(){
        $state = I('state'); // 1、确认发货,2、确认到货
        $id = I('id'); // 这个id是re_goods的id
        // dump($_GET);exit;
        $res = M('re_goods')->save(array('id' => $id, 'state' => $state));

        show_res($res);
    }

    // 已经完成
    public function getLogisticsGoodsFinished(){
        $db = M('logistics_goods l');
        $where = array('re_goods.state' => array('in', array(4,5,6,7)));

        // 总公司和子公司关系,只能是当前公司的
        $where['l.writer_id'] = session('company_id');

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        // if ($name = trim(I('real_name', ''))) {
        //     $where['u.real_name'] = array('like', '%'.$name.'%');
        // }
        if ($title = I('l.title', '')) {
            $where['l.title'] = array('like', '%'.$title.'%');
        }

        $count = $db
            ->join('right join coal_re_goods re_goods on re_goods.uid = l.id')
            ->join('left join coal_users u on u.id = re_goods.writer_id')
            ->where($where)
            ->count();

        $data = $db
            ->field('re_goods.id, l.coal_type, l.price, l.quantity, l.from_province,l.from_city,l.from_area,
            l.to_province,l.to_city,l.to_area,l.tip_type,l.tip,re_goods.state, l.comment, u.real_name as driver_name, u.phone as driver_phone')
            ->join('right join coal_re_goods re_goods on re_goods.uid = l.id')
            ->join('left join coal_users u on u.id = re_goods.writer_id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('re_goods.create_time desc')
            ->select();
        // sql();
        // dump_text($data);
        foreach ($data as $key => $value) {
            // 出发地
            $data[$key]['sell_address'] = $value['from_province'].$value['from_city'].$value['from_area'];

            // 到达地
            $data[$key]['buy_address'] = $value['to_province'].$value['to_city'].$value['to_area'];

            // 交费
            if ($value['tip_type'] == 1) {
                $tip_type = '信息费';
            } else if ($value['tip_type'] == 2) {
                $tip_type = '保证金';
            }
            $data[$key]['tip'] = $tip_type.':'.$value['tip'].'元';

            // 煤炭种类
            $data[$key]['coal_type'] = coal_type($value['coal_type']);

            // 承运状态
            $state = $value['state'];
            $data[$key]['state'] = getReGoodsType($value['state']);

            // 接单车主
            $data[$key]['driver'] = $value['driver_name'].'<br>('.$value['driver_phone'].')';

            // 操作
            switch ($state) {
                case 0:
                    // 确认发货
                    $option = "{
                    type:'get',
                    data:{id:'".$value['id']."',state:1},
                    url:'".U('confirm')."',
                    confirmMsg:'确定操作吗？'
                    }";
                    $dostr = create_button($option, 'doajax', '确认发货');
                    break;
                case 3:
                    // 确认到货
                    $option = "{
                    type:'get',
                    data:{id:'".$value['id']."',state:4},
                    url:'".U('confirm')."',
                    confirmMsg:'确定操作吗？'
                    }";
                    $dostr = create_button($option, 'doajax', '确认到货');
                    break;
                default:
                    $dostr = '';
                    break;
            }
            // $data[$key]['dostr'] = $dostr;

            // $option2 = "{
            //     id:'seller_news_detail',
            //     data:{id:'".$value['id']."'},
            //     url:'".U('logisticsGoodsDetail')."',
            //     width:'800',
            //     height:'600',
            //     }";
            // $option3 = "{
            //     id:'seller_news_edit',
            //     data:{id:'".$value['id']."'},
            //     url:'".U('logisticsGoodsEdit')."',
            //     width:'800',
            //     height:'600',
            //     }";
            // $option4 = "{
            //     type:'get',
            //     data:{id:'".$value['id']."'},
            //     url:'".U('logisticsGoodsDel')."',
            //     confirmMsg:'确定要删除吗？'
            //     }";
            // $data[$key]['dostr'] = create_button($option2, 'doajax', '确')
            // .create_button($option3, 'dialog', '编辑')
            // .create_button($option4, 'doajax', '删除')
            ;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        // dump($info);exit;
        echo json_encode($info);
    }
}