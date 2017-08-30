<?php
namespace Api\Controller;
//游客,操作包括贸易版和物流版
use Think\Controller;
class AccController extends ApiController  {
    //账号登陆
    public function login(){
        $res=D('Users','Logic')->login(I('account'),I('password'));
        if(!$res['code']){
            //账号密码验证失败
            backJson($res);
        }else{
            //如果是运输版则直接登录
            if($res['type']==1){
                //查看是否是通过认证了
                if($res['is_authentication']==0){
                    //没有认证的直接返回没有认证
                    backJson($res);
                }else{
                    //认证过的返回认证了哪些内容
                    $ret=$res;
                    //查看是否认证了司机
                    $driver=M('driver')->where(array('uid'=>session('user_id')))->find();
                    if(count($driver)){
                        //是否认证过司机
                        if ($driver['is_passed']==1){
                            $ret['driver']=1;//认证并通过
                        }else{
                            $ret['driver']=2;//认证未通过
                        }
                    }else{
                            $ret['driver']=0;//未进行认证
                    }
                    //司机绑定车辆情况:已经向车主发起申请,已经有绑定,还没有进行绑定
                    $driver=M('driver')->where(array('uid'=>session('user_id')))->find();
                    $ret['bind_truck']=M('truck')->where(array('id'=>$driver['truck_id']))->getField('lic_number');
                    if(!$ret['bind_truck']){
                        //查看是否有发起过绑定申请,车主还没有通过
                        $shenqing=M('driver_trucker')->where(array('driver_id'=>session('user_id'),'state'=>1))->find();
                        if($shenqing){
                            $ret['bind_truck']=2;
                        }else{
                            $ret['bind_truck']=0;
                        }
                    }
                    //查看是否认证了车辆并且通过审核
                    $truck=M('truck')->where(array('owner_id'=>session('user_id'),'owner_type'=>1))->find();
                    if($truck){
                        $ret['truck']=1;
                    }else{
                        $ret['truck']=0;
                    }
                    $ret['name']=M('users')->where(array('id'=>session('user_id')))->getField('real_name');
                    $photo=M('users')->where(array('id'=>session('user_id')))->getField('photo');
                    $ret['photo']=$photo==40?getTrueImgSrc('public/logo.png'):getTrueImgSrc($photo);
                    backJson($ret);
                }

            }else{
                //企业版只有管理员才能登录
                $user=M('users')->where(array('id'=>session('user_id')))->find();
                if ($user['is_admin']==0 and $user['is_login']==0){
                    //后台添加的一般管理员,则查看是否允许登录
                    $ret['code']=0;
                    $ret['message']='您无权登陆';
                    session(null);
                    unset($_SESSION);
                    session_destroy();
                    backJson($ret);
                }else{
                    //查看是否是通过认证了
                    if($res['is_authentication']==0){
                        //没有认证的直接返回没有认证
                        backJson($res);
                    }else{
                        $ret=$res;
                        //查找公司的名字传到前台
                        $user=M('users')->where(array('id'=>session('user_id')))->find();
                        $company=M('company')->where(array('id'=>$user['company_id']))->find();
                        //查看公司是否通过审核
                        if(count($company)){
                            if($company['is_passed']){
                                $ret['company']=$company['name'];
                            }else{
                                $ret['company']=2;
                            }
                        }else{
                                $ret['company']=0;
                        }
                        $ret['name']=$user['real_name'];
                        $photo=M('users')->where(array('id'=>session('user_id')))->getField('photo');
                        $ret['photo']=$photo==40?getTrueImgSrc('public/logo.png'):getTrueImgSrc($photo);
                        backJson($ret);
                    }
                    backJson($res);
                }
            }
        }
    }
    /************************************************退出账号*****************************************/
    function loginOut(){
        backJson(D('Users','Logic')->loginout());
    }
    //注册账号
    function register(){
        backJson(D('Users','Logic')->register());
    }
    /*************************************验证码(融资用)**********************************************************************/
    public function showVerify1(){
        $config =    array(
            'fontSize'    =>    25,    // 验证码字体大小
            'length'      =>    4,     // 验证码位数
            'useNoise'    =>    false, // 关闭验证码杂点
            'useImgBg'    =>  false,   //背景图片
            'imageH'      =>  60,
        );
        $Verify = new \Think\Verify($config);
        $Verify->entry();
    }
    /**************************************交易信息列表*****************************/
    function sellerNewsList(){
        //每页显示条数
        if(!I('size')){
            $pagesize=10;
        }else{
            $pagesize=I('size');
        }
        //当前页码
        if(!I('pagenum')){
            $page=1;
        }else{
            $page=I('pagenum');
        }
        //搜索条件
        $sql='';
        //排序处理
        $order=array('creat_time desc,id desc');
        switch (I('price_type')){
            case 'up':$order=array_merge(array('del_price'=>'asc'),$order);break;
            case 'down':$order=array_merge(array('del_price'=>'desc'),$order);break;
        }
        switch (I('add_time')){
            case 'up':$order=array_merge(array('creat_time'=>'asc'),$order);break;
            case 'down':$order=array_merge(array('creat_time'=>'desc'),$order);break;
        }
        switch (I('click')){
            case 'up':$order=array_merge(array('click'=>'asc'),$order);break;
            case 'down':$order=array_merge(array('click'=>'desc'),$order);break;
        }
        //煤种
        if(I('coal_type')){
            if($sql==''){
                $sql="coal_type = '".I('coal_type')."'";
            }else{
                $sql=$sql." and "."coal_type = '".I('coal_type')."'";
            }
        }
        //总数据
        $news['records']=M('seller_news')->count();//总条数
        $news['total']=ceil($news['records']/$pagesize);
        $news['code']=1;
        //获取当前页
        $limit=$pagesize*($page-1).",".$pagesize;
        $news['rows']=M('seller_news')->where($sql)->field('id,title,dwrz,sdjlf,del_price,pro_address_s')->order($order)->limit($limit)->select();
        //按照收到基硫分筛选
        if(I('sdjlf_d')){
            foreach ($news['rows'] as $k =>$v){
                $sdjlf=explode('-',$v['sdjlf']);
                if($sdjlf[0]<I('sdjlf_d')){
                    unset($news['rows'][$k]);
                }
            }
        }
        if(I('sdjlf_u')){
            foreach ($news['rows'] as $k =>$v){
                $sdjlf=explode('-',$v['sdjlf']);
                if($sdjlf[1]>I('sdjlf_u')){
                    unset($news['rows'][$k]);
                }
            }
        }

        if(count($news['rows'])==0){
            $ret['code']=0;
            $ret['message']='没有数据';
            die(json_encode($ret));
        }
        $news['pagenum']=$page;
        backJson($news);
    }
    //展示新闻
    public function showNew(){
        $new=M('news')->where(array('id'=>I('id')))->find();
        // 时间 2017年7月14日13:33:17 从今天开始的计数才是正确的
        // 更新点击次数
        M('news')->save(array('id' => $new['id'], 'click' => $new['click'] + 1));

        $new['true_pic']=getTrueImgSrc($new['picture']);
        $this->assign('new',$new);
        $this->display();
    }
    //APP获取的文章详情
    public function newDetail(){
        //验证提交信息
        if(!I('id')){
            $ret['code']=0;
            $ret['message']='必要的字段为空';
            backJson($ret);
        }
        //获取信息
        $new=M('news')->field('id,title,click')->where(array('id'=>I('id')))->find();

        if($new){
            // 更新点击次数
            M('news')->save(array('id' => $new['id'], 'click' => $new['click'] + 1));
            //正确获取信息
            $new['url']=U('showNew',array('id'=>$new['id']));
            $ret['code']=1;
            $ret['message']='返回成功';
            $ret['detail']=$new;
            echo json_encode(null_filter($ret));
        }else{
            //没有相关信息
            $ret['code']=2;
            $ret['message']='没有相关信息';
            echo json_encode(null_filter($ret));
        }

    }
    /***********************************************搜索新闻**************************************************/
    function searchNews(){
        //关键字
        $key_word=I('key_word')?I('key_word'):'';
        if(!I('size')){
            $pagesize=10;
        }else{
            $pagesize=I('size');
        }
        if(!I('pagenum')){
            $page=1;
        }else{
            $page=I('pagenum');
        }
        //总数据
        $where['title']=array('like',"%".$key_word."%");
        $news['records']=M('news')->where($where)->count();//总条数
        $news['total']=ceil($news['records']/$pagesize);
        $news['code']=1;
        //获取当前页
        $limit=$pagesize*($page-1).",".$pagesize;
        $news['rows']=M('news')->field('id,title,creat_time,source as writer,description,litpic')->where($where)->order(array('id desc','creat_time'=>'desc'))->limit($limit)->select();
        //拼接缩略图
        foreach ($news['rows'] as $key=>$value){
            if($value['litpic']){
                $news['rows'][$key]['litpic']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].C('OUT_LOAD_DIR').$value['litpic'];
            }
            $news['rows'][$key]['detail_url']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].U('showNew',array('id'=>$value['id']));
        }
        $news['pagenum']=$page;
        echo json_encode(null_filter($news));
    }
    //宏观新闻(2017-2-16)
    public function listHg(){
        if(!I('size')){
            $pagesize=10;
        }else{
            $pagesize=I('size');
        }
        if(!I('pagenum')){
            $page=1;
        }else{
            $page=I('pagenum');
        }
        //总数据
        $news['records']=M('news')->where(array('type'=>1))->count();//总条数
        $news['total']=ceil($news['records']/$pagesize);
        $news['code']=1;
        //获取当前页
        $limit=$pagesize*($page-1).",".$pagesize;
        $news['news']=M('news')->where(array('type'=>1))->field('id,title,creat_time,source as writer,description,litpic,show_type,picture,picture1,picture2,video')->order(array('id desc','creat_time'=>'desc'))->limit($limit)->select();
        //查看展示方式
        foreach ($news['news'] as $key=>$value){
            switch ($value['show_type']){
                case 1:
                    $news['news'][$key]['litpic1']=getTrueImgSrc($value['picture'],true);
                    unset($news['news'][$key]['video']);
                    break;
                case 2:
                    $news['news'][$key]['litpic1']=getTrueImgSrc($value['picture'],true);
                    $news['news'][$key]['litpic2']=getTrueImgSrc($value['picture1'],true);
                    $news['news'][$key]['litpic3']=getTrueImgSrc($value['picture2'],true);
                    unset($news['news'][$key]['video']);
                    break;
                case 3:
                    $news['news'][$key]['litpic1']=getTrueImgSrc($value['picture'],true);
                    unset($news['news'][$key]['video']);
                    break;
                case 4:
                    $news['news'][$key]['litpic1']=getTrueImgSrc($value['picture'],true);
                    $news['news'][$key]['video']=$value['video'];
                    break;
            }
            unset($news['news'][$key]['litpic']);
            unset($news['news'][$key]['picture']);
            unset($news['news'][$key]['picture1']);
            unset($news['news'][$key]['picture2']);
            $news['news'][$key]['detail_url']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].U('showNew',array('id'=>$value['id']));
        }
        //轮播广告
        $ad=M('ad')->where(array('is_use'=>1))->where(array('channel'=>1))->field('picture,link_url')->select();
        if($ad){
            foreach ($ad as $key=>$value){
                $ad[$key]['picture']=getTrueImgSrc($value['picture']);
            }
            $news['ad']=$ad;
        }else{
            $news['ad']=array();
        }
        $news['pagenum']=$page;
        backJson($news);
    }
    //矿区咨询(2017-2-16)
    // 行业数据
    public function listKq(){
        if(!I('size')){
            $pagesize=10;
        }else{
            $pagesize=I('size');
        }
        if(!I('pagenum')){
            $page=1;
        }else{
            $page=I('pagenum');
        }
        //总数据
        $news['records']=M('news')->where(array('type'=>2))->count();//总条数
        $news['total']=ceil($news['records']/$pagesize);
        $news['code']=1;
        //获取当前页
        $limit=$pagesize*($page-1).",".$pagesize;
        $news['news']=M('news')->where(array('type'=>2))->field('id,title,creat_time,source as writer,description,litpic,show_type,picture,picture1,picture2,video')->order(array('id desc','creat_time'=>'desc'))->limit($limit)->select();
        //查看展示方式
        foreach ($news['news'] as $key=>$value){
            switch ($value['show_type']){
                case 1:
                    $news['news'][$key]['litpic1']=getTrueImgSrc($value['picture'],true);
                    unset($news['news'][$key]['video']);
                    break;
                case 2:
                    $news['news'][$key]['litpic1']=getTrueImgSrc($value['picture'],true);
                    $news['news'][$key]['litpic2']=getTrueImgSrc($value['picture1'],true);
                    $news['news'][$key]['litpic3']=getTrueImgSrc($value['picture2'],true);
                    unset($news['news'][$key]['video']);
                    break;
                case 3:
                    $news['news'][$key]['litpic1']=getTrueImgSrc($value['picture'],true);
                    unset($news['news'][$key]['video']);
                    break;
                case 4:
                    $news['news'][$key]['litpic1']=getTrueImgSrc($value['picture'],true);
                    $news['news'][$key]['video']=$value['video'];
                    break;
            }
            unset($news['news'][$key]['litpic']);
            unset($news['news'][$key]['picture']);
            unset($news['news'][$key]['picture1']);
            unset($news['news'][$key]['picture2']);
            $news['news'][$key]['detail_url']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].U('showNew',array('id'=>$value['id']));
        }
        //轮播广告
        $ad=M('ad')->where(array('is_use'=>1))->where(array('channel'=>4))->field('picture,link_url')->select();
        if($ad){
            foreach ($ad as $key=>$value){
                $ad[$key]['picture']=getTrueImgSrc($value['picture']);
            }
            $news['ad']=$ad;
        }else{
            $news['ad']=array();
        }
        $news['pagenum']=$page;
        echo json_encode(null_filter($news));
    }
    // 煤矿价格列表
    public function pitPriceList(){
        $pit_name = trim(I('pit_name'));
        if ($pit_name) {
            $where = array('name' => array('like','%'.$pit_name.'%'));
        } else {
            $where = array();
        }
        $pits = M('pit_name')->where($where)->select();
        $info = array();
        foreach ($pits as $key => $value) {
            $info[$key]['name'] = $value['name'];
            $tmp = M('pit_price p')
                ->field('p.id,n.name,c.name,p.price,p.invoice')
                ->join('left join coal_pit_name n on p.pit_id = n.id')
                ->join('left join coal_pit_cate c on p.cate_id = c.id')
                ->where(array('p.pit_id' => $value['id']))->select();
            // 2017年8月8日14:39:06 zgw 增加热值和价差的字段，以后增加

            $info[$key]['list'] = $tmp;
        }
        if ($info) {
            $data['code'] = 1;
            $data['message'] = '获取成功';
            $data['data'] = $info;
        } else {
            $data['code'] = 0;
            $data['message'] = '没有数据';
        }
        backJson($data);
    }

    //娱乐咨询(2017-2-16)
    public function listYl(){
        if(!I('size')){
            $pagesize=10;
        }else{
            $pagesize=I('size');
        }
        if(!I('pagenum')){
            $page=1;
        }else{
            $page=I('pagenum');
        }
        //总数据
        $news['records']=M('news')->where(array('type'=>3))->count();//总条数
        $news['total']=ceil($news['records']/$pagesize);
        $news['code']=1;
        //获取当前页
        $limit=$pagesize*($page-1).",".$pagesize;
        $news['news']=M('news')->where(array('type'=>3))->field('id,title,creat_time,source as writer,description,litpic,show_type,picture,picture1,picture2,video')->order(array('id desc','creat_time'=>'desc'))->limit($limit)->select();
        //查看展示方式
        foreach ($news['news'] as $key=>$value){
            switch ($value['show_type']){
                case 1:
                    $news['news'][$key]['litpic1']=getTrueImgSrc($value['picture'],true);
                    unset($news['news'][$key]['video']);
                    break;
                case 2:
                    $news['news'][$key]['litpic1']=getTrueImgSrc($value['picture'],true);
                    $news['news'][$key]['litpic2']=getTrueImgSrc($value['picture1'],true);
                    $news['news'][$key]['litpic3']=getTrueImgSrc($value['picture2'],true);
                    unset($news['news'][$key]['video']);
                    break;
                case 3:
                    $news['news'][$key]['litpic1']=getTrueImgSrc($value['picture'],true);
                    unset($news['news'][$key]['video']);
                    break;
                case 4:
                    $news['news'][$key]['litpic1']=getTrueImgSrc($value['picture'],true);
                    $news['news'][$key]['video']=$value['video'];
                    break;
            }
            unset($news['news'][$key]['litpic']);
            unset($news['news'][$key]['picture']);
            unset($news['news'][$key]['picture1']);
            unset($news['news'][$key]['picture2']);
            $news['news'][$key]['detail_url']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].U('showNew',array('id'=>$value['id']));
        }
        //轮播广告
        $ad=M('ad')->where(array('is_use'=>1))->where(array('channel'=>4))->field('picture,link_url')->select();
        if($ad){
            foreach ($ad as $key=>$value){
                $ad[$key]['picture']=getTrueImgSrc($value['picture']);
            }
            $news['ad']=$ad;
        }else{
            $news['ad']=array();
        }
        $news['pagenum']=$page;
        echo json_encode(null_filter($news));
    }
    //行业数据(2017-2-16)
    public function listSj(){
        //电厂库存
        $data=M('power_in')->field('picture,litpic,name,id,inventory,daily')->select();
        foreach ($data as $key=>$value){
            $data[$key]['date']=round($value['inventory']/$value['daily'],2);
            //拼接缩略图
            $data[$key]['litpic']=getTrueImgSrc($value['picture'],true);
        }
        if($data){
            $ret['power']=$data;
        }else{
            $ret['power']=array();
        }
        //港口库存
        $data=M('port_stock')->field('picture,id,`name`,inventory,in_out,litpic')->select();
        foreach ($data as $key=>$value){
            //拼接缩略图
            $data[$key]['litpic']=getTrueImgSrc($value['picture'],true);
        }
        if($data){
            $ret['port']=$data;
        }else{
            $ret['port']=array();
        }
        //海运价格
        $data=M('ship_price')->field('picture,id,`from`,to,ship,litpic,price,in_out')->select();
        foreach ($data as $key=>$value){
            //拼接缩略图
            $data[$key]['litpic']=getTrueImgSrc($value['picture'],true);
        }
        if($data){
            $ret['ship']=$data;
        }else{
            $ret['ship']=array();
        }
        //轮播广告
        //轮播广告
        $ad=M('ad')->where(array('is_use'=>1))->where(array('channel'=>4))->field('picture,link_url')->select();
        if($ad){
            foreach ($ad as $key=>$value){
                $ad[$key]['picture']=getTrueImgSrc($value['picture']);
            }
            $ret['ad']=$ad;
        }else{
            $ret['ad']=array();
        }
        //返回数据
        $ret['code']=1;
        $ret['message']='获取成功';
        echo json_encode(null_filter($ret));
    }
    /************************************************今日行情****************************************************/
    public function listMarket(){
        //groupby不能用所以要组织sql语句
        $data=M('today_market')->field('id,system,name,price,in_out')->order(array('id','system'))->select();
        if($data){
            //按照system分组
            $result =   array();
            foreach($data as $k=>$v){
                $result[$v['system']][]    =   $v;
            }
            $rearr=array();
            $i=0;
            foreach ($result as $key =>$value){

                $rearr[$i]['type']=(string)$key;
                $rearr[$i]['data']=$value;
                $i++;
            }
            $ret['code']=1;
            $ret['message']='获取成功';
            $ret['row']=$rearr;
            //轮播广告
            $ad=M('ad')->where(array('is_use'=>1))->where(array('channel'=>3))->field('picture,link_url')->select();
            if($ad){
                foreach ($ad as $key=>$value){
                    $ad[$key]['picture']=getTrueImgSrc($value['picture']);
                }
                $ret['ad']=$ad;
            }else{
                $ret['ad']=array();
            }
            echo json_encode(null_filter($ret));
        }else{
            $ret['code']=0;
            $ret['message']='获取失败';
            echo  json_encode(null_filter($ret));
        }
    }
    /*******************************************招聘列表*****************************************************/
    function recruit(){
        //分页变量
        $pagesize=10;
        $page=I('page')?I('page'):1;
        //总条数
        $count=M('recruit')->where(array('is_passed'=>1))->select();
        $count=count($count);
        //总页数
        $total_page=ceil($count/$pagesize);
        //limit
        $limit=$pagesize*($page-1).",".$pagesize;
        //获取当前页
        $recruit=M('recruit')->where(array('is_passed'=>1))->order('addate desc,id')->field('id,editor_id,title,salary,address,addate')->limit($limit)->select();
        //获取账号信息
        foreach ($recruit as $key=>$value){
            $acc=M('users')->where(array('id'=>$value['editor_id']))->field('type,account,photo_lit')->find();
            $recruit[$key]['tel']=$acc['account'];
            //图片处理
            switch ($acc['type']){
                case 0;
                    if(!$acc['photo_lit']){
                        $recruit[$key]['picture']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].C('OUT_LOAD_DIR')."../images/buyer_photo.png";
                    }else{
                        $recruit[$key]['picture']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].C('OUT_LOAD_DIR').$acc['photo_lit'];
                    }
                    $name=M('buyer')->where(array('aid'=>$value['editor_id']))->field('name')->find();break;
                case 1;
                    if(!$acc['photo_lit']){
                        $recruit[$key]['picture']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].C('OUT_LOAD_DIR')."../images/seller_photo.png";
                    }else{
                        $recruit[$key]['picture']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].C('OUT_LOAD_DIR').$acc['photo_lit'];
                    }
                    $name=M('seller')->where(array('aid'=>$value['editor_id']))->field('name')->find();break;
                case 2;
                    if(!$acc['photo_lit']){
                        $recruit[$key]['picture']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].C('OUT_LOAD_DIR')."../images/company_photo.png";
                    }else{
                        $recruit[$key]['picture']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].C('OUT_LOAD_DIR').$acc['photo_lit'];
                    }
                    $name=M('company')->where(array('aid'=>$value['editor_id']))->field('name')->find();break;
                case 3;
                    if(!$acc['photo_lit']){
                        $recruit[$key]['picture']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].C('OUT_LOAD_DIR')."../images/trucker_photo.png";
                    }else{
                        $recruit[$key]['picture']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].C('OUT_LOAD_DIR').$acc['photo_lit'];
                    }
                    $name=M('trucker')->where(array('aid'=>$value['editor_id']))->field('name')->find();break;
                default:$ret['code']=0;$ret['message']='获取失败';die(json_encode($ret));
            }
            $recruit[$key]['name']=$name['name'];
        }
        //处理输出
        if(count($recruit)){
            $ret['code']=1;
            $ret['message']='获取成功';
            $ret['rows']=$recruit;
            $ret['page']=$page;
            $ret['total_page']=$total_page;
            $ret['total']=$count;
            echo json_encode($ret);
        }else{
            $ret['code']=0;
            $ret['message']='没有数据';
            echo json_encode($ret);
        }
    }
    /*******************************************求职列表*****************************************************/
    function apply(){
        //分页变量
        $pagesize=10;
        $page=I('page')?I('page'):1;
        //总条数
        $count=M('apply')->where(array('is_passed'=>1))->order('addate asc,id')->select();
        $count=count($count);
        //总页数
        $total_page=ceil($count/$pagesize);
        //limit
        $limit=$pagesize*($page-1).",".$pagesize;
        //获取当前页
        $recruit=M('apply')->where(array('is_passed'=>1))->order('addate desc,id')->limit($limit)->select();
        foreach ($recruit as $key=>$value){
            if(!$value['picture']){
                $recruit[$key]['picture']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].C('OUT_LOAD_DIR')."../images/apply_photo.png";
            }
        }

        //处理输出
        if(count($recruit)){
            $ret['code']=1;
            $ret['message']='获取成功';
            $ret['rows']=$recruit;
            $ret['page']=$page;
            $ret['total_page']=$total_page;
            $ret['total']=$count;
            echo json_encode($ret);
        }else{
            $ret['code']=0;
            $ret['message']='没有数据';
            echo json_encode($ret);
        }
    }
    /************************************************伊泰派单测试*******************************************/
    function ytCreatBill(){
        vendor('Yitai.Yitai');
        $yitai=new \Yitai();
        $ret=$yitai->creatBill(I('seller'),I('buyer'),I('lic_number'),I('coal_type'),I('code_str'),I('bill_number'));
        backJson($ret);
    }
    /***********************************************忘记密码1***********************************************/
    function forgotPsw1(){
        //查看提交的手机号是否存在
        $account=I('phone');
        $acc=M('users')->where("phone='$account' or account='$account'")->find();

        if(!$acc){
            $ret['code']=0;
            $ret['message']='手机号输入有误';
            die(json_encode($ret));
        }


        //发送短信
        //生产随机码
        $ver_num=rand_num(6);
        session('ver_num',$ver_num);
        //引入类库
        vendor('TopSdk.TopSdk');
        $mes=new \Message();
        if($mes->regSendCode($acc['phone'],$ver_num)){
            $ret['code']=1;
            $ret['message']='短信发送成功';
            $ret['ver_num']=session('ver_num');
            die(json_encode($ret));
        }else{
            $ret['code']=0;
            $ret['ver_num']=session('ver_num');
            $ret['message']='发送失败';
            die(json_encode($ret));
        }
    }
    /***********************************************忘记密码2***********************************************/
    function forgotPsw2(){
        $account=I('phone');
        //查看验证码是否为空
        if(!I('ver_num')){
            $ret['code']=0;
            $ret['message']='验证码不能为空';
            backJson($ret);
        }
        //查看短信验证码是否正确
        if(!session('ver_num') || session('ver_num')!=I('ver_num')){
            $ret['code']=0;
            $ret['message']='手机验证码错误';
            backJson($ret);
        }
        //查看新密码是否为空
        if(!I('new1')||!I('new2')){
            $ret['code']=0;
            $ret['message']='新密码不能为空';
            backJson($ret);
        }
        //查看两个密码是否一样
        if(I('new1')!=I('new2')){
            $ret['code']=0;
            $ret['message']='两次密码输入不一样';
            backJson($ret);
        }
        //修改密码
        $data['password']=authcode(I('new1'),C('CODE_KEY'));
        $res=M('users')->where("phone='$account' or account='$account'")->save($data);
        if($res!==false){
            $ret['code']=1;
            $ret['message']='密码修改成功';
            backJson($ret);
        }else{
            $ret['code']=0;
            $ret['message']='修改失败，请稍后再试';
            backJson($ret);
        }
    }
    /***********************************************跟伊泰对接获取空车列表**************************************************/
    function getFreeTruck(){
        $truck=M('truck t')
            ->order('last_time asc,t.id asc')
            ->join('coal_driver d on d.truck_id=t.id')
            ->where(array('d.is_work'=>1))
            ->where(array('t.is_passed'=>1,'t.state'=>1,'t.is_comperation'=>1))
            ->where('t.user_id!=0')
            ->field('t.lic_number,t.jiyun,t.user_id as company_id,t.owner_order')
            ->group('t.id')
            ->select();
        if(count($truck)){
            $ret['code']=1;
            $ret['rows']=$truck;
            die(json_encode($ret));
        }else{
            $ret['code']=0;
            die(json_encode($ret));
        }
    }
    /************************************************跟伊泰对接自动派单*****************************************/
    function arrBill(){
        if(!I('seller')||!I('buyer')||!I('lic_number')||!I('coal_type')||!I('code_str')||!I('bill_number')){
            $ret['code']=0;
            $ret['message']='提交信息有误';
            die(json_encode($ret));
        }

        //用提煤单号检查该订单是否派出过
        $ifbill=M('link_yitai')->where(array('bill_number'=>I('bill_number')))->find();
        if($ifbill){
            $ret['code']=0;
            $ret['message']='该订单已经派出了';
            die(json_encode($ret));
        }
        // 2017年6月4日15:27:54 zgw add 检查二维码是否有重复
        // 检查二维码是否有重复
        // $ewm = M('link_yitai')->where(array('code_str'=>I('code_str')))->find();
        // if($ewm){
        //     $ret['code']=0;
        //     $ret['message']='二维码有重复';
        //     die(json_encode($ret));
        // }
        //检查是否车辆可用
        $truck=M('truck')->where(array('lic_number'=>I('lic_number'),'is_passed'=>1,'state'=>1))->find();
        if(!$truck){
            $ret['code']=0;
            $ret['message']='车辆状态异常';
            backJson($ret);
        }
        // 2017年7月1日18:22:07 zgw 司机不做限制。出现伊泰派单后，司机点休息，然后巧合调度改了310、510锁定切换。所以去掉
        // $is_truck_work=D('Truck','Logic')->isDispatch($truck['id'],$truck['user_id']);
        // if($is_truck_work['code']!=1){
        //     $ret['code']=0;
        //     $ret['message']=$is_truck_work['message'];
        //     die(json_encode($ret));
        // }

        //检测输入的买家卖家系统中是否已经存在
        $seller_id = getOrderCompanyId(I('seller'));

        $bill['seller']=$seller_id;

        $buyer_id = getOrderCompanyId(I('buyer'));

        $bill['buyer']=$buyer_id;



        M()->startTrans();
        //检测是否已经存在煤种,如果不存在则添加后返回ID,如果存在则直接使用ID

        // 获得计划的第一辆车
        $trucks = D('Truck', 'Logic')->getWaitTrucks(session('company_id'));
        // dump_text($trucks);
        if ($trucks) {

            $first_truck = $trucks[0];
        }

        $coal_type=M('coal_type')->where(array('name'=>I('coal_type')))->find();
        if($coal_type){
            $data2['coal_type']=$coal_type['id'];
        }else{
            $coal_type_id=M('coal_type')->add(array('name'=>I('coal_type')));
            $data2['coal_type']=$coal_type_id;
        }
        //增加大订单
        $data2['order_id']=$data['order_id']=I('order_id')?I('order_id'):'MWT'."YITAI".'T'.time().rand_num();
        $data2['create_time']=date('Y-m-d H:i:s');
        $data2['seller_id']=$seller_id;
        $data2['buyer_id']=$buyer_id;
        $data2['order_type']=6;
        $data2['quantity']=I('arrange_w')?I('arrange_w'):40;
        $order_res=M('orders')->add($data2);

        //增加物流安排
        $logistics['order_id']=$data2['order_id'];
        $logistics['quantity']=I('arrange_w')?I('arrange_w'):40;
        $logistics['res_quantity']=0;
        $logistics['assign_id']=0;
        $logistics['assigned_id']=$truck['user_id'];
        $logistics['create_time']=date('Y-m-d H:i:s');
        $logistics_res=M('logistics')->add($logistics);
        //增加提煤单
        $bill['company']=$truck['user_id'];
        $bill['trucker_id']=$truck['owner_id'];
        $bill['logistics_id']=$logistics_res;
        $bill['arrange_w']=I('arrange_w')?I('arrange_w'):40;
        $bill['dis_time']=date("Y-m-d H:i:s");
        $bill['state']=1;
        $bill['create_type']=1;
        $bill['order_id']=$data2['order_id'];
        $bill['truck_id']=$truck['id'];
        $bill['owner_type']=$truck['owner_type'];
        $bill['anchored_id']=$truck['anchored_id'];
        $bill['use_type']=I('is_zibei')?2:1;
        $bill_res=M('bill')->add($bill);

        //增加伊泰关联表
        $link_yitai['bill_id']=$bill_res;
        $link_yitai['bill_number']=I('bill_number');
        $link_yitai['code_str']=I('code_str');
        $link_yitai['coal_type']=I('coal_type');
        //伊泰返回的是否是自备矿的标识
        $link_yitai['is_zibei']=I('is_zibei');
        //返回是否是卡片打的提煤单
        $link_yitai['is_hand']=I('is_hand');
        //计算派出滞后时间
        if(!I('dis_time')){
            $link_yitai['dis_use_time']='未知';
        }else {
            $link_yitai['dis_time'] =I('dis_time');
            $dis_time = time();
            $begin_time = strtotime(I('dis_time'));
            $begin_use_time = $dis_time - $begin_time;

            $day = floor($begin_use_time / 86400);
            $hour = floor(($begin_use_time - ($day * 86400)) / 3600);
            $minit = floor(($begin_use_time - ($day * 86400) - $hour * 3600) / 60);
            if ($day) {
                $link_yitai['dis_use_time'] = $day . '天' . $hour . '小时' . $minit . '分钟';
            } elseif ($hour) {
                $link_yitai['dis_use_time'] = $hour . '小时' . $minit . '分钟';
            } elseif ($minit) {
                $link_yitai['dis_use_time'] = $minit . '分钟';
            } else {
                $link_yitai['dis_use_time'] = '不到一分钟';
            }
        }
        $link_yitai_res=M('link_yitai')->add($link_yitai);

        //更新车辆状态
        {
            //为了处理历史派单
            $user_id=M('truck')->where(array('lic_number'=>I('lic_number')))->getField('user_id');
            $list_trucks=M('truck')->where(array('user_id'=>$user_id,'state'=>1))->field('id,lic_number')->order('last_time asc, id asc')->select();
            $first_truck='';
            foreach ($list_trucks as $key=>$value){
                $code=D('Truck', 'Logic')->isDispatch($value['id'],$user_id);

                if($code['code']==1){
                    $first_truck=$value['lic_number'];
                    break;
                }else{
                    continue;
                }
            }
        }


        $truck_res=M('truck')->where(array('id'=>$truck['id']))->save(array('state'=>2));

        if($order_res&&$logistics_res&&$bill_res&&$link_yitai_res&&$truck_res!==false){

            //处理车辆状态表
            {
                //为了得到空车列表里车辆的请求状态,如果派单成功则清除掉该车辆的状态数据
                M('truck_restatue')->where(array('lic_number'=>I('lic_number')))->delete();
            }

            addArrHistory($first_truck,I('lic_number'),I('bill_number'),$bill_res,$bill['dis_time'],$bill['company']);            M()->commit();
            $ret['code']=1;
            $ret['message']='成功';
            //如果是卡片打印出来的单子，就不提醒司机
            if(!I('is_hand')){
                noticeBill($truck['id']);
            }
            die(json_encode($ret));
        }else{
            M()->rollback();
            $ret['code']=0;
            $ret['message']='失败';
            die(json_encode($ret));
        }
    }
    /************************************************跟伊泰对接获取数据库重车状态的订单*****************************************/
    function getDoingBill(){
        $bill=M('bill b')
            ->join('coal_link_yitai y on y.bill_id=b.id')
            ->where("state=1 or state=2 or state=3 or state=4 or state=5")
            ->field('bill_number,truck_id,company as company_id')
            ->select();

        //获取集运站服务器地址
        foreach ($bill as $key=>$value){
            $bill[$key]['jiyun']=M('truck')->where(array('id'=>$value['truck_id']))->getField('jiyun');
            $bill[$key]['owner_order']=M('truck')->where(array('id'=>$value['truck_id']))->getField('owner_order');
            unset($bill[$key]['truck_id']);
        }
        if(count($bill)){
            $ret['code']=1;
            $ret['rows']=$bill;
            echo json_encode($ret);
        }else{
            $ret['code'] = 0;
            $ret['message'] = '没有数据';
            die(json_encode($ret));
        }
    }
    /************************************************跟伊泰对接通过提煤单号结束订单*****************************************/
    function finishBill()
    {
        if (!I('bill_number')|| !I('end_w')||!I('money')||!I('end_time')) {
            $ret['code'] = 0;
            $ret['message'] = '提交参数有误';
            die(json_encode($ret));
        }
        $bill=M('bill b')
            ->join('coal_link_yitai y on y.bill_id = b.id')
            ->where(array('bill_number'=>I('bill_number')))
            ->where("state!=6 and state!=7 and state!=8")
            ->field('b.id as id,y.bill_number as bill_number,b.truck_id as truck_id,b.driver_id as driver_id')
            ->find();
        if(!count($bill)){
            $ret['code'] = 0;
            $ret['message'] = '订单状态异常';
            die(json_encode($ret));
        }
        M()->startTrans();
        $truck['state']=1;
        //2017-06-27如果提交的有结束时间就依照结束时间为准,如果没有提交依照当前时间
        $truck['last_time']=I('end_time');
        $truck_res=M('truck')->where(array('id'=>$bill['truck_id']))->save($truck);
        $bill_data['state']=6;
        $bill_data['end_first_w']=I('end_w')+15;
        $bill_data['end_second_w']=15;
        $bill_data['end_w']=I('end_w');

         if(I('begin_w')){
              $bill_data['begin_first_w']=15;
              $bill_data['begin_second_w']=I('begin_w')+15;
              $bill_data['begin_w']=I('begin_w');
          }

        $bill_data['end_first_time']=date("Y-m-d H:i:s");
        $bill_data['end_second_time']=date("Y-m-d H:i:s");

        $link_yitai['do_end_time']=date('Y-m-d H:i:s');
        //运费
        $link_yitai['money']=I('money');
        //计算派出滞后时间
        if(!I('end_time')){
            $link_yitai['end_use_time']='未知';
            $link_yitai['end_time']=null;
        }else{
            $link_yitai['end_time']=I('end_time');
            $dis_time=time();
            $begin_time=strtotime(I('end_time'));
            $begin_use_time=$dis_time-$begin_time;

            $day=floor($begin_use_time/86400);
            $hour=floor(($begin_use_time-($day*86400))/3600);
            $minit=floor(($begin_use_time-($day*86400)-$hour*3600)/60);
            if($day){
                $link_yitai['end_use_time']=$day.'天'.$hour.'小时'.$minit.'分钟';
            }elseif ($hour){
                $link_yitai['end_use_time']=$hour.'小时'.$minit.'分钟';
            }elseif ($minit){
                $link_yitai['end_use_time']=$minit.'分钟';
            }else{
                $link_yitai['end_use_time']='不到一分钟';
            }
        }


        $bill_res=M('bill')
            ->where(array('id'=>$bill['id']))
            ->where("state!=6 and state!=7 and state!=8")
            ->save($bill_data);
        $link_res=M('link_yitai y')
            ->where(array('bill_number'=>I('bill_number')))
            ->save($link_yitai);
        if($truck_res!==false&&$bill_res!==false&&$link_res!==false){
            M()->commit();
            $ret['code'] = 1;
            $ret['message'] = '操作成功';
            // 预暂停车辆
            is_truck_cron($bill['truck_id'], 'finish');

            //如果是通过IC卡刷出的提煤单则不给司机推送消息
            $is_hand=M('link_yitai')->where(array('bill_number'=>I('bill_number')))->getField('is_hand');
            if($is_hand!=1){
                D('Push','Logic')->noticeDriver('提煤单完结','您所拉运的提煤单已经成功完结,请知悉!',$bill['driver_id']);
            }

            die(json_encode($ret));
        }else{
            M()->rollback();
            $ret['code'] = 0;
            $ret['message'] = '操作失败';
            die(json_encode($ret));
        }
    }
    /************************************************跟伊泰对接获取数据库空车状态的订单（）*****************************************/
    function getDoingBill1(){
        $bill=M('bill b')
            ->join('coal_link_yitai y on y.bill_id=b.id')
            ->where("state=2")
            ->field('bill_number,truck_id,company as company_id')
            ->select();
        //获取集运站服务器地址
        foreach ($bill as $key=>$value){
            $bill[$key]['jiyun']=M('truck')->where(array('id'=>$value['truck_id']))->getField('jiyun');
            $bill[$key]['owner_order']=M('truck')->where(array('id'=>$value['truck_id']))->getField('owner_order');
            unset($bill[$key]['truck_id']);
        }
        if(count($bill)){
            $ret['code']=1;
            $ret['rows']=$bill;
            echo json_encode($ret);
        }else{
            $ret['code'] = 0;
            $ret['message'] = '没有数据';
            die(json_encode($ret));
        }
    }
    /************************************************跟伊泰对接通过提煤单号更改重车为重车状态*****************************************/
    function finishBill1()
    {
        if (!I('bill_number') || !I('begin_w') || !I('begin_time')) {
            $ret['code'] = 0;
            $ret['message'] = '提交参数有误';
            die(json_encode($ret));
        }
        //防止数据
        $bill=M('bill b')
            ->join('coal_link_yitai y on y.bill_id=b.id')
            ->where(array('y.bill_number'=>I('bill_number'),'state'=>2))
            ->field('b.id as id,y.bill_number as bill_number')
            ->find();
        if(!count($bill)){
            $ret['code'] = 0;
            $ret['message'] = '订单状态异常';
            die(json_encode($ret));
        }

        $bill_data['state']=4;
        $bill_data['begin_first_w']=15;
        $bill_data['begin_second_w']=15+I('begin_w');
        $bill_data['begin_w']=I('begin_w');
        $bill_data['begin_first_time']=date('Y-m-d H:i:s');
        $bill_data['begin_second_time']=date('Y-m-d H:i:s');
        //计算派出滞后时间
        if(!I('begin_time')){
            $link_yitai['begin_time']=null;
            $link_yitai['begin_use_time']='未知';
        }else {
            $link_yitai['begin_time']=I('begin_time');
            $begin_time = time();
            $dis_time = strtotime(I('begin_time'));
            $begin_use_time = $begin_time - $dis_time;
            $day = floor($begin_use_time / 86400);
            $hour = floor(($begin_use_time - ($day * 86400)) / 3600);
            $minit = floor(($begin_use_time - ($day * 86400) - $hour * 3600) / 60);
            if ($day) {
                $link_yitai['begin_use_time'] = $day . '天' . $hour . '小时' . $minit . '分钟';
            } elseif ($hour) {
                $link_yitai['begin_use_time'] = $hour . '小时' . $minit . '分钟';
            } elseif ($minit) {
                $link_yitai['begin_use_time'] = $minit . '分钟';
            } else {
                $link_yitai['begin_use_time'] = '不到一分钟';
            }
        }
        $data1['begin_time']=I('begin_time');
        $bill_res=M('bill ')
            ->where(array('id'=>$bill['id'],'state'=>2))
            ->save($bill_data);
        $link_res=M('link_yitai')
            ->where(array('bill_number'=>I('bill_number')))
            ->save($link_yitai);
        if($bill_res!==false and $link_res!==false){
            $ret['code'] = 1;
            $ret['message'] = '操作成功';
            die(json_encode($ret));
        }else{
            $ret['code'] = 0;
            $ret['message'] = '操作失败';
            die(json_encode($ret));
        }
    }
    /************************************************跟伊泰对接获取司机退单的列表*****************************************/
    function getRebill(){
        $rebill=M('driver_rbill r')
            ->join('coal_bill b on r.bill_id=b.id')
            ->join('coal_link_yitai y on y.bill_id=b.id')
            ->where(array('r.state'=>5,'b.create_type'=>1))
            ->field('bill_number,truck_id')
            ->select();
        foreach ($rebill as $key=>$value){
            $arr[$key]['bill_number']=$value['bill_number'];
            $arr[$key]['jiyun']=M('truck')->where(array('id'=>$value['truck_id']))->getField('jiyun');
        }
        if(count($arr)){
            $ret['rows']=$arr;
            $ret['code']=1;
            backJson($ret);
        }else{
            $ret['rows']=$arr;
            $ret['code']=0;
            backJson($ret);
        }
    }
    /*********************************************伊泰退单成功提交过来处理退单成功********************************************************/
    function acceptRebill(){
        if (!I('bill_number')) {
            $ret['code'] = 0;
            $ret['message'] = '提交参数有误';
            die(json_encode($ret));
        }
        // 2017年7月2日13:41:31 zgw 为了实现车队只用打电话退单，系统就能自动获取到，不用手动再点，做此优化
        $yitai_bill = M('link_yitai')->where(array('bill_number'=>trim(I('bill_number'))))->find();
        if (!$yitai_bill) {
            $ret['code'] = 0;
            $ret['message'] = '没有伊泰生成的单';
            die(json_encode($ret));
        }
        $bill_id = $yitai_bill['bill_id'];

        // 2017年6月21日15:26:58 edit zgw 只做改动，没有记录。在reason做记录
        $rebill = M('driver_rbill')->where(array('bill_id' => $bill_id))->find();
        // 2、reason记录伊泰操作
        if ($rebill) {
            $res2 = M('driver_rbill')->save(array('id' => $rebill['id'], 'reason' => $rebill['reason'] . '（伊泰已经退单）'));
        } else {
            // 生成一个退单记录
            $data['return_time'] = date("Y-m-d H:i:s");
            $data['bill_id']      = $bill_id;
            $data['state']        = 5;
            $data['reason']       = '伊泰系统主动退单，系统已处理';
            $res2 = M('driver_rbill')->add($data);
        }
        // 1、正常废弃货单
        $res=D('Bill','Logic')->returnPass($bill_id);

        if($res && $res2 !== false){
            // 拿到单的车辆，判断预暂停
            $truck_id = M('bill')->where(array('id' => $bill_id))->getField('truck_id');
            is_truck_cron($truck_id, 'return');
            $ret['code']=1;
            $ret['message']='处理成功';
            backJson($ret);
        }else{
            $ret['code']=0;
            $ret['message']='处理失败';
            backJson($ret);
        }
    }
    /*********************************************打印提煤单通过二维码字符串获取信息********************************************************/
    function getBillDetailByewm(){
        $bill=M('bill b')
            ->join('coal_link_yitai y on b.id=y.bill_id')
            ->where(array('y.code_str'=>I('ewm')))
            ->field('b.seller as seller,b.state,b.buyer as buyer,y.dis_time as dis_time,y.bill_number,y.coal_type as coal_type,
            b.truck_id,y.dis_time as pri_time,y.bill_id,b.is_print,b.use_type')
            ->find();

        if(!$bill){
            $ret['code']=0;
            $ret['message']='没有获取到相关信息';
            backJson($ret);
        }
        if($bill['state']!=2){
            $ret['code']=0;
            $ret['message']='提煤单状态异常';
            backJson($ret);
        }
        // 2017年7月6日16:41:40 zgw 小薛提出bug,已经打印过了
        if($bill['use_type'] != 1){
            $ret['code']=0;
            $ret['message']='不是纸质的提煤单';
            backJson($ret);
        }
        if($bill['is_print'] == 1){
            $ret['code']=0;
            $ret['message']='已经打印过了';
            backJson($ret);
        }
        $bill['seller']=M('company')->where(array('id'=>$bill['seller']))->getField('name');
        $bill['buyer']=M('company')->where(array('id'=>$bill['buyer']))->getField('name');
        $bill['lic_number']=M('truck')->where(array('id'=>$bill['truck_id']))->getField('lic_number');
        $bill['owner_order']=M('truck')->where(array('id'=>$bill['truck_id']))->getField('owner_order');
        $bill['dis_time']=date('Y.m.d',strtotime($bill['dis_time'])+3600*6);
        $bill['pri_time']=date('H:i:s',strtotime($bill['pri_time'])+3600*6);
        $ret['code']=1;
        $ret['message']='正确获取';
        $ret['detail']=$bill;
        backJson($ret);
    }
    /*********************************************打印完成后更改是否打印过的状态********************************************************/
    function finishPrint(){
        $bill_id = I('bill_id');
        $print_state = I('is_print');
        $res = M('bill')->find($bill_id);
        if (!$res) {
            $ret['code']=0;
            $ret['message']='没有获取到相关信息';
            backJson($ret);
        }
        if ($res['is_print'] == 1) {
            $ret['code']=0;
            $ret['message']='打印状态异常';
            backJson($ret);
        }
        $sn = I('sn');
        $yitai_data = M('link_yitai')->where(array('code_str' => $sn, 'bill_id' => $bill_id))->find();
        if (!$yitai_data) {
            $ret['code']=0;
            $ret['message']='没有获取到相关信息';
            backJson($ret);
        }

        // 正常做相应处理
        $res1 = M('bill')->save(array('id' => $bill_id, 'is_print' => $print_state));
        if ($res1 !== false) {
            $code = 1;
            $message = '处理成功';

            // 2017年6月4日11:54:39 以后扩展
            // if ($print_state == 1) {
            //     $message = '处理成功';
            // } else {
            //     $message = '处理成功，其他';
            // }

        } else {
            $code = 0;
            $message = '处理失败';
        }
        $ret['code']=$code;
        $ret['message']=$message;
        backJson($ret);
    }





    /***********************************************跟伊泰对接获取空车列表(依照公司来获取)**************************************************/
    function getFreeTruck1(){
        $company_array=explode('_',I('id_str'));
        $truck=array();
        foreach ($company_array as $key=>$value){
            $truck= array_merge($truck,D('Company','Logic')->getListTrucks($value));
        }
        if(count($truck)){
            $ret['code']=1;
            $ret['rows']=$truck;
            die(json_encode($ret));
        }else{
            $ret['code']=0;
            die(json_encode($ret));
        }
    }

    /************************************************跟伊泰对接获取数据库重车状态的订单(依照公司来获取)*****************************************/
    function getDoingBill2(){
        $company_array=explode('_',I('id_str'));

        $bill=M('bill b')
            ->join('coal_link_yitai y on y.bill_id=b.id')
            ->where("state=1 or state=2 or state=3 or state=4 or state=5")
            ->where(array('company'=>array('in',$company_array)))
            ->field('bill_number,truck_id,company as company_id')
            ->select();
        //获取集运站服务器地址
        foreach ($bill as $key=>$value){
            $bill[$key]['jiyun']=M('truck')->where(array('id'=>$value['truck_id']))->getField('jiyun');
            $bill[$key]['owner_order']=M('truck')->where(array('id'=>$value['truck_id']))->getField('owner_order');
            unset($bill[$key]['truck_id']);
        }
        if(count($bill)){
            $ret['code']=1;
            $ret['rows']=$bill;
            echo json_encode($ret);
        }else{
            $ret['code'] = 0;
            $ret['message'] = '没有数据';
            die(json_encode($ret));
        }
    }

    /************************************************跟伊泰对接获取数据库空车状态的订单(依照公司来获取)*****************************************/
    function getDoingBill3(){
        $company_array=explode('_',I('id_str'));
        $bill=M('bill b')
            ->join('coal_link_yitai y on y.bill_id=b.id')
            ->where("state=2")
            ->where(array('company'=>array('in',$company_array)))
            ->field('bill_number,truck_id,company as company_id')
            ->select();
        //获取集运站服务器地址
        foreach ($bill as $key=>$value){
            $bill[$key]['jiyun']=M('truck')->where(array('id'=>$value['truck_id']))->getField('jiyun');
            $bill[$key]['owner_order']=M('truck')->where(array('id'=>$value['truck_id']))->getField('owner_order');
            unset($bill[$key]['truck_id']);
        }
        if(count($bill)){
            $ret['code']=1;
            $ret['rows']=$bill;
            echo json_encode($ret);
        }else{
            $ret['code'] = 0;
            $ret['message'] = '没有数据';
            die(json_encode($ret));
        }
    }

    /********************************************车辆请求订单后,请求状态状态更新*************************************************************/
    function updateTruckStatue(){
        //提交数据监测
        if(!I('lic_number')||!I('state')||!I('sys_name')){
            $ret['code']=0;
            $ret['message']='数据提交不完整';
            backJson($ret);
        }
        //车辆必须是空车状态才能更新
        $truck_state=M('truck')->where(array('lic_number'=>I('lic_number')))->getField('state');
        if($truck_state!=1){
            $ret['code']=0;
            $ret['message']='车辆状态异常';
            backJson($ret);
        }
        $data=I('post.');
        if (!$data['state_code']) {
            $data['state_code'] = 0;
        }
        // 1、更新为不是最新的单
        $res = M('truck_restatue')->where(array('lic_number' => $data['lic_number'], 'sys_name' => $data['sys_name']))->save(array('is_latest' => 0));
        // 2、新增最新的单
        $data['update_time']=date('Y-m-d H:i:s');
        $res1 = M('truck_restatue')->add($data);
        if($res !== false && $res1){
            $ret['code']=1;
            $ret['message']='更新成功';
            backJson($ret);
        } else {
            $ret['code']=0;
            $ret['message']='更新失败';
            backJson($ret);
        }
    }

    /**********************************************获取退单请求队列***********************************************************/
    function getReBills(){
        $company_array=explode('_',I('id_str'));

        $bill=M('bill b')
            ->join('coal_link_yitai y on y.bill_id=b.id')
            ->where("state=1 or state=2 or state=3")
            ->where(array('company'=>array('in',$company_array)))
            ->field('bill_number,truck_id,company as company_id,state')
            ->select();
        //获取集运站服务器地址
        foreach ($bill as $key=>$value){
            $bill[$key]['jiyun']=M('truck')->where(array('id'=>$value['truck_id']))->getField('jiyun');
            $bill[$key]['owner_order']=M('truck')->where(array('id'=>$value['truck_id']))->getField('owner_order');
            unset($bill[$key]['truck_id']);
        }
        if(count($bill)){
            $ret['code']=1;
            $ret['rows']=$bill;
            echo json_encode($ret);
        }else{
            $ret['code'] = 0;
            $ret['message'] = '没有数据';
            die(json_encode($ret));
        }
    }

    /**********************************************伊泰核对数据***************************************************/
    // 待核对的数据列表
    public function checkDataList(){
        $companys = explode('_',I('id_str'));
        $list = M('check_bill')
            ->field('company_id, bill_number, begin_w, end_w, money')
            ->where(array('state' => 0, 'company_id' => array('in', $companys)))
            ->select();
        // 更新5天前的单，作废
        $before_five_day_date = date('Y-m-d H:i:s', time() - 5*24*3600);
        M('check_bill')->where(array('create_time' => array('lt', $before_five_day_date)))->save(array('state' => 2));
        if($list){
            $ret['code'] = 1;
            $ret['rows'] = $list;
        }else{
            $ret['code'] = 0;
            $ret['message'] = '没有数据';
        }
        backJson($ret);
    }
    // 核对有误的修改bill,没有呢？没有就不返回，过期5天，自动结束
    public function checkDataResult(){
        $bill_number = I('bill_number');
        $begin_w = I('begin_w');
        $end_w = I('end_w');
        $money = I('money');
        if (!$bill_number || !$begin_w || !$end_w || !$money) {
            $ret['code']=0;
            $ret['message']='数据提交不完整';
            backJson($ret);
        }
        $cron = M('check_bill')->where(array('bill_number' => $bill_number, 'state' => 0))->find();
        if (!$cron) {
            $ret['code']=0;
            $ret['message']='提煤单号数据异常';
            backJson($ret);
        }
        M()->startTrans();
        // 1、更新check_bill表
        $res1 = M('check_bill')->save(array('id' => $cron['id'], 'state' => 1));
        // 2、更新bill表,2017年7月15日14:53:50 zgw 车辆吨数不准确
        $res2 = M('bill')->save(array(
            'id' => $cron['bill_id'],
            'begin_first_w' => 15,
            'begin_second_w' => $begin_w + 15,
            'begin_w' => $begin_w,
            'end_first_w' => $end_w + 15,
            'end_second_w' => 15,
            'end_w' => $end_w
        ));
        // 3、更新link_yitai表
        $res3 = M('link_yitai')
            ->where(array('bill_number' => $bill_number, 'bill_id' => $cron['bill_id']))
            ->save(array('money' => $money));
        // 4、做变更记录
        $data = array(
            'company_id' => $cron['company_id'],
            'bill_id' => $cron['bill_id'],
            'bill_number' => $cron['bill_number'],
            'begin_w' => $begin_w,
            'end_w' => $end_w,
            'money' => $money,
            'remark' => '',
            'relation_id' => $cron['id'],
            'create_time' => get_time()
        );
        $res5 = M('check_bill_change_log')->add($data);
        if ($res1 !== false && $res2 !== false && $res3 !== false && $res5 !== false) {
            M()->commit();
            $ret['code']=0;
            $ret['message']='修改成功';
            backJson($ret);
        } else {
            M()->rollback();
            $ret['code']=0;
            $ret['message']='修改失败';
            backJson($ret);
        }
    }


    function test(){
        vendor('TopSdk.TopSdk');
        $mes=new \Message();
        $res=$mes->javaLoseSap(I('tel'),I('time'),I('company'));
        dump($res);
    }

    public function map_test(){
        // $trucks = M('truck t')
        //     ->join('left join coal_bill b on b.truck_id = t.id')
        //     ->join('left join coal_bill b on b.truck_id = t.id')
        //     ->where(array('t.user_id' => 302))
        //     ->select();
        $trucks = M('bill b')
            ->field('t.owner_order,b.driver_id')
            ->join('left join coal_truck t on t.id = b.truck_id')
            ->where(array('b.driver_id' => array('gt',0), 't.user_id' => array('in','302,377')))
            ->order('t.owner_order asc')
            ->group('b.truck_id')
            ->select();
        $data = array();
        $i = 0;
        foreach ($trucks as $key => $val) {

            $tmp = M('gps_history')->where(array('uid' => $val['driver_id']))->find();
            if ($tmp) {
                $data[$i]['owner_order'] = $val['owner_order'];
                $data[$i]['x'] = $tmp['x'];
                $data[$i]['y'] = $tmp['y'];
                if ($i > 50) {
                    break;
                }
                $i++;
            }
        }
        backJson($data);
    }
}