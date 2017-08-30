<?php
/**
 * 伊泰订单
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/8/1
 * Time: 10:45
 */
namespace Admin\Controller;

class YitaiBillController extends CommonController {
    // 伊泰订单列表
    public function billList(){
        $this->display();
    }

    // 获取伊泰订单列表数据
    public function getBillList(){

        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        $count = M('link_yitai y')
            ->count();

        $data = M('link_yitai y')
            ->join('left join coal_bill b on b.id = y.bill_id')
            ->limit($page_size * $page_num, $page_size)
            ->select();
        foreach ($data as $key => $value) {
            // 是否自备矿
            $data[$key]['is_zibei'] = $value == 1?'自备矿':'社会矿';
            // 运费
            if ($value['money'] == 0) {
                if ($value['state'] <= 6) {
                    $money = '还未完成';
                } else if ($value['state'] == 7) {
                    // 伊泰退单的
                    $money = '伊泰退单';
                } else {
                    $money = '异常';
                }
            } else {
                $money = $value['money'];
            }
            $data[$key]['money'] = $money;
        }
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list' => $data,
        );
        echo json_encode($info);exit;
    }
}