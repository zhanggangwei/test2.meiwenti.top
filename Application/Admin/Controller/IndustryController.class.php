<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/3/28
 * Time: 9:44
 */
namespace Admin\Controller;
class IndustryController extends CommonController {
    //******************电厂库存***********************************
    public function getPowerin(){
        $tag = 'power_in';
        $db = M($tag);
        $where = array();

        $page_num  = I('pageCurrent') - 1;
        $page_size = I('pageSize', 2);

        $count = $db
            ->where($where)->count();

        $data = $db
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('sort asc')
            ->select();
//        echo M()->_sql();exit;
        foreach ($data as $key => $value) {
            $data[$key] = $value;
            $option1 = "{
                id:'".$tag."_edit',
                url:'".U('Admin/Industry/editPowerin')."',
                data:{id:'".$value['id']."'},
                type:'get',
                height:'400'
            }";
            $option2 = "{
                type:'get',
                data:{id:'".$value['id']."'},
                url:'".U('Admin/Industry/delPowerin')."',
                confirmMsg:'确定要删除吗？'
                }";

            $dostr = '<button type="button" class="btn-default" data-toggle="dialog" data-options="'.$option1.'">编辑</button>'.
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

    public function addPowerin(){
        if (IS_POST) {
            $post = I('post.');
            $res = D('PowerIn')->addData($post);
            if ($res['code']) {
                $array = array('statusCode' => 200, 'message' => '处理成功！', 'closeCurrent' => true, 'tabid' => 'Industry_powerin');
                echo json_encode($array);exit;
            } else {
                alert($res['info'], 300);
            }
        }
        $this->display();
    }

    public function editPowerin(){
        if (IS_POST) {
            $post = I('post.');
            $map = array('id' => $post['id']);
            $res = D('PowerIn')->editData($map, $post);
            if ($res['code']) {
                if ($res['info'] !== false) {
                    after_alert(array('closeCurrent' => true, 'tabid' => 'Industry_powerin'));
                } else {
                    alert('处理失败！', 300);
                }
            } else {
                alert($res['info'], 300);
            }
        }
        $id = I('get.id');
        $info = M('power_in')->find($id);
        $this->info = $info;
        $this->display();
    }

    public function delPowerin($id){
        $res = M('power_in')->delete($id);
        show_res($res);
    }

    // 采集电厂数据
    public function collectPowerin(){
        $ch = curl_init();
        $timeout = 0;

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4'));

        $url = 'http://m.zhaomei.com/';
        curl_setopt($ch, CURLOPT_URL, $url);
        $contents = curl_exec($ch);
        // echo $contents;
        $contents = str_replace(PHP_EOL, '', $contents);
        preg_match_all('/<table(.*)<\/table>/iU', $contents, $arr);
        // print_r($arr);

        // 采集电厂数据
        $power_data = str_replace('  ', ' ', $arr[1][0]);
        // echo $power_data;
        preg_match_all('/<td class="n-t1">(.*)<\/td>/iU', $power_data, $arr1);
        preg_match_all('/<td class="n-t2">(.*)<\/td>/iU', $power_data, $arr2);
        preg_match_all('/<td class="n-t3(.*)">(.*)<\/td>/iU', $power_data, $arr3);
        // echo $arr1;
        foreach ($arr1[0] as $key => $val) {
            $where = array('name' => $arr1[1][$key]);
            $data = array(
                'inventory'  => $arr2[1][$key],
                'daily'  => $arr3[2][$key],
                'update_time'  => date('Y-m-d')
            );
            M('power_in')->where($where)->save($data);
        }
        after_alert(array('tabid' => 'Industry_powerin'));
    }
    //******************港口库存***********************************
    public function getPort(){
        $tag = 'port_stock';
        $db = M($tag);
        $where = array();

        $page_num  = I('pageCurrent') - 1;
        $page_size = I('pageSize', 2);

        $count = $db
            ->where($where)->count();

        $data = $db
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('sort asc,id asc')
            ->select();
//        echo M()->_sql();exit;
        foreach ($data as $key => $value) {
            $data[$key] = $value;
            $option1 = "{
                id:'".$tag."_edit',
                url:'".U('Admin/Industry/editPort')."',
                data:{id:'".$value['id']."'},
                type:'get',
                height:'400'
            }";
            $option2 = "{
                type:'get',
                data:{id:'".$value['id']."'},
                url:'".U('Admin/Industry/delPort')."',
                confirmMsg:'确定要删除吗？'
                }";

            $dostr = '<button type="button" class="btn-default" data-toggle="dialog" data-options="'.$option1.'">编辑</button>'.
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

    public function addPort(){
        if (IS_POST) {
            $post = I('post.');
            $res = D('PortStock')->addData($post);
            if ($res['code']) {
                $array = array('statusCode' => 200, 'message' => '处理成功！', 'closeCurrent' => true, 'tabid' => 'Industry_portstock');
                echo json_encode($array);exit;
            } else {
                alert($res['info'], 300);
            }
        }
        $this->display();
    }

    public function editPort(){
        if (IS_POST) {
            $post = I('post.');
            $map = array('id' => $post['id']);
            $res = D('PortStock')->editData($map, $post);
            if ($res['code']) {
                if ($res['info'] !== false) {
                    $array = array('statusCode' => 200, 'message' => '处理成功！', 'closeCurrent' => true, 'tabid' => 'Industry_portstock');
                    echo json_encode($array);exit;
                } else {
                    alert('处理失败！', 300);
                }
            } else {
                alert($res['info'], 300);
            }
        }
        $id = I('get.id');
        $info = M('port_stock')->find($id);
        $this->info = $info;
        $this->display();
    }

    public function delPort($id){
        $res = M('port_stock')->delete($id);
        show_res($res);
    }
    // 采集港口数据
    public function collectPortstock(){
        $ch = curl_init();
        $timeout = 0;

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4'));

        $url = 'http://m.zhaomei.com/';
        curl_setopt($ch, CURLOPT_URL, $url);
        $contents = curl_exec($ch);
        // echo $contents;
        $contents = str_replace(PHP_EOL, '', $contents);
        preg_match_all('/<table(.*)<\/table>/iU', $contents, $arr);
        // print_r($arr);

        // 采集港口数据
        $power_data = str_replace('  ', ' ', $arr[1][1]);
        // echo $power_data;
        preg_match_all('/<td class="n-t1">(.*)<\/td>/iU', $power_data, $arr1);
        preg_match_all('/<td class="n-t2">(.*)<\/td>/iU', $power_data, $arr2);
        preg_match_all('/<td class="n-t3(.*)">(.*)<\/td>/iU', $power_data, $arr3);
        // echo $arr1;
        foreach ($arr1[0] as $key => $val) {
            $where = array('name' => $arr1[1][$key]);
            $data = array(
                'inventory'  => $arr2[1][$key],
                'in_out'  => $arr3[2][$key] + 0,
                'update_time'  => date('Y-m-d')
            );
            M('port_stock')->where($where)->save($data);
        }
        after_alert(array('tabid' => 'Industry_portstock'));
    }
    //******************海运价格***********************************
    public function getShip(){
        $tag = 'ship_price';
        $db = M($tag);
        $where = array();

        $page_num  = I('pageCurrent') - 1;
        $page_size = I('pageSize', 2);

        $count = $db
            ->where($where)->count();

        $data = $db
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('sort asc')
            ->select();
//        echo M()->_sql();exit;
        foreach ($data as $key => $value) {
            $data[$key] = $value;
            $option1 = "{
                id:'".$tag."_edit',
                url:'".U('Admin/Industry/editShip')."',
                data:{id:'".$value['id']."'},
                type:'get',
                height:'400'
            }";
            $option2 = "{
                type:'get',
                data:{id:'".$value['id']."'},
                url:'".U('Admin/Industry/delShip')."',
                confirmMsg:'确定要删除吗？'
                }";

            $dostr = '<button type="button" class="btn-default" data-toggle="dialog" data-options="'.$option1.'">编辑</button>'.
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

    public function addShip(){
        if (IS_POST) {
            $post = I('post.');
            $res = D('ShipPrice')->addData($post);
            if ($res['code']) {
                $array = array('statusCode' => 200, 'message' => '处理成功！', 'closeCurrent' => true, 'tabid' => 'Industry_shipprice');
                echo json_encode($array);exit;
            } else {
                alert($res['info'], 300);
            }
        }
        $this->display();
    }

    public function editShip(){
        if (IS_POST) {
            $post = I('post.');
            $map = array('id' => $post['id']);
            $res = D('ShipPrice')->editData($map, $post);
            if ($res['code']) {
                if ($res['info'] !== false) {
                    $array = array('statusCode' => 200, 'message' => '处理成功！', 'closeCurrent' => true, 'tabid' => 'Industry_shipprice');
                    echo json_encode($array);exit;
                } else {
                    alert('处理失败！', 300);
                }
            } else {
                alert($res['info'], 300);
            }
        }
        $id = I('get.id');
        $info = M('ship_price')->find($id);
        $this->info = $info;
        $this->display();
    }

    public function delShip($id){
        $res = M('ship_price')->delete($id);
        show_res($res);
    }

    // 采集海运数据
    public function collectShipprice(){
        $ch = curl_init();
        $timeout = 0;

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4'));

        $url = 'http://m.zhaomei.com/';
        curl_setopt($ch, CURLOPT_URL, $url);
        $contents = curl_exec($ch);
        // echo $contents;
        $contents = str_replace(PHP_EOL, '', $contents);
        preg_match_all('/<table(.*)<\/table>/iU', $contents, $arr);
        // print_r($arr);

        // 采集海运数据
        $power_data = str_replace('  ', ' ', $arr[1][2]);
        preg_match_all('/<td class="n-t1">(.*)<\/td>/iU', $power_data, $arr1);
        preg_match_all('/<td class="n-t2">(.*)<\/td>/iU', $power_data, $arr2);
        preg_match_all('/<td class="n-t3">(.*)<\/td>/iU', $power_data, $arr3);
        preg_match_all('/<td class="n-t4">(.*)<\/td>/iU', $power_data, $arr4);
        preg_match_all('/<td class="n-t4 (.*)">(.*)<\/td>/iU', $power_data, $arr5);
        // echo $arr1;
        // dump($arr5);exit;
        foreach ($arr1[0] as $key => $val) {
            $where = array('from' => $arr1[1][$key], 'to' => $arr2[1][$key]);
            $data = array(
                'ship'  => $arr3[1][$key],
                'price'  => $arr4[1][$key] + 0,
                'in_out'  => trim($arr5[2][$key]) + 0,
                'update_time'  => date('Y-m-d')
            );
            // dump($where);
            // dump($data);

            M('ship_price')->where($where)->save($data);
        }
        $power_data = str_replace('  ', ' ', $arr[1][3]);
        preg_match_all('/<td class="n-t1">(.*)<\/td>/iU', $power_data, $arr1);
        preg_match_all('/<td class="n-t2">(.*)<\/td>/iU', $power_data, $arr2);
        preg_match_all('/<td class="n-t3">(.*)<\/td>/iU', $power_data, $arr3);
        preg_match_all('/<td class="n-t4">(.*)<\/td>/iU', $power_data, $arr4);
        preg_match_all('/<td class="n-t4 (.*)">(.*)<\/td>/iU', $power_data, $arr5);
        // echo $arr1;
        // dump($arr5);exit;
        foreach ($arr1[0] as $key => $val) {
            $where = array('from' => $arr1[1][$key], 'to' => $arr2[1][$key]);
            $data = array(
                'ship'  => $arr3[1][$key],
                'price'  => $arr4[1][$key] + 0,
                'in_out'  => trim($arr5[2][$key]) + 0,
                'update_time'  => date('Y-m-d')
            );
            // dump($where);
            // dump($data);

            M('ship_price')->where($where)->save($data);
        }
        after_alert(array('tabid' => 'Industry_shipprice'));
    }
    //******************今日价格***********************************
    public function getTmarket(){
        $tag = 'today_market';
        $db = M($tag);
        $where = array();

        $page_num  = I('pageCurrent') - 1;
        $page_size = I('pageSize', 2);

        $count = $db
            ->where($where)->count();

        $data = $db
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('update_time desc,id desc')
            ->select();
//        echo M()->_sql();exit;
        foreach ($data as $key => $value) {
            $data[$key] = $value;
            $option1 = "{
                id:'".$tag."_edit',
                url:'".U('Admin/Industry/editTmarket')."',
                data:{id:'".$value['id']."'},
                type:'get',
                height:'400'
            }";
            $option2 = "{
                type:'get',
                data:{id:'".$value['id']."'},
                url:'".U('Admin/Industry/delTmarket')."',
                confirmMsg:'确定要删除吗？'
                }";

            $dostr = '<button type="button" class="btn-default" data-toggle="dialog" data-options="'.$option1.'">编辑</button>'.
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

    public function addTmarket(){
        if (IS_POST) {
            $post = I('post.');
            $res = D('TodayMarket')->addData($post);
            if ($res['code']) {
                $array = array('statusCode' => 200, 'message' => '处理成功！', 'closeCurrent' => true, 'tabid' => 'todaymarket');
                echo json_encode($array);exit;
            } else {
                alert($res['info'], 300);
            }
        }
        $this->display();
    }

    public function editTmarket(){
        if (IS_POST) {
            $post = I('post.');
            $map = array('id' => $post['id']);
            $res = D('TodayMarket')->editData($map, $post);
            if ($res['code']) {
                if ($res['info'] !== false) {
                    $array = array('statusCode' => 200, 'message' => '处理成功！', 'closeCurrent' => true, 'tabid' => 'todaymarket');
                    echo json_encode($array);exit;
                } else {
                    alert('处理失败！', 300);
                }
            } else {
                alert($res['info'], 300);
            }
        }
        $id = I('get.id');
        $info = M('today_market')->find($id);
        $this->info = $info;
        $this->display();
    }

    public function delTmarket($id){
        $res = M('today_market')->delete($id);
        show_res($res);
    }

    // 采集行情数据
    public function collectTodaymarket(){
        $ch = curl_init();
        $timeout = 0;

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 8_0 like Mac OS X) AppleWebKit/600.1.3 (KHTML, like Gecko) Version/8.0 Mobile/12A4345d Safari/600.1.4'));

        $url = 'http://m.zhaomei.com/coalprice';
        curl_setopt($ch, CURLOPT_URL, $url);
        $contents = curl_exec($ch);
        // echo $contents;
        $contents = str_replace(PHP_EOL, '', $contents);
        // 采集港口数据
        preg_match_all('/<h2><span class="a1">(.*)<\/span><span class="a2">(.*)<\/span><\/h2>/iU', $contents, $arr);
        preg_match_all('/<span class="name">(.*)<\/span>/iU', $contents, $arr1);
        preg_match_all('/<span class="price">(.*)<\/span>/iU', $contents, $arr2);
        preg_match_all('/<span class="arrow(.*)">(.*)<\/span>/iU', $contents, $arr3);
        foreach ($arr1[1] as $key => $val) {
            $where = array('name' => $val);
            $data = array(
                'price'  => $arr2[1][$key] + 0,
                'in_out'  => $arr3[2][$key] + 0,
                'update_time'  => date('Y-m-d')
            );
            if ($key < 5) {
                $data['system'] = $arr[1][0].$arr[2][0];
            } else if ($key < 11) {
                $data['system'] = $arr[1][1].$arr[2][1];
            } else if ($key < 17) {
                $data['system'] = $arr[1][2].$arr[2][2];
            } else if ($key < 20) {
                $data['system'] = $arr[1][3].$arr[2][3];
            } else {
                $data['system'] = $arr[1][4].$arr[2][4];
            }
            M('today_market')->where($where)->save($data);
        }
        after_alert(array('tabid' => 'Industry_portstock'));
    }

    //******************煤矿价格***********************************
    // 加载页面
    public function pitPrice(){
        $pits = M('pit_name')->order('sort desc')->select();
        $info = array();
        foreach ($pits as $key => $value) {
            $info[$key]['name'] = $value['name'];
            $info[$key]['sort'] = $value['sort'];
            $tmp = M('pit_price p')
                ->field('p.*,c.name as cate_name')
                ->join('left join coal_pit_cate c on c.id = p.cate_id')
                ->where(array('p.pit_id' => $value['id']))
                ->order('p.sort desc')
                ->select();
            $info[$key]['list'] = $tmp;
        }
        $this->info = $info;
        $this->display();
    }

    // 获得煤矿价格数据
    public function getPitPrice(){
        $pits = M('pit_name')->order('sort desc')->select();
        $info = array();
        foreach ($pits as $key => $value) {
            $info[$key]['name'] = $value['name'];
            $info[$key]['sort'] = $value['sort'];
            $tmp = M('pit_price p')
                ->field('p.*,c.name as cate_name')
                ->join('left join coal_pit_cate c on c.id = p.cate_id')
                ->where(array('p.pit_id' => $value['id']))
                ->order('p.sort desc')
                ->select();
            $info[$key]['list'] = $tmp;
        }
        echo json_encode($info);
    }
    // 增加煤矿价格数据（根据煤矿增加多条数据）
    public function addPitPrice(){
        $post = I('post.');
        $post['update_time'] = time();
        $post['create_time'] = time();
        // 循环
        foreach ($post as $key => $value) {
            $tmp = M('pit_price')->where(array('pit_id' => $post['pit_id'], 'cate_id' => $post['cate_id']))->find();
            if ($tmp) {
                $post['id'] = $tmp['id'];
                $res = M('pit_price')->save($post);
            } else {
                $res = M('pit_price')->add($post);
            }
        }

        if ($res !== false) {
            echo json_encode(['msg' => date('H:i:s').'成功']);exit;
        } else {
            echo json_encode(['msg' => '失败']);exit;
        }
    }
    // 修改单个煤矿多个价格数据
    public function editPitPrice(){

    }
    // 修改多个煤矿的价格
    public function editAllPitPrice(){

    }

    // 增加煤矿
    public function addPitName(){
        $name = I('name');
        try {
            $res = M('pit_name')->add(array('name' => $name));
            if ($res) {
                $ret['code'] = 1;
                $ret['message'] = '增加成功';
            } else {
                $ret['code'] = 0;
                $ret['message'] = '增加失败';
            }
            backJson($ret);
        } catch (\Exception $e) {
            $ret['code'] = 0;
            $ret['message'] = '增加失败';
            backJson($ret);
        }
    }

    // 增加煤矿煤种
    public function addPitCate(){
        $name = I('name');
        try {
            $res = M('pit_cate')->add(array('name' => $name));
            if ($res) {
                $ret['code'] = 1;
                $ret['message'] = '增加成功';
            } else {
                $ret['code'] = 0;
                $ret['message'] = '增加失败';
            }
            backJson($ret);
        } catch (\Exception $e) {
            $ret['code'] = 0;
            $ret['message'] = '增加失败';
            backJson($ret);
        }
    }

    // 增加单条信息
    public function addOnePitPrice(){

    }

    // 增加煤矿价格历史记录
    public function addPitPriceLog($data){
        $res = M('pit_price_log')->add($data);
        return $res;
    }

    // public function addTmarket(){
    //     if (IS_POST) {
    //         $post = I('post.');
    //         $res = D('TodayMarket')->addData($post);
    //         if ($res['code']) {
    //             $array = array('statusCode' => 200, 'message' => '处理成功！', 'closeCurrent' => true, 'tabid' => 'todaymarket');
    //             echo json_encode($array);exit;
    //         } else {
    //             alert($res['info'], 300);
    //         }
    //     }
    //     $this->display();
    // }
    //
    // public function editTmarket(){
    //     if (IS_POST) {
    //         $post = I('post.');
    //         $map = array('id' => $post['id']);
    //         $res = D('TodayMarket')->editData($map, $post);
    //         if ($res['code']) {
    //             if ($res['info'] !== false) {
    //                 $array = array('statusCode' => 200, 'message' => '处理成功！', 'closeCurrent' => true, 'tabid' => 'todaymarket');
    //                 echo json_encode($array);exit;
    //             } else {
    //                 alert('处理失败！', 300);
    //             }
    //         } else {
    //             alert($res['info'], 300);
    //         }
    //     }
    //     $id = I('get.id');
    //     $info = M('today_market')->find($id);
    //     $this->info = $info;
    //     $this->display();
    // }
    //
    // public function delTmarket($id){
    //     $res = M('today_market')->delete($id);
    //     show_res($res);
    // }
}