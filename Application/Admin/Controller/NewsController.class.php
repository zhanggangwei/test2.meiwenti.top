<?php
/**
 * Created by PhpStorm.
 * User: zgw
 * Date: 2017/3/27
 * Time: 11:53
 */
namespace Admin\Controller;

class NewsController extends CommonController {

    // 加载宏观资讯页面
//    public function macro(){
//        $this->display();
//    }

    //获得宏观资讯的数据
    public function getMacro(){
        $where = array('type' => 1);
        if ($title = I('title', '')) {
            $where['title'] = array('like', '%'.$title.'%');
        }
        $this->getNews($where);
    }

    //获取矿区资讯
    public function getOredistrict(){
        $where = array('type' => 2);
        if ($title = I('title', '')) {
            $where['title'] = array('like', '%'.$title.'%');
        }
        $this->getNews($where);
    }

    //获取轻松一刻
    public function getFuntime(){
        $where = array('type' => 3);
        if ($title = I('title', '')) {
            $where['title'] = array('like', '%'.$title.'%');
        }
        $this->getNews($where);
    }

    // 删除新闻
    public function del($id){
        $this->delD($id);
    }
    public function del1($id){
        $this->delD($id);
    }
    public function del2($id){
        $this->delD($id);
    }

    // 共有删除方法
    private function delD($id){
        if ($id) {
            $res = M('news')->delete($id);
            show_res($res);
        } else {
            alert('新闻id参数错误！',300);
        }
    }

    private function getNews($where){
        $db = M('news');

        $page_num  = I('pageCurrent') - 1;
        $page_size = I('pageSize', 2);

        // 为了让三个分类有各自可以控制的方法
        $num = '';
        switch ($where['type']) {
            case 2:
                $num = 1;
                break;
            case 3:
                $num = 2;
                break;
            default:
                $num = '';
                break;
        }

//        if ($title = I('title', '')) {
//            $where['title'] = array('like', '%'.$title.'%');
//        }
//        if ($account = I('account', '')) {
//            $where['a.account'] = array('like', $account.'%');
//        }

        $count = $db
//            ->join('left join coal_admin_auth_group_access ga on ga.uid = a.id')
//            ->join('left join coal_admin_auth_group g on ga.group_id = g.id')
            ->where($where)->count();

        $data = $db
//            ->field('a.id, a.account, a.nickname, a.phone, a.is_lock, a.create_time, g.title')
//            ->join('left join coal_admin_auth_group_access ga on ga.uid = a.id')
//            ->join('left join coal_admin_auth_group g on ga.group_id = g.id')
            ->where($where)
            ->limit($page_size * $page_num, $page_size)
            ->order('creat_time desc, id desc')
            ->select();
//        echo M()->_sql();exit;
        foreach ($data as $key => $value) {
            $data[$key] = $value;
            $option1 = "{
                id:'news_edit',
                url:'".U('Admin/News/editNews')."',
                data:{id:'".$value['id']."'},
                fresh:true,
                title:'文章编辑'
            }";
            $option2 = "{
                type:'get',
                data:{id:'".$value['id']."'},
                url:'".U('Admin/News/del'.$num)."',
                confirmMsg:'确定要删除吗？'
                }";

            $dostr = '<button type="button" class="btn-default" data-toggle="navtab" data-options="'.$option1.'">编辑</button>'.
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

    // 共有增加方法
    public function addNews(){
        if (IS_POST) {
            $post = I('post.');
            $post['picture'] = $post['pic'][0];
            $post['picture1'] = $post['pic'][1];
            $post['picture2'] = $post['pic'][2];
            if ($post['video']) {
                $post['video'] = 'http://img.meiwenti.top/'.$post['video'];
            }
            if (!$post['video'] && $post['video1']) {
                $post['video'] = $post['video1'];
            }
            if ($post['is_html'] == 1) {
                $content = $post['content1'];
            } else {
                $content = $post['content'];
            }
            $post['body'] = $content;
            $res = D('News')->addData($post);
            if ($res['code']) {
                $array = array('statusCode' => 200, 'message' => '处理成功！', 'closeCurrent' => true, 'tabid' => 'recruit');
                echo json_encode($array);exit;
            } else {
                alert($res['info'], 300);
            }
        }
        $this->display();
    }

    // 共有的编辑方法
    public function editNews(){
        if (IS_POST) {
            $post = I('post.');
            $map = array('id' => $post['id']);
            if ($post['video']) {
                $post['video'] = 'http://img.meiwenti.top/'.$post['video'];
            }
            $data = array(
                'type'  => $post['type'],
                'show_type'  => $post['show_type'],
                'title'  => $post['title'],
                'source'  => $post['source'],
                'writer'  => $post['writer'],
                'video'  => $post['video'],
                'description'  => $post['description'],
                'body'  => $post['content'],
            );
            if (!$data['video'] && $post['video1']) {
                $data['video'] = $post['video1'];
            }
            // dump($post);exit;
            if ($post['pic']) {
                $data['picture'] = $post['pic'][0];
                if ($post['show_type'] == 2) {
                    $data['picture1'] = $post['pic'][1];
                    $data['picture2'] = $post['pic'][2];
                }
            }

            $res = D('News')->editData($map, $data);


            if ($res['code'] and $res['message']!==false) {
                if ($res['info'] !== false) {
                    after_alert(array('closeCurrent' => true, 'tabid' => 'News_macro,News_oredistrict,News_funtime'));
                } else {
                    alert('处理失败！', 300);
                }
            } else {
                alert($res['message'], 300);
            }
        }
        $id = I('get.id');
        $info = M('news')->find($id);
        $this->info = $info;
        $this->display();
    }

    // 批量修改煤矿煤炭价格数据
    public function editAllCoalPrice(){
        // 所有煤矿
        $pits = M('pit_name')->select();
        $pit_cate = M('pit_cate')->select();

        $data = array(
            'pits' => $pits,
            'pit_cate' => $pit_cate,
        );
        $this->assign($data);
        $this->display();
    }

    // 临时增加方案
    public function editP(){
        $post = I('post.');
        $post['update_time'] = time();
        $post['create_time'] = time();
        $tmp = M('pit_price')->where(array('pit_id' => $post['pit_id'], 'cate_id' => $post['cate_id']))->find();
        if ($tmp) {
            $post['id'] = $tmp['id'];
            $res = M('pit_price')->save($post);
        } else {
            $res = M('pit_price')->add($post);
        }

        if ($res !== false) {
            echo json_encode(['msg' => date('H:i:s').'成功']);exit;
        } else {
            echo json_encode(['msg' => '失败']);exit;
        }
    }
}