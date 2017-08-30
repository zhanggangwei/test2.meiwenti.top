<?php
namespace Api\Controller;
use Think\Controller;
//普通用户,包括运输版和企业版,必须是登陆过的
class UsersController extends ApiController  {
    function _initialize(){
        //查看是否登陆
        if(!session('user')){
            header('Code:1000');
            $res['code']=1000;
            $res['message']='没有登陆';
            backJson($res);
        }
    }
    //贸易商认证筛选是否已经存在公司名字
    function checkCompany(){
        //筛选名字最少是四个字符
        if(mb_strlen(I('lic_name'))<4){
            $ret['code']=0;
            $ret['message']='没有结果';
            backJson($ret);
        }else{
            $name=I('lic_name');
            $company=M('company')->where("name like '%".$name."%' or LOCATE(name,'".$name."' )")->select();
            //查看是否已经有人认证如果有认证则需要去掉
            $array=[];
            $i=0;
            foreach ($company as $value){
                $user=M('users')->where(array('company_id'=>$value['id']))->find();
                if(!$user){
                    $array[$i]['name']=$value['name'];
                    $array[$i]['id']=$value['id'];
                    $i++;
                }
            }
            if(count($array)){
                $ret['code']=1;
                $ret['message']='有预选结果';
                $ret['rows']=$array;
                backJson($ret);
            }else{
                $ret['code']=0;
                $ret['message']='没有结果';
                backJson($ret);
            }
        }

    }
    //贸易商认证(输入认证)
    function authenticationCompany(){
        //验证注册来源
        if(M('users')->where(array('account'=>session('user')))->getField('type')==1){
            $res['code']=0;
            $res['message']='运输版不能进行贸易商认证';
            backJson($res);
        }
        $res=D('Company','Logic')->authentication();
        backJson($res);
    }
    //贸易商认证(已经有公司进行认领)
    function authenticationCompany1(){
        //验证注册来源
        if(M('users')->where(array('account'=>session('user')))->getField('type')==1){
            $res['code']=0;
            $res['message']='运输版不能进行贸易商认证';
            backJson($res);
        }
        $res=D('Company','Logic')->authentication1();
        backJson($res);
    }
    //司机认证
    function authenticationDriver(){
        //验证注册来源
        if(M('users')->where(array('account'=>session('user')))->getField('type')==2){
            $res['code']=0;
            $res['message']='企业版不能进行司机认证';
            backJson($res);
        }
        $res=D('Driver','Logic')->authentication();
        backJson($res);
    }
    //车辆认证
    function authenticationTruck(){
        //验证注册来源
        if(M('users')->where(array('account'=>session('user')))->getField('type')==2){
            $res['code']=0;
            $res['message']='企业版不能进行车辆认证';
            backJson($res);
        }
        $res=D('Truck','Logic')->authentication();
        backJson($res);
    }
    /*******************************************发布招聘***************************************************/
    function addRecruit()
    {
        //验证输入内容是否完整
        if(!I('title')||!I('number')||!I('salary')||!I('education')||!I('worked')||!I('address')){
            $ret['code']=3;
            $ret['message']='提交信息不完整';
            die(json_encode($ret));
        }
        $data=I('post.');
        $data['lic_level']=I('lic_level')?I('lic_level'):'没有要求';
        $data['comment']=I('comment')?I('comment'):'无';
        $data['addate']=date('Y-m-d');
        $data['editor_id']=session('user_id');
        //写进数据库
        $res=M('recruit')->add($data);
        if($res){
            $ret['code']=1;
            $ret['message']='发布成功';
            die(json_encode($ret));
        }else{
            $ret['code']=4;
            $ret['message']='发布失败';
            die(json_encode($ret));
        }
    }
    /*******************************************招聘详细*****************************************************/
    function recruitDetail(){
        if(!I('id')){
            $ret['code']=0;
            $ret['message']='信息提交有误';
            die(json_encode($ret));
        }
        $recruit=M('recruit')->where(array('id'=>I('id'),'is_passed'=>1))->find();
        if($recruit){
            $acc=M('users')->where(array('id'=>$recruit['editor_id']))->field('type,account,photo_lit,phone')->find();

            $recruit['tel']=$acc['phone'];
            //图片处理
            switch ($acc['type']) {
                case 0;
                    $name = M('buyer')->where(array('aid' => $recruit['editor_id']))->field('name')->find();
                    break;
                case 1;
                    $name = M('seller')->where(array('aid' => $recruit['editor_id']))->field('name')->find();
                    break;
                case 2;
                    $name = M('company')->where(array('aid' => $recruit['editor_id']))->field('name')->find();
                    break;
                case 3;
                    $name = M('trucker')->where(array('aid' => $recruit['editor_id']))->field('name')->find();
                    break;
                default:
                    $ret['code'] = 0;
                    $ret['message'] = '获取失败';
                    die(json_encode($ret));
            }
            $recruit['name']=$name['name'];
            if(!$recruit){
                $ret['code']=2;
                $ret['message']='没有获取到数据';
                die(json_encode($ret));
            }else{
                $ret['code']=1;
                $ret['message']='获取成功';
                $ret['rows']=$recruit;
                die(json_encode($ret));
            }
        }else{
            $ret['code']=2;
            $ret['message']='没有获取到数据';
            die(json_encode($ret));
        }

    }
    /*******************************************发布求职*****************************************************/
    function addApply(){
        //发布不做限制
        //验证必要的信息是否为空
        if(!I('name')||!I('sex')||!I('age')||!I('wordked')||!I('title')||!I('salary')||!I('education')||!I('lic_level')||!I('address')||!I('home')){
            $ret['code']=0;
            $ret['message']='信息提交不完整';
            die(json_encode($ret));
        }
        //接受数据
        $data=I('post.');
        $data['addate']=date('Y-m-d');
        $data['editor_id']=session('user_id');
        $res=M('apply')->add($data);
        if($res){
            $ret['code']=1;
            $ret['message']='发布成功';
            die(json_encode($ret));
        }else{
            $ret['code']=2;
            $ret['message']='发布失败';
            die(json_encode($ret));
        }
    }
    /*******************************************求职详情*****************************************************/
    function applyDetail(){
        if(!I('id')){
            $ret['code']=0;
            $ret['message']='信息提交有误';
            die(json_encode($ret));
        }
        $apply=M('apply')->where(array('id'=>I('id'),'is_passed'=>1))->find();
        if(!$apply){
            $ret['code']=2;
            $ret['message']='没有获取到数据';
            die(json_encode($ret));
        }else{
            $ret['code']=1;
            $ret['message']='获取成功';
            $ret['rows']=$apply;
            die(json_encode($ret));
        }
    }
    /*******************************************发布火运信息*************************************************/
    function addTrain(){
        //查看提交的信息是否完整
        if(!I('tel')||!I('from_w')||!I('to_w')||!I('empty_time')||!I('stroke')){
            $ret['code']=0;
            $ret['message']='信息不完整';
            die(json_encode($ret));
        }
        vendor("InputVerify.InputVerify");
        $input_verify=new \InputVerify();
        if(!$input_verify->valid_mobile(I('tel'))){
            $ret['code']=0;
            $ret['message']='手机号格式不正确';
            die(json_encode($ret));
        }
//        if(!$input_verify->isDate(I('empty_time'))){
//            $ret['code']=0;
//            $ret['message']='日期格式不正确';
//            die(json_encode($ret));
//        }
        //检查是否是已经登陆的账号在发布信息
        $data=I('post.');
        if(session('aid')){
            $data['editor_id']=session('aid');
        }else{
            $data['editor_id']=0;
        }

        $data['addate']=date("Y-m-d");//添加日期
        $res=M('train')->add($data);
        if($res){
            $ret['code']=1;
            $ret['message']='发布成功';
            echo json_encode($ret);
        }else{
            $ret['code']=2;
            $ret['message']="发布失败";
            echo json_encode($ret);
        }
    }
    /*****************************************火运列表******************************************************/
    function listTrain(){
        $pagesize=10;
        $page=I('page')?I('page'):1;
        //获取搜索条件
        $search_arr=array();
        if(I('from_w')){
            $search_arr['from_w']=I('from_w');
        }
        if(I('to_w')){
            $search_arr['to_w']=I('to_w');
        }
        if(I('empty_time')){
            $search_arr['empty_time']=I('empty_time');
        }
        if(I('stroke')){
            $search_arr['stroke']=I('stroke');
        }

        $limit=$pagesize*($page-1).",".$pagesize;
        $train=M('train')->where($search_arr)->where(array('is_passed'=>1))->limit($limit)->order("addate desc,id")->select();
        if(count($train)){
            //处理行程和图片
            foreach ($train as $key=>$value){
                if(!$value['picture']){
                    $train[$key]['picture']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].C('OUT_LOAD_DIR')."../images/train_photo.png";
                }else{
                    $train[$key]['picture']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].C('OUT_LOAD_DIR').$value['picture'];
                }
            }
            $records=M('train')->where(array('is_passed'=>1))->select();
            $ret['pagenum']=$page;
            $ret['rows']=$train;
            $ret['records']=count($records);
            $ret['total_page']=ceil($ret['records']/$pagesize);
            $ret['code']=1;
            $ret['message']='获取成功';
            echo json_encode($ret);
        }else{
            $ret['code']=2;
            $ret['message']='没有数据';
            die(json_encode($ret));
        }
    }
    /************************************************发布船运信息*****************************************/
    function addShip(){
        //查看提交的信息是否完整
        if(!I('tel')||!I('name')||!I('slong')||!I('width')||!I('load_w')||!I('empty_time')||!I('port')||!I('build_year')){
            $ret['code']=0;
            $ret['message']='信息不完整';
            die(json_encode($ret));
        }
        vendor("InputVerify.InputVerify");
        $input_verify=new \InputVerify();
        if(!$input_verify->valid_mobile(I('tel'))){
            $ret['code']=0;
            $ret['message']='手机号格式不正确';
            die(json_encode($ret));
        }
//        if(!$input_verify->isDate(I('empty_time'))){
//            $ret['code']=0;
//            $ret['message']='日期格式不正确';
//            die(json_encode($ret));
//        }
        if(!$input_verify->isNumber(I('build_year'))){
            $ret['code']=0;
            $ret['message']='造船年份有误';
            die(json_encode($ret));
        }
        //检查是否是已经登陆的账号在发布信息
        $data=I('post.');
        if(session('aid')){
            $data['editor_id']=session('aid');
        }else{
            $data['editor_id']=0;
        }

        $data['addate']=date("Y-m-d");//添加日期
        $res=M('ship')->add($data);
        if($res){
            $ret['code']=1;
            $ret['message']='发布成功';
            echo json_encode($ret);
        }else{
            $ret['code']=2;
            $ret['message']="发布失败";
            echo json_encode($ret);
        }
    }
    /************************************************船运信息列表*****************************************/
    function listShip(){
        $pagesize=10;
        $page=I('page')?I('page'):1;
        //获取搜索条件
        $search_arr=array();
        if(I('port')){
            $search_arr['port']=I('port');
        }
        if(I('load_w')){
            $search_arr['load_w']=I('load_w');
        }
        if(I('empty_time')){
            $search_arr['empty_time']=I('empty_time');
        }
        //船龄处理
        //默认是1-10年

        if(I('min_age')){
            $date=date('Y');
            $max_age=$date-I('min_age');
        }else{
            $date=date('Y');
            $max_age=$date;
        }
        if(I('max_age')){
            $date=date('Y');
            $min_age=$date-I('max_age');
        }else{
            $date=date('Y');
            $min_age=$date-10;
        }
        $seach_year="build_year <=$max_age and build_year>=$min_age";
        $limit=$pagesize*($page-1).",".$pagesize;
        $ship=M('ship')->where($search_arr)->where($seach_year)->where(array('is_passed'=>1))->order("addate desc,id")->limit($limit)->select();
        //echo M()->getLastSql();
        if(count($ship)){
            //处理行程和图片
            foreach ($ship as $key=>$value){
//                if(!$value['picture']){
                $ship[$key]['picture']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].C('OUT_LOAD_DIR')."../images/ship_photo.png";
//                }else{
//                    $ship[$key]['picture']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].C('OUT_LOAD_DIR').$value['picture'];
//                }
            }
            $records=M('ship')->where(array('is_passed'=>1))->select();
            $ret['pagenum']=$page;
            $ret['rows']=$ship;
            $ret['records']=count($records);
            $ret['total_page']=ceil($ret['records']/$pagesize);
            $ret['code']=1;
            $ret['message']='获取成功';
            echo json_encode($ret);
        }else{
            $ret['code']=2;
            $ret['message']='没有数据';
            die(json_encode($ret));
        }
    }
    /************************************************发布长途信息*****************************************/
    function addLongHaul(){
        //验证输入是否全面
        if(!I('begin_date')||!I('end_date')||!I('from_address')||!I('to_address')||!I('product')||!I('weight')||!I('transport')||!I('name')||!I('tel')){
            $ret['code']=0;
            $ret['message']='信息提交不完整';
            die(json_encode($ret));
        }
//        //验证日期格式
        vendor("InputVerify.InputVerify");
        $input_verify=new \InputVerify();
//        if(!$input_verify->isDate(I('begin_date'))){
//            $ret['code']=0;
//            $ret['massege']='日期格式不正确';
//            die(json_encode($ret));
//        }
//
//        if(!$input_verify->isDate(I('begin_date'))){
//            $ret['code']=0;
//            $ret['massege']='日期格式不正确';
//            die(json_encode($ret));
//        }
        //验证手机格式
        if(!$input_verify->valid_mobile(I('tel'))){
            $ret['code']=0;
            $ret['message']='手机号不正确';
            die(json_encode($ret));
        }
        //检查是否是已经登陆的账号在发布信息
        $data=I('post.');
        if(session('aid')){
            $data['editor_id']=session('aid');
        }else{
            $data['editor_id']=0;
        }
        //地区处理
        $from_arr=explode('-',I('from_address'));
        $data['from_p']=$from_arr[0];
        $data['from_s']=$from_arr[1];

        $to_arr=explode('-',I('to_address'));
        $data['to_p']=$to_arr[0];
        $data['to_s']=$to_arr[1];

        $data['addate']=date("Y-m-d");//添加日期
        $res=M('long_haul')->add($data);
        if($res){
            $ret['code']=1;
            $ret['message']='发布成功';
            echo json_encode($ret);
        }else{
            $ret['code']=2;
            $ret['message']="发布失败";
            echo json_encode($ret);
        }

    }
    /************************************************长途信息列表*****************************************/
    function listLongHaul(){
        $pagesize=10;
        $page=I('page')?I('page'):1;
        //获取搜索条件
        $search_arr=array();

        $from_arr=explode('-',I('from_address'));
        $from_p=$from_arr[0];
        $from_s=$from_arr[1];

        if($from_p){
            $search_arr['from_p']=$from_p;
        }
        if($from_s){
            $search_arr['from_s']=$from_s;
        }

        $to_arr=explode('-',I('to_address'));
        $to_p=$to_arr[0];
        $to_s=$to_arr[1];
        if($to_p){
            $search_arr['to_p']=$to_p;
        }
        if(I('to_s')){
            $search_arr['to_s']=$to_s;
        }

        if(I('begin_date')){
            $begin_date['begin_date']  = array('EGT',I('begin_date'));
        }else{
            $begin_date['begin_date']  = array('EGT',"1977-01-01");
        }

        if(I('end_date')){
            $end_date['end_date'] =array('ELT',I('end_date'));
        }else{
            $end_date['end_date']  = array('ELT',"2050-01-01");
        }

        if(I('min_weight')){
            $min_weight=I('min_weight');
        }else{
            $min_weight=0;
        }

        if(I('max_weight')){
            $max_weight=I('max_weight');
        }else{
            $max_weight=1000000000;
        }
        $weight_str="weight<=$max_weight and weight>=$min_weight";
        $limit=$pagesize*($page-1).",".$pagesize;
        $long_haul=M('long_haul')->where($search_arr)->where($begin_date)->where($weight_str)->where(array('is_passed'=>1))->order("addate desc,id")->limit($limit)->select();
        if(count($long_haul)){
            //处理行程和图片
            foreach ($long_haul as $key=>$value){
                if(!$value['picture']){
                    $long_haul[$key]['picture']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].C('OUT_LOAD_DIR')."../images/longhaul_photo.png";
                }else{
                    $long_haul[$key]['picture']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].C('OUT_LOAD_DIR').$value['picture'];
                }
            }
            $records=M('long_haul')->where(array('is_passed'=>1))->select();
            $ret['pagenum']=$page;
            $ret['rows']=$long_haul;
            $ret['records']=count($records);
            $ret['total_page']=ceil($ret['records']/$pagesize);
            $ret['code']=1;
            $ret['message']='获取成功';
            echo json_encode($ret);
        }else{
            $ret['code']=2;
            $ret['message']='没有数据';
            die(json_encode($ret));
        }
    }
    /******************************************修改密码第一步*****************************************/
    function rePassword1(){
        //查看提交的手机号是否存在
        $acc=M('account')->where(array('account'=>I('tel')))->find();
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
        if($mes->regSendCode(I('tel'),$ver_num)){
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
    /************************************************修改密码第二步*****************************************/
    function rePassword2(){
        //查看提交的手机号是否存在
        $acc=M('account')->where(array('account'=>I('tel')))->find();
        if(!$acc){
            $ret['code']=0;
            $ret['message']='手机号输入有误';
            die(json_encode($ret));
        }
        //查看短信验证码是否正确
        if(session('ver_num')!=I('ver_num')){
            $ret['code']=0;
            $ret['message']='手机验证码错误';
            die(json_encode($ret));
        }
        //查看新密码是否为空
        if(!I('new1')||!I('new2')){
            $ret['code']=0;
            $ret['message']='新密码不能为空';
            die(json_encode($ret));
        }
        //查看两个密码是否一样
        if(I('new1')!=I('new2')){
            $ret['code']=0;
            $ret['message']='两次密码输入不一样';
            die(json_encode($ret));
        }
        //修改密码
        $data['password']=authcode(I('new1'),C('CODE_KEY'));
        $res=M('account')->where(array('account'=>I('tel')))->save($data);
        if($res!==false){
            $ret['code']=1;
            $ret['message']='密码修改成功';
            die(json_encode($ret));
        }else{
            $ret['code']=0;
            $ret['message']='修改失败，请稍后再试';
            die(json_encode($ret));
        }
    }
    /*************************************************短途抢单列表**************************************/
    function listShortHaul(){
        $short_haul=M('logistics')->where(array('type'=>1,'state'=>1))->order("arr_time desc,id")->select();
        if(count($short_haul)){
            foreach ($short_haul as $key =>$value){
                //卖方
                $order=M('orders')->where(array('order_id'=>$value['order_id']))->field('seller_id,buyer_id,log_id,coal_type')->find();
                $seller_acc=M('account')->where(array('account'=>$order['seller_id']))->field('id,photo_lit')->find();
                $seller=M('seller')->where(array('aid'=>$seller_acc['id']))->field('name')->find();
                $short_haul[$key]['seller']=$seller['name'];
                //买方
                $buyer_acc=M('account')->field('id,photo_lit')->where(array('account'=>$order['buyer_id']))->find();
                $buyer=M('buyer')->where(array('aid'=>$buyer_acc['id']))->field('name')->find();
                $short_haul[$key]['buyer']=$buyer['name'];
                //煤种
                $short_haul[$key]['coal_type']=$order['coal_type'];
                //查看是卖家还是买家负责物流
                if($order['seller_id']==$order['log_id']){
                    if(!$seller['photo_lic']){
                        $short_haul[$key]['photo_lit']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].C('OUT_LOAD_DIR').'../images/seller_photo.png';
                    }else{
                        $short_haul[$key]['photo_lit']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].C('OUT_LOAD_DIR').$seller['photo_lic'];
                    }
                }else{
                    if(!$buyer['photo_lic']){
                        $short_haul[$key]['photo_lit']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].C('OUT_LOAD_DIR')."../images/buyer_photo.png";
                    }else{
                        $short_haul[$key]['photo_lit']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].C('OUT_LOAD_DIR').$buyer['photo_lic'];
                    }
                }
                unset($short_haul[$key]['order_id']);
                unset($short_haul[$key]['type']);
                unset($short_haul[$key]['company']);
                unset($short_haul[$key]['assign_id']);
                unset($short_haul[$key]['arr_time']);
                unset($short_haul[$key]['price']);
                unset($short_haul[$key]['end_time']);
                unset($short_haul[$key]['state']);
            }
            $ret['code']=1;
            $ret['message']='获取成功';
            $ret['rows']=$short_haul;
            die(json_encode($ret));
        }else{

            $ret['code']=0;
            $ret['message']='没有数据';
            die(json_encode($ret));
        }

    }
    /*************************************************融资页面**************************************/
    function addFinance(){
        if(!I('business')||!I('company')||!I('address')||!I('num')||!I('name')||!I('name')){
            $this->error('必要字段不能为空');
        }
        //验证码判断
        if(!check_verify(I('imagecode'))){
            $this->error('验证码输入错误');
        }
        $data=I('post.');
        $data['creat_time']=date("Y-m-d H:i:s");
        $res=M('finance')->add($data);
        if($res){
            $this->success('发布成功');
        }else{
            $this->error('发布失败，请稍后再试');
        }
    }
    /*************************************************首页金融和新闻**************************************/
    function indexNews(){
        //首页展示的新闻
        $index_new=M('news')->order('click desc')->field('title,id')->find();
        $index_new['url']=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].U('News/showNew',array('id'=>$index_new['id']));
        $finance=C('HTTP_TYPE').$_SERVER['SERVER_NAME'].U('finance');
        $ret['index_new']=$index_new;
        $ret['finance']=$finance;
        $ret['code']=1;
        $ret['message']='返回成功';
        echo json_encode($ret);
    }
    /*************************************************生成自身信息二维码**************************************/
    function makeEwm(){
        $array['id']=session('user_id');
        $array['name']=M('users')->where(array('id'=>session('user_id')))->getField('real_name');
        $data=json_encode($array);
        //加密
        $key=rand_num(4,"ALL");
        vendor('Phpqrcode.Phpqrcode');
        $value = $key.encrypt($data,$key); //二维码内容
        $errorCorrectionLevel = 'L';//容错级别
        $matrixPointSize = 10;//生成图片大小
        //生成二维码图片
        \QRcode::png($value, false, $errorCorrectionLevel, $matrixPointSize, 1,false);

    }
    /****************************************************提货点进矿扫描*********************************************/
    function beginFirstRead(){
        if(!I('code')||!I('account')||!I('pwd')){
            $ret['code']='0';
            $ret['message']='传递信息不完整';
            die(json_encode($ret));
        }
        //过磅账号密码测试
        $res=D('Users','Logic')->login(I('account'),I('pwd'));
        if(!$res['code'])
        {
            backJson($res);
        }
        //获取公司ID
        $users=M('users')->where(array('id'=>session('user_id')))->find();
        D('Bill','Logic')->beginFirstRead($users['company_id'],I('bill_id'));
    }
    /***********************************************运输版我的***********************************************/
    function index(){
        //公共
        {
            $ret['is_authentication']=M('users')->where(array('id'=>session('user_id')))->getField('is_authentication');
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

            //查看是否认证了车辆并且通过审核
            $truck=M('truck')->where(array('owner_id'=>session('user_id'),'owner_type'=>1))->find();
            if($truck){
                $ret['truck']=1;
            }else{
                $ret['truck']=0;
            }



            //常规信息
            $user=M('users')->where(array('id'=>session('user_id')))->find();
            $ret['name']=$user['real_name'];
            $ret['photo']=$user['photo']==40?C('OSS_WEB_URL').'public/logo.png':C('OSS_WEB_URL').$user['photo'];
        }

        //车主
        if($ret['truck']==1)
        {
            //请求合作的物流公司
            $company=M('company_trucker')->where(array('trucker_id'=>session('user_id'),'status'=>1))->select();
            $ret['company']=count($company);

            //拥有车辆
            $trucks=M('truck')->where(array('owner_id'=>session('user_id'),'owner_type'=>1,'is_passed'=>1))->select();
            $ret['truck_total']=count($trucks);

            $ret['truck_doing']=$trucks=M('truck')->where(array('owner_id'=>session('user_id'),'owner_type'=>1,'is_passed'=>1))->where("state = 3 or state=2")->count();//在途车辆

            $ret['truck_idle']=$trucks=M('truck')->where(array('owner_id'=>session('user_id'),'owner_type'=>1,'is_passed'=>1,'state'=>1))->count();//闲置车辆

            //提煤单数据
            $history_bill=D('Bill','Logic')->history(session('user_id'),null,null,null,null,null,null,null,null,null);
            $ret['total_times']=count($history_bill);//总车次
            $ret['total_w']=0;
            foreach ($history_bill as $key=>$value){
                $ret['total_w']=$ret['total_w']+$value['end_w'];
            }
            //请求合作的司机
            $ret['bind_driver']=M('driver_trucker')->where(array('trucker_id'=>session('user_id'),'status'=>1))->count();
            //查看通知信息
            //查看跟那些公司合作
            $companys=M('company_trucker')->where(array('trucker_id'=>session('user_id'),'status'=>1))->field('company')->select();
            $ret['apply_company']=M('company_trucker')->where(array('trucker_id'=>session('user_id'),'status'=>2))->count();
            $notice='';
            foreach ($companys as $value){
                $company_data=M('company')->where(array('id'=>$value['company']))->field('id,name')->find();
                if($company_data){
                    $text1=M('company_notice')->where(array('company_id'=>$company_data['id']))->order('add_time desc,id desc')->getfield('notice');
                    if($text1){
                        $notice=$notice.$company_data['name'].':'.$text1;
                    }
                }

            }

        }

        //司机
        if($ret['driver']==1)
        {



            $ret['is_work']=$driver['is_work'];
            //司机排队情况
            $use_truck=M('truck')->where(array('id'=>$driver['truck_id']))->find();
            $truck_company_id=$use_truck['user_id'];
            $company_trucks=M('truck')
                ->where(array('user_id'=>$truck_company_id,'state'=>1,'is_comperation'=>1))
                ->field('user_id,state,last_time,id,lic_number')
                ->select();
            $truck_data = array();
            $i = 0;
            foreach ($company_trucks as $key => $value) {
                $is_dispatch = D('Truck', 'Logic')->isDispatch($value['id'],$truck_company_id);
                if ($is_dispatch['code'] == 1) {
                    $truck_data[$i] = $value;
                    $i++;
                }
            }
            $ret['arr_truck']=0;
            if($use_truck['last_time']){
                foreach ($truck_data as $key => $value){
                    if(!$value['last_time']){
                        $ret['arr_truck']=$ret['arr_truck']+1;
                    }else if($value['last_time'] and $value['last_time']<$use_truck['last_time']){
                        $ret['arr_truck']=$ret['arr_truck']+1;
                    }
                }
            }else{
                foreach ($truck_data as $key => $value){
                    if(!$value['last_time']&&$value['id']<$use_truck['id']){
                        $ret['arr_truck']=$ret['arr_truck']+1;
                    }
                }
            }
            //查看司机是否是工作中
            $is_work=D('Driver','Logic')->is_work($driver['uid']);
            $ret['arr_truck']=$is_work?$ret['arr_truck']:'休';

            //待确认的提煤单
            if($is_work){
                $ret['doing_bill']=M('truck')->where(array('id'=>$driver['truck_id'],'state'=>2))->count();//正在需要确认的提煤单
            }else{
                $ret['doing_bill']=0;
            }

            //司机绑定车辆情况:已经向车主发起申请,已经有绑定,还没有进行绑定
            $driver=M('driver')->where(array('uid'=>session('user_id')))->find();
            $ret['bind_truck']=M('truck')->where(array('id'=>$driver['truck_id']))->getField('lic_number');
            if(!$ret['bind_truck']){
                //查看是否有发起过绑定申请,车主还没有通过
                $shenqing=M('driver_trucker')->where(array('driver_id'=>session('user_id'),'status'=>1))->find();
                if($shenqing){
                    $ret['bind_truck']=2;
                }else{
                    $ret['bind_truck']=0;
                }
            }
            //查看通知信息
            //查看司机在哪个公司里面
            $user_company=M('company')->where(array('id'=>$use_truck['user_id']))->getField('name');
            $notice='';
            if($user_company){
                $text=M('company_notice')->where(array('company_id'=>$use_truck['user_id']))->order('add_time desc,id desc')->getfield('notice');
                $notice=$user_company.":".$text;
            }

            // 2017年7月20日17:24:45 zgw 增加手势密码超限后是否锁定的标记
            $ret['gesture_lock']=$user['gesture_lock'];
        }
        //查看登录账号
        $ret['account']=M('users')->where(array('id'=>session('user_id')))->getField('account');
        $ret['notice']=$notice;
        $ret['code']=1;
        $ret['message']='获取成功';
        backJson($ret);
    }
    /***********************************************更换头像***********************************************/
    function changePhoto(){
        if(!I('photo')){
            $ret['code']=0;
            $ret['message']='参数有误';
            backJson($ret);
        }
        $data['photo']=I('photo');
        $result=M('users')->where(array('id'=>session('user_id')))->save($data);
        if($result!==false){
            $ret['code']=1;
            $ret['message']='修改成功';
            $ret['photo']=C('OSS_WEB_URL').I('photo');
            backJson($ret);
        }else{
            $ret['code']=0;
            $ret['message']='修改失败';
            backJson($ret);
        }
    }
    /***********************************************修改密码***********************************************/
    function resetPws(){
        $old_pws=authcode(I('old'),C('CODE_KEY'));
        if(M('users')->where(array('id'=>session('user_id')))->getField('password')!=$old_pws){
            $ret['code']=0;
            $ret['message']='旧密码输入有误';
            backJson($ret);
        }
        //查看新密码是否为空
        if(!I('new1')||!I('new2')){
            $ret['code']=0;
            $ret['message']='新密码不能为空';
            backJson($ret);
        }
        //查看两个密码是否一样
        if(I('new1')!==I('new2')){
            $ret['code']=0;
            $ret['message']='两次密码输入不一样';
            backJson($ret);
        }
        //修改密码
        $data['password']=authcode(I('new1'),C('CODE_KEY'));
        $res=M('users')->where(array('id'=>session('user_id')))->save($data);
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
    /****************************************************扫描获取二维码内容(用于绑定操作)*********************************************/
    function readCode(){
        if(!I('code')){
            $ret['code']=0;
            $ret['message']='二维码信息有误';
            die(json_encode($ret));
        }
        //截取密钥
        $key=substr(I('code'),0,4);
        $data=substr(I('code'),4);
        $code=object_array(json_decode(decrypt($data,$key)));
        if($code['id']){
            $ret['detail']=$code;
            $ret['code']=1;
            $ret['messag']='正确获取信息';
        }else{
            $ret['code']=0;
            $ret['messag']='获取信息失败';
        }
        backJson($ret);
    }
    /****************************************************获取煤种*********************************************/
    function scan_code_add_buddy(){
        if(!I('code')){
            $ret['code']=0;
            $ret['message']='二维码信息有误';
            die(json_encode($ret));
        }

        $user_code=I('code');
        $uid=$_SESSION['user_id'];

        //截取密钥
        $key=substr($user_code,0,4);
        $data=substr($user_code,4);
        $code=object_array(json_decode(decrypt($data,$key)));
        if($code['id']){
            $friend_id= $code['id'];
            vendor('Emchat.Easemobclass');
            $h=new \Easemob();
            $h->addFriend($uid,$friend_id);
            $ret['code']=1;
            $ret['messag']='添加好友成功';
        }else{
            $ret['code']=0;
            $ret['messag']='添加好友失败';
        }
        backJson($ret);

    }
    function getCoalType(){
        $coal=M('coal_type')->select();
        $ret['detail']=[];
        foreach ($coal as $key=>$value){
            $ret['detail'][$key]['name']=$value['name'];
            $ret['detail'][$key]['id']=$value['id'];
        }

        $ret['code']=1;
        $ret['message']='获取成功';
        backJson($ret);
    }
    /********************************************物流公司派单********************************************/
    function testGetResquantity(){
        $company_id=M('users')->where(array('id'=>session('user_id')))->getField('company_id');
        D('Company','Logic')->logisticsList($company_id);
    }
    function testGetTrucks(){
        $company_id=M('users')->where(array('id'=>session('user_id')))->getField('company_id');
        D('Company','Logic')->getUseTrukList($company_id);
    }
    function arrBill(){
        $user=M('users')->where(array('id'=>session('user_id')))->find();
        $logistics_id=I('logistics_id');
        $truck_id=I('truck_id');
        D('Bill','Logic')->arr($logistics_id,$truck_id,$user['company_id'],40);
    }
    /******************************贸易商版本我的页面*************************/
    function companyIndex()
    {
        //常规信息
        //名称
        //头像
        //拥有车辆
        $user=M('users')->where(array('id'=>session('user_id')))->find();
        $company=M('company')->where(array('id'=>$user['company_id']))->find();
        if($company['is_passed']===null){
            header('Code:2000');
            $ret['is_authentication']=0;
        }else if($company['is_passed']==0){
            header('Code:2100');
            $ret['is_authentication']=2;
        }else{
            $ret['is_authentication']=1;
        }
        $ret['name']=$company['name']?$company['name']."(".$user['real_name'].")":'还没进行认证';
        $ret['photo']=$user['photo']==40?getTrueImgSrc('public/logo.png'):getTrueImgSrc($user['photo']);
        if($company){
            $ret['trucks']=M('truck')->where(array('user_id'=>$company['id'],'is_comperation'=>1))->count();
        }else{
            $ret['trucks']=0;
        }
        $ret['code']=1;
        $ret['message']='获取成功';
        $ret['account']=$user['account'];
        $ret['is_vip']=$company['is_vip'];
        backJson($ret);
    }
    /******************************货源大厅***************************************/
    function listLogisticsGoods(){
        $pagesize=10;
        $page=I('page')?I('page'):1;
        $limit=$pagesize*($page-1).",".$pagesize;
        $where=array('l.is_passed'=>1);
        $create_time=I('create_time')?I('create_time'):date('Y-m-01 H:i:s');
        if(I('create_time')){
            // $where2="l.create_time >= '".I('create_time')."'";
            $where2['l.create_time'] = array('between', array(I('create_time').' 0:0:0',I('create_time').' 23:59:59'));
        }else{
            $where2="l.create_time > '".$create_time."'";
        }
        if(I('from_add')){
            $from_add=explode('-',I('from_add'));
            if($from_add[0]){
                $where=array_merge($where,array('from_province'=>$from_add[0]));
            }
            if($from_add[1]){
                $where=array_merge($where,array('from_city'=>$from_add[1]));
            }
            if($from_add[2]){
                $where=array_merge($where,array('from_area'=>$from_add[2]));
            }
        }
        if(I('to_add')){
            $to_add=explode('-',I('to_add'));
            if($to_add[0]){
                $where=array_merge($where,array('to_province'=>$to_add[0]));
            }
            if($to_add[1]){
                $where=array_merge($where,array('to_city'=>$to_add[1]));
            }
            if($to_add[2]){
                $where=array_merge($where,array('to_area'=>$to_add[2]));
            }
        }
        $logisticsgoods=M('logistics_goods l')
            ->join("coal_company c on l.writer_id=c.id")
            ->where($where)
            ->where($where2)
            ->order('create_time desc,id desc')
            ->limit($limit)
            ->field('l.from_city,l.to_city,l.coal_type,l.quantity,l.comment,c.name,c.id as compnay_id,l.create_time,c.is_vip,l.id as id')
            ->select();
        // sql();
        $total_logisticsgoods=M('logistics_goods l')
            ->join("coal_company c on l.writer_id=c.id")
            ->where(array('l.is_passed'=>0))
            ->field('l.from_city,l.to_city,l.coal_type,l.quantity,l.comment,c.name,c.id,l.create_time')
            ->select();
        foreach ($logisticsgoods as $key=>$value){
            $logisticsgoods[$key]['phone']=M('users')->where(array('company_id'=>$value['compnay_id'],'is_admin'=>1))->getField('phone');
            $logisticsgoods[$key]['create_time']=getday($value['create_time']);
            $logisticsgoods[$key]['coal_type']=coal_type($value['coal_type']);
            unset($logisticsgoods[$key]['compnay_id']);
        }
        if(count($logisticsgoods)){
            $ret['rows']=$logisticsgoods;
            $ret['code']=1;
            $ret['message']='获取成功';
            $ret['pagenum']=$page;
            $ret['rows']=$logisticsgoods;
            $ret['records']=count($total_logisticsgoods);
            $ret['total_page']=ceil($ret['records']/$pagesize);
            backJson($ret);
        }else{
            $ret['code']=1;
            $ret['rows']=array();
            $ret['message']='没有内容';
            backJson($ret);
        }
    }
    /******************************货源大厅详情***************************************/
    function detailLogisticsGoods(){
        if(!I('id')){
            $ret['code']=0;
            $ret['message']='传递参数有误';
            backJson($ret);
        }
        $own=M('users')->where(array('id'=>session('user_id')))->find();
        if(!$own['is_authentication']){
            header('Code:2000');
            $ret['code']=0;
            $ret['message']='您还没有进行认证';
            backJson($ret);
        }
        $id=I('id');
        $logisticsgoods=M('logistics_goods l')
            ->join("coal_company c on l.writer_id=c.id")
            ->where(array('l.id'=>$id))
            ->field('l.from_city,l.to_city,l.coal_type,l.quantity,l.comment,c.name,l.writer_id as company_id,l.create_time,l.id as id,l.click')
            ->find();
        $user=M('users')->where(array('company_id'=>$logisticsgoods['company_id'],'is_admin'=>1))->find();
        $logisticsgoods['coal_type']=coal_type($logisticsgoods['coal_type']);
        $logisticsgoods['create_time']=getday($logisticsgoods['create_time']);
        $logisticsgoods['user_name']=$user['real_name'];
        $logisticsgoods['user_time']=getyear($user['create_time']);
        $logisticsgoods['user_photo']=$user['photo']==40?getTrueImgSrc('public/logo.png'):getTrueImgSrc($user['photo']);
        $logisticsgoods['user_records']=M('logistics_goods')->where(array('writer_id'=>$user['company_id']))->count();
        $logisticsgoods['user_phone']=$user['phone'];
        unset($logisticsgoods['company_id']);
        //增加点击量
        $data['click']=$logisticsgoods['click']+1;
        M('logistics_goods')->where(array('id'=>I('id')))->save($data);
        if($logisticsgoods){
            $ret['detail']=$logisticsgoods;
            $ret['code']=1;
            $ret['message']='获取成功';
            backJson($ret);
        }else{
            $ret['code']=0;
            $ret['message']='没有内容';
            backJson($ret);
        }
    }

    /*手势密码操作*/
    /**
     * 获取手势密码
     */
    public function getGestureCipher(){
        // 不能只是司机端的,以后扩展到任意端支付相关的
        $res = M('users')->find(session('user_id'));
        if ($res) {
            $ret['code'] = 1;
            $ret['message'] = '获取成功';
            $ret['isset'] = ($res['gesture_cipher']?1:0);
            $ret['gesture'] = mwt_base64_encode($res['gesture_cipher']);
            backJson($ret);
        } else {
            $ret['code'] = 0;
            $ret['message'] = '数据非法';
            backJson($ret);
        }
    }
    /**
     * 设置手势密码
     * $gesture 手势密码
     * $type 1设置，2修改，3忘记
     * return code\message
     */
    public function setGestureCipher(){
        $gesture = trim(I('gesture'));
        $type = I('type',0,'intval');
        // 不能小于4位
        if (strlen($gesture) < 4) {
            $ret['code'] = 0;
            $ret['message'] = '密码长度不能小于4位';
            backJson($ret);
        }
        // 不能有其他字符
        if (!preg_match('/^[0-8]{4,}$/',$gesture)) {
            $ret['code'] = 0;
            $ret['message'] = '密码只能是纯数字';
            backJson($ret);
        }

        switch ($type) {
            case 1:
                // 设置密码
                break;
            case 2:
                // 修改密码
                // 原来密码验证
                $old_gesture = I('old_gesture');
                if (!$old_gesture) {
                    $ret['code'] = 0;
                    $ret['message'] = '参数错误';
                    backJson($ret);
                }
                $user = M('users')->where(array('id' => session('user_id'), 'gesture_cipher' => $old_gesture))->find();
                if (!$user) {
                    $ret['code'] = 0;
                    $ret['message'] = '原密码不正确';
                    backJson($ret);
                }
                break;
            case 3:
                // 忘记密码
                //查看验证码是否为空
                if(!I('ver_num')){
                    $ret['code']=0;
                    $ret['message']='验证码不能为空';
                    backJson($ret);
                }
                //查看短信验证码是否正确
                if(!session('ver_num') || session('ver_num') != I('ver_num')){
                    $ret['code']=0;
                    $ret['message']='手机验证码错误';
                    backJson($ret);
                }
                break;
            default:
                $ret['code'] = 0;
                $ret['message'] = '参数错误';
                backJson($ret);
                break;
        }
        // 存起来
        $res = M('users')->save(array('id' => session('user_id'), 'gesture_cipher' => $gesture, 'gesture_lock' => 0));
        if ($res !== false) {
            $ret['code'] = 1;
            $ret['message'] = '设置成功';
            backJson($ret);
        } else {
            $ret['code'] = 0;
            $ret['message'] = '设置失败';
            backJson($ret);
        }
    }

    /**
     * 设置手势密码 version 2.0
     * $gesture 手势密码
     * $type 1设置，2修改，3忘记
     * return code\message
     * 为了兼容之前的版本
     * 1、忘记密码时，用身份证号验证。
     * zgw PS：可以在第一个接口提示，如果要修改手势密码，请下载最新版本。
     */
    public function setGestureCipher2(){
        $gesture = trim(I('gesture'));
        $type = I('type',0,'intval');
        // 不能小于4位
        if (strlen($gesture) < 4) {
            $ret['code'] = 0;
            $ret['message'] = '密码长度不能小于4位';
            backJson($ret);
        }
        // 不能有其他字符
        if (!preg_match('/^[0-8]{4,}$/',$gesture)) {
            $ret['code'] = 0;
            $ret['message'] = '密码只能是纯数字';
            backJson($ret);
        }

        switch ($type) {
            case 1:
                // 设置密码
                break;
            case 2:
                // 修改密码
                // 原来密码验证
                $old_gesture = I('old_gesture');
                if (!$old_gesture) {
                    $ret['code'] = 0;
                    $ret['message'] = '参数错误';
                    backJson($ret);
                }
                $user = M('users')->where(array('id' => session('user_id'), 'gesture_cipher' => $old_gesture))->find();
                if (!$user) {
                    $ret['code'] = 0;
                    $ret['message'] = '原密码不正确';
                    backJson($ret);
                }
                break;
            case 3:
                // 忘记密码
                //查看验证码是否为空
                if(!I('ver_num')){
                    $ret['code']=0;
                    $ret['message']='验证码不能为空';
                    backJson($ret);
                }
                //查看短信验证码是否正确
                if(!session('ver_num') || session('ver_num') != I('ver_num')){
                    $ret['code']=0;
                    $ret['message']='手机验证码错误';
                    backJson($ret);
                }
                // 验证身份证号
                if (!I('lic_number')) {
                    $ret['code']=0;
                    $ret['message']='身份证号不能为空';
                    backJson($ret);
                }
                $info = M('driver')->where(array('lic_number' => trim(I('lic_number'))))->find();
                if (!$info) {
                    $ret['code']=0;
                    $ret['message']='身份证号不正确';
                    backJson($ret);
                }
                break;
            default:
                $ret['code'] = 0;
                $ret['message'] = '参数错误';
                backJson($ret);
                break;
        }
        // 存起来
        $res = M('users')->save(array('id' => session('user_id'), 'gesture_cipher' => $gesture, 'gesture_lock' => 0));
        if ($res !== false) {
            $ret['code'] = 1;
            $ret['message'] = '设置成功';
            backJson($ret);
        } else {
            $ret['code'] = 0;
            $ret['message'] = '设置失败';
            backJson($ret);
        }
    }

    /**
     * 锁定手势密码
     */
    public function lockGesture(){
        $res = M('users')->save(array('id' => session('user_id'), 'gesture_lock' => 1));
        if ($res !== false) {
            $ret['code'] = 1;
            $ret['message'] = '锁定成功';
            backJson($ret);
        } else {
            $ret['code'] = 0;
            $ret['message'] = '锁定失败';
            backJson($ret);
        }
    }

    /**
     * 更换手机号
     */
    public function changePhone(){
        $new_phone = trim(I('new_phone'));
        $password = trim(I('password'));
        $ver_num = I('ver_num');
        // 检查参数
        if (!$new_phone || !$ver_num) {
            $ret['code'] = 0;
            $ret['message'] = '参数错误';
            backJson($ret);
        }
        // 原密码是否正确
        $info1 = M('users')->where(array('id' => session('user_id'), 'password' => authcode($password)))->find();
        if (!$info1) {
            $ret['code'] = 0;
            $ret['message'] = '原密码错误';
            backJson($ret);
        }
        // 与原手机是否一样，手机号是否存在
        $info = M('users')->where(array('phone' => $new_phone))->find();
        if ($info) {
            $ret['code'] = 0;
            $ret['message'] = '手机号已存在';
            backJson($ret);
        }
        // 查看短信验证码是否正确
        if(!session('ver_num') || session('ver_num') != $ver_num){
            $ret['code']=0;
            $ret['message']='手机验证码错误';
            backJson($ret);
        }
        $res = M('users')->save(array('id' => session('user_id'), 'phone' => $new_phone));
        if ($res !== false) {
            $ret['code'] = 1;
            $ret['message'] = '修改成功';
            backJson($ret);
        } else {
            $ret['code'] = 0;
            $ret['message'] = '修改失败';
            backJson($ret);
        }
    }

}