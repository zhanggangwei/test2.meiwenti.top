<?php
namespace Add\Controller;
/**
 * Created by PhpStorm.
 * User: 君王
 * Date: 2017-04-17
 * Time: 9:32
 */
use Think\Controller;
use Vip\Controller\IndexController;

class MapController extends Controller{
    public function __construct(){
        empty($_SESSION['user_id'])?die(json_encode(array('code'=>0,'msg'=>'没有登录的用户'))):'';
    }
    public function update_gps(){
            $map=M("map");
            $data=$map->select();
            foreach ($data as $k=>$v){
                $x=$v['x'];
                $y=$v['y'];
                $id=$v['id'];
             M()->execute("update coal_map set gps=ST_GeomFromText('POINT($x $y)') where id=$id");
            }
        }
//获取附近的人
    public function get_near_list(){
        $x  =  I('x') ;
        $y  =  I('y');
        $distance  =  I('distance');
        empty($x)?die(json_encode(array('code'=>0,'msg'=>'error'))):'';
        empty($y)?die(json_encode(array('code'=>0,'msg'=>'error'))):'';
        empty($distance)?die(json_encode(array('code'=>0,'msg'=>'error'))):'';
        if ($x>180 or $x <-180){
            die(json_encode(array('code'=>'0','message'=>'经度错误')));
        }
        if ($y>90 or $y <-90){
            die(json_encode(array('code'=>'0','message'=>'维度错误')));
        }
        $data=M()->query("SELECT id,real_name,account,phone,CONCAT('http://img.meiwenti.top/',photo) as photo from coal_users where (ST_Distance_Sphere(ST_GeomFromText('POINT(%s %s)'),local_gps))/1000 < %s and is_lock=0 and real_name!='';",$x,$y,$distance);
        if(!empty($data)){
            $data=array(
                'code'=>1,
                'msg'=>'获取附近'.$distance.'公里的人成功!',
                'data'=>$data
            );
            die(json_encode($data));
        }else{
            $data=array(
                'code'=>0,
                'msg'=>'附近没有人哦',
                'data'=>array()
            );
            die(json_encode($data));
        }
    }
//筛选显示
    public function select_view(){
        $type_name  =  I('type_name') ;
        $x  =  I('x') ;
        $y  =  I('y');
        $distance  =  I('distance');
        empty($distance)?die(json_encode(array('code'=>0,'msg'=>'参数错误'))):'';
        if ($x>180 or $x <-180){
            die(json_encode(array('code'=>'0','message'=>'经度错误')));
        }
        if ($y>90 or $y <-90){
            die(json_encode(array('code'=>'0','message'=>'维度错误')));
        }
        if (!is_numeric($distance)){
            die(json_encode(array('code'=>'0','message'=>'距离错误')));
        }
        if(!empty($type_name)){
            $type="add_type='".$type_name."' and";
        }else{
            $type='';
        }
        $view_data=M()->query("select `id`,`name`,photo,add_p,add_s,add_q,x,y,add_type,(select path from coal_mapicon where coal_mapicon.name=coal_map.add_type) as path from coal_map where $type ((ST_Distance_Sphere(ST_GeomFromText('POINT(%s %s)'),gps))/1000) < %s and add_type!=''",$x,$y,$distance);

        if(!empty($view_data)){
            $data=array(
                'code'=>1,
                'msg'=>'获取附近'.$distance.'公里'.$type_name.'成功!',
                'data'=>$view_data
            );
            die(json_encode($data));
        }else{
            $data=array(
                'code'=>0,
                'msg'=>'暂无数据',
                'data'=>array()
            );
            die(json_encode($data));
        }
    }
    public function get_map_type(){
        $view_data=M()->query(" select * from coal_mapicon" );
        if(!empty($view_data)){
            $data=array(
                'code'=>1,
                'msg'=>'获取成功!',
                'data'=>$view_data
            );
            die(json_encode($data));
        }else{
            $data=array(
                'code'=>0,
                'msg'=>'暂无数据',
                'data'=>array()
            );
            die(json_encode($data));
        }
    }
    /*******************************运输版司机，获得车辆列表**********************************/
    public function get_car_list(){
        $x  =  I('x') ;
        $y  =  I('y');
        $distance  =  I('distance');

        empty($distance)?die(json_encode(array('code'=>0,'msg'=>'参数错误'))):'';
        if ($x>180 or $x <-180){
            die(json_encode(array('code'=>'0','message'=>'经度错误')));
        }
        if ($y>90 or $y <-90){
            die(json_encode(array('code'=>'0','message'=>'维度错误')));
        }
        if (!is_numeric($distance)){
            die(json_encode(array('code'=>'0','message'=>'距离错误')));
        }
        //空车
        $kc_1=M()->query("select group_concat(driver_id) as zz from coal_bill  where driver_id<>0 and state in (2,3)");
        $zz1=$kc_1[0]['zz'];
        empty($zz1)?$zz1=-1:'';
        $kc=M()->query("select id,real_name,account,phone,photo,ST_X(local_gps) as x,ST_Y(local_gps) as y from coal_users where id   IN ($zz1) and  (ST_Distance_Sphere(ST_GeomFromText('POINT(%s %s)'),local_gps))/1000 < %s;",$x,$y,$distance);
        // 重车
        $kc_2=M()->query("select group_concat(driver_id) as zz from coal_bill  where driver_id<>0 and state in (4,5)");
        $zz2=$kc_2[0]['zz'];
        empty($zz2)?$zz2=-1:'';
        $zc=M()->query("select id,real_name,account,phone,photo ,ST_X(local_gps) as x,ST_Y(local_gps) as y from coal_users where id   IN ($zz2) and  (ST_Distance_Sphere(ST_GeomFromText('POINT(%s %s)'),local_gps))/1000 < %s;",$x,$y,$distance);
        // 闲置
        $kc_3=M()->query("select group_concat(driver_id) as zz from coal_bill  where driver_id<>0 and state in (1)");
        $zz3=$kc_3[0]['zz'];
        empty($zz3)?$zz3=-1:'';
        $xz=M()->query("select id,real_name,account,phone,photo ,ST_X(local_gps) as x,ST_Y(local_gps) as y from coal_users where id   IN ($zz3) and  (ST_Distance_Sphere(ST_GeomFromText('POINT(%s %s)'),local_gps))/1000 < %s;",$x,$y,$distance);

        //统计

        $kc_tj=M()->query("select count(*) as count from coal_users where id   IN ($zz1) and  (ST_Distance_Sphere(ST_GeomFromText('POINT(%s %s)'),local_gps))/1000 < %s;",$x,$y,$distance);

        $zc_tj=M()->query("select count(*) as count  from coal_users where id   IN ($zz2) and  (ST_Distance_Sphere(ST_GeomFromText('POINT(%s %s)'),local_gps))/1000 < %s;",$x,$y,$distance);

        $xz_tj=M()->query("select count(*) as count  from coal_users where id   IN ($zz3) and  (ST_Distance_Sphere(ST_GeomFromText('POINT(%s %s)'),local_gps))/1000 < %s;",$x,$y,$distance);

        $data=array(
            'code'=>1,
            'tongji'=>array(
                'kc'=>   $kc_tj[0]['count'],
                'zc'=>   $zc_tj[0]['count'],
                'xz'=>   $xz_tj[0]['count'],
            ),
            'kc_data'=>$kc,
            'zc_data'=>$zc,
            'xz_data'=>$xz,
        );
        die(json_encode($data));
    }
    /*******************************运输版车主，获得车辆列表**********************************/
    /**
     * 2017年8月2日11:21:32 zgw add 车主查看旗下车辆.
     */
    public function get_car_list_by_trucker(){
        // 验证是否车主
        $trucks = M('truck')->where(array('owner_type' => 1, 'owner_id' => session('user_id')))->select();
        // sql();
        if (!$trucks) {
            backJson(['code' => 0, 'message' => '您不是车主']);
        }
        $data = array();
        $kc = 0;
        $zc = 0;
        $xz = 0;

        $i = 0;
        foreach ($trucks as $key => $value) {
            /**
             * 2017年8月4日14:35:39 zgw by huang
             * 应该步骤：在车辆装GPS之后。
             * 1、先判断车有没有GPS，没有找到GPS不显示。
             * 2、没有单的空闲车，点击出现司机未知。
             * 3、有单没有司机的空车，点击出现司机未知。
             * 4、已经接单的空车，点击出现司机信息。
             * 5、已经接单的重车，点击出现司机信息。
             *
             * 现在是根据司机gps。所以判断逻辑不一样
             * 先看有没有单，区分空闲和非空闲。
             * 再看有没有司机，区分空车、重车。
             * 再看司机gps,分有gps和没有gps
             * 总结：现在的地图车辆逻辑不准确。只能取有正在进行的单的车。
             *
             */

            // 先看有没有车
            $tmp_truck = M('truck')->find($value['id']);
            if (!$tmp_truck) {
                doLog('车辆id:'.$value['id'].',没有记录',1);
                backJson(array('code'=>0, 'message'=>'车辆数据异常'));
            }
            // 看看有没有单,上一个单的司机
            $tmp_bill = M('bill')->where(array('truck_id' => $value['id'], 'driver_id' => array('neq', 0)))->order('id desc')->find();
            if (!$tmp_bill) {
                doLog($tmp_truck['lic_number'].'没有单',2);
                continue;
                // $data[$key]['info'] = '没有订单记录';
            }
            // gps
            $gps = M('gps_history')->where(['uid' => $tmp_bill['driver_id']])->order('id desc')->find();
            if (!$gps) {
                doLog($tmp_truck['lic_number'].'没有GPS',1);
                continue;
                // $data[$key]['info'] = '没有GPS记录';
            }
            $data[$i]['truck_id'] = $value['id'];
            $data[$i]['lic_number'] = $value['lic_number'];
            $data[$i]['owner_order'] = $value['owner_order'];

            $data[$i]['x'] = $gps['x'];
            $data[$i]['y'] = $gps['y'];
            // 区分轻重车,0=>空车,1=>重车,2>闲置。
            switch ($tmp_bill['state']) {
                case 1:
                    $t_state = 0;
                    $kc++;
                    break;
                case 2:
                    $t_state = 0;
                    $kc++;
                    break;
                case 3:
                    $t_state = 0;
                    $kc++;
                    break;
                case 4:
                    $t_state = 1;
                    $zc++;
                    break;
                case 5:
                    $t_state = 1;
                    $zc++;
                    break;
                case 6:
                    $t_state = 2;
                    $xz++;
                    break;
                case 7:
                    $t_state = 2;
                    $xz++;
                    break;
                case 8:
                    $t_state = 2;
                    $xz++;
                    break;
                case 9:
                    $t_state = 2;
                    $xz++;
                    break;
                default:
                    $t_state = 2;
                    $xz++;
                    break;
            }
            $data[$i]['truck_state'] = $t_state;
            // 司机信息
            $driver_info = M('users')->where(array('id' => $tmp_bill['driver_id']))->find();
            if (!$driver_info) {
                $driver_name = '';
                $driver_phone = '';
                $driver_photo = '';
            } else {
                if ($tmp_bill['state'] < 6) {
                    $driver_name = $driver_info['real_name'];
                    $driver_phone = $driver_info['phone'];
                    $driver_photo = $driver_info['photo'];
                } else {
                    $driver_name = '';
                    $driver_phone = '';
                    $driver_photo = '';
                }
            }
            $data[$i]['id'] = $tmp_bill['driver_id'];
            $data[$i]['driver_name'] = $driver_name;
            $data[$i]['driver_phone'] = $driver_phone;
            $data[$i]['driver_photo'] = getTrueImgSrc($driver_photo);

            $i++;
        }
        if (!$data) {
            backJson(array('code'=>0, 'message'=>'没有正在进行的订单'));
        }
        // 模拟数据
        $data1['code'] = 1;
        $data1['message'] = '获取成功';
        $data1['tongji'] = array(
            'kc' => $kc,
            'zc' => $zc,
            'xz' => count($trucks) - $kc - $zc,
        );
        $data1['data'] = $data;
        // $data['data'] = array(
        //     // array(
        //     //     'lic_number' => '粤B95326',
        //     //     'owner_order' => 'HX001',
        //     //     'id' => '11',
        //     //     'x' => '',
        //     //     'y' => '',
        //     //     'truck_state' => 0,
        //     //     'driver' => array()
        //     // ),
        //     array(
        //         'lic_number' => '粤B95325',
        //         'owner_order' => 'HX002',
        //         'id' => '12',
        //         'x' => '114.033573',
        //         'y' => '22.625097',
        //         'truck_state' => 2,
        //         'driver_name' => '',
        //         'driver_phone' => '',
        //         'driver_photo' => ''
        //     ), // 闲置
        //     array(
        //         'lic_number' => '粤B95324',
        //         'owner_order' => 'HX003',
        //         'id' => '14',
        //         'x' => '114.035573',
        //         'y' => '22.626097',
        //         'truck_state' => 0,
        //         'driver_name' => '',
        //         'driver_phone' => '',
        //         'driver_photo' => ''
        //     ), // 空车无司机
        //     array(
        //         'lic_number' => '粤B95323',
        //         'owner_order' => 'HX004',
        //         'id' => '114',
        //         'x' => '114.013573',
        //         'y' => '22.628097',
        //         'truck_state' => 0,
        //         'driver_name' => '张三',
        //         'driver_phone' => '15895623652',
        //         'driver_photo' => getTrueImgSrc('asdasd.png')
        //     ), // 空车有司机
        //     array(
        //         'lic_number' => '粤B95322',
        //         'owner_order' => 'HX005',
        //         'id' => '214',
        //         'x' => '114.023573',
        //         'y' => '22.634097',
        //         'truck_state' => 1,
        //         'driver_name' => '李四',
        //         'driver_phone' => '15295623652',
        //         'driver_photo' => getTrueImgSrc('asdasd111.png')
        //     ), // 重车
        // );
        backJson($data1);
    }
    /*******************************企业版获取地图车辆列表**********************************/
    // 2017年6月5日20:55:57 add zgw 企业版，获得车辆列表接口
    public function get_car_list_qyb(){
        $x  =  I('x') ;
        $y  =  I('y');
        $distance  =  I('distance');
        // 检验参数
        empty($distance)?die(json_encode(array('code'=>0,'msg'=>'参数错误'))):'';
        if ($x>180 or $x <-180){
            die(json_encode(array('code'=>'0','message'=>'经度错误')));
        }
        if ($y>90 or $y <-90){
            die(json_encode(array('code'=>'0','message'=>'维度错误')));
        }
        if (!is_numeric($distance)){
            die(json_encode(array('code'=>'0','message'=>'距离错误')));
        }
        // 取数据,状态只是2-5，不包含1，6，7，8，9
        $data1 = M('bill b')
            ->field('u.id,ST_X(u.local_gps) as x,ST_Y(u.local_gps) as y,u.real_name,u.phone,t.lic_number,b.seller,b.buyer,b.state')
            ->join('left join coal_users u on u.id = b.driver_id')
            ->join('left join coal_truck t on t.id = b.truck_id')
            ->where("((ST_Distance_Sphere(ST_GeomFromText('POINT($x $y)'),u.local_gps))/1000) < $distance")
            ->where(array('b.state' => array('in',array(2,3,4,5))))
            ->select();
        // 目的地
        $kc_conut = 0;
        $zc_conut = 0;
        $kc_data = array();
        $zc_data = array();
        foreach ($data1 as $key => $value) {
            $destination = ''; // 目的地
            $truck_state = ''; // 空车重车
            if ($value['state'] == 2 || $value['state'] == 3) {
                // 空车
                $destination = getCompanyName($value['seller']);
                $truck_state = 'kc.png';
                $kc_conut++;
                $value['destination'] = $destination;
                $value['truck_state'] = $truck_state;
                $kc_data[] = $value;
            } else {
                // 重车
                $destination = getCompanyName($value['buyer']);
                $truck_state = 'zc.png';
                $zc_conut++;
                $value['destination'] = $destination;
                $value['truck_state'] = $truck_state;
                $zc_data[] = $value;
            }
        }
        // 闲置，司机身份没拉单的
        $xz_count = 0;
        $xz = array();

        // 返回数据
        $data=array(
            'code'=>1,
            'tongji'=>array(
                'kc'=>   $kc_conut,
                'zc'=>   $zc_conut,
                'xz'=>   $xz_count,
            ),
            'kc_data'=>$kc_data,
            'zc_data'=>$zc_data,
            'xz_data'=>$xz,
        );
        die(json_encode($data));
    }
    public function search_map(){
        $keyword  =  I('keyword');
        empty($keyword)?die(json_encode(array('code'=>0,'msg'=>'参数错误'))):'';
        $data1=M()->query("select `id`,`name`,photo,add_p,add_s,add_q,x,y,add_type,(select path from coal_mapicon where coal_mapicon.name=coal_map.add_type) as path from coal_map where name like '%$keyword%'");
        if(!empty($data1)){
            $data=array(
                'code'=>1,
                'msg'=>'获取成功!',
                'data'=>$data1
            );
            die(json_encode($data));
        }else{
            $data=array(
                'code'=>0,
                'msg'=>'暂无数据',
                'data'=>array()
            );
            die(json_encode($data));
        }
    }
    public function upload_gps(){
        // 2017年8月2日11:57:35 zgw add 自动删除2天前的数据 start
        M('gps_history')->where(array('push_time' => array('lt', date('Y-m-d H:i:s', strtotime('-2 days')))))->delete();
        // end
        $uid=$_SESSION['user_id'];

        $x  =  I('x') ;
        $y  =  I('y');
        empty($x)?die(json_encode(array('code'=>0,'msg'=>'参数错误'))):'';
        empty($y)?die(json_encode(array('code'=>0,'msg'=>'参数错误'))):'';
        if ($x>180 or $x <-180){
            die(json_encode(array('code'=>'0','message'=>'经度错误')));
        }
        if ($y>90 or $y <-90){
            die(json_encode(array('code'=>'0','message'=>'维度错误')));
        }
         $data=M()->execute("update coal_users set local_gps=ST_GeomFromText('POINT($x $y)') where id='$uid'");
        if(!empty($data)){
            $h=M()->execute("INSERT INTO coal_gps_history set x=$x, y=$y ,uid=$uid");
            $data=array(
                'code'=>1,
                'msg'=>'更新坐标成功!',
            );
            die(json_encode($data));
        }else{
            $data=array(
                'code'=>0,
                'msg'=>'更新失败',
            );
            die(json_encode($data));
        }
    }
    public function get_gps_by_id(){
        $uid  =  I('uid') ;
        empty($uid)?die(json_encode(array('code'=>0,'msg'=>'参数错误'))):'';
        $data=M()->query("SELECT ST_X(local_gps) as x,ST_Y(local_gps) as y,id from coal_users where id in ($uid)");
        if(!empty($data)){
            $data=array(
                'code'=>1,
                'msg'=>'获取成功!',
                'data'=>$data
            );
            die(json_encode($data));
        }else{
            $data=array(
                'code'=>0,
                'msg'=>'获取失败',
                'data'=>array()
            );
            die(json_encode($data));
        }
    }
    public function get_gps_by_id_v2(){
        $uid  =  I('uid') ;
        $unix_time  =  I('unix_time') ;
        empty($uid)?die(json_encode(array('code'=>0,'msg'=>'参数错误'))):'';
        empty($unix_time)?die(json_encode(array('code'=>0,'msg'=>'参数错误'))):'';
        $data=M()->query("SELECT  x, y,uid from coal_gps_history where uid in ($uid)and UNIX_TIMESTAMP(push_time)<$unix_time");
        if(!empty($data)){
            $data=array(
                'code'=>1,
                'msg'=>'获取成功!',
                'data'=>$data
            );
            die(json_encode($data));
        }else{
            $data=array(
                'code'=>0,
                'msg'=>'更新失败',
                'data'=>array()
            );
            die(json_encode($data));
        }
    }
    public function admin_get_cargps_zc(){
        $uid=$_SESSION['company_id'];
        $data=M()->query("select distinct c.id as userid,a.photo,a.model,a.lic_number,c.real_name,ST_X(c.local_gps) as longitude,ST_Y(c.local_gps) as latitude ,c.phone from coal_truck a
LEFT JOIN coal_bill b on b.truck_id=a.id
LEFT JOIN coal_users c on b.driver_id=c.id
where a.user_id='$uid' and a.is_comperation=1 and b.state in (2,3,4,5) and ST_X(c.local_gps)!='' and ST_Y(c.local_gps) !='';");
        echo json_encode($data);die();

    }
    public function admin_get_cargps_kc(){
        $uid=$_SESSION['company_id'];
        $data=M()->query("select distinct c.id as userid ,a.photo,a.model,a.lic_number,c.real_name,ST_X(c.local_gps) as longitude,ST_Y(c.local_gps) as latitude ,c.phone from coal_truck a
LEFT JOIN coal_bill b on b.truck_id=a.id
LEFT JOIN coal_users c on b.driver_id=c.id
where a.user_id='$uid' and a.is_comperation=1 and b.state in (1,6,7,8) and ST_X(c.local_gps)!='' and ST_Y(c.local_gps) !='';");
        echo json_encode($data);die();
    }
    public function admin_get_cargps_all(){
        $uid=$_SESSION['company_id'];
        $data=M()->query("select distinct c.id as userid ,a.photo,a.model,a.lic_number,c.real_name,ST_X(c.local_gps) as longitude,ST_Y(c.local_gps) as latitude ,c.phone from coal_truck a
LEFT JOIN coal_bill b on b.truck_id=a.id
LEFT JOIN coal_users c on b.driver_id=c.id
where a.user_id='$uid' and a.is_comperation=1  and ST_X(c.local_gps)!='' and ST_Y(c.local_gps) !='';");
        echo json_encode($data);die();
    }

    public function admin_get_history_gps(){
        $uid  =  I('uid') ;
        $unix_time  =  time()-1000;
        empty($uid)?die(json_encode(array('code'=>0,'msg'=>'参数错误'))):'';
        empty($unix_time)?die(json_encode(array('code'=>0,'msg'=>'参数错误'))):'';
        $data=M()->query("SELECT  concat(x,',', y) as gps from coal_gps_history where uid = $uid and UNIX_TIMESTAMP(push_time)<$unix_time");
        if(!empty($data)){
            $data=array(
                'code'=>1,
                'msg'=>'获取成功!',
                'data'=>$data
            );
            die(json_encode($data));
        }else{
            $data=array(
                'code'=>0,
                'msg'=>'更新失败',
                'data'=>array()
            );
            die(json_encode($data));
        }
    }
    public function test_gps(){
        $uid=$_SESSION['user_id'];
        $data=M()->query("select   concat(x,',',y) as gps from  coal_gps_history  where uid=$uid order by push_time desc limit 450,600");
        if(!isset($_SESSION['gps_n'])){
            $_SESSION['gps_n']=0;
        }
        if(count($data)<$_SESSION['gps_n']){
            $_SESSION['gps_n']=0;
        }else{
            $_SESSION['gps_n']=$_SESSION['gps_n']+1;
        }
        print json_encode($data[$_SESSION['gps_n']]);
    }

    // 企业版地图获取车辆，新接口
    public function get_car_list_qyb2(){
        // 根据用户得到公司，涉及子公司
        $users = M('users')->field('local_gps', true)->where(array('id' => session('user_id')))->find();
        if ($users['type'] != 2) {
            $ret['code'] = 0;
            $ret['message'] = '不是企业用户';
            backJson($ret);
        }
        // 看是不是总公司
        $companies = retrun_company_ids($users['company_id']);
        // 2017年8月9日15:05:02 zgw 最新逻辑
        /**
         * 1、物流公司是旗下使用的车。是总数。然后看所有司机的gps,没有gps算空闲。然后看有没有<6的单，重车算重车，
         * 空车算空车。未接单算空车，没有司机信息。
         * 2、贸易商是显示业务相关的车，由单状态2-5的车的司机。没有gps的不展示，有就按照空重车区分。
         */

        // 看是物流公司还是贸易商。如果有物流公司单，默认是物流公司，否则就是贸易商。这个逻辑可能有漏洞
        $bill = M('bill')->where(array('company' => array('in', $companies)))->find();
        if ($bill) {
            // 是物流公司
            $trucks = M('truck')->where(array('user_id' => array('in', $companies)))->select();
            $trcuk_count = M('truck')->where(array('user_id' => array('in', $companies)))->count(); // 总数

            $data = array();
            $kc = 0;
            $zc = 0;
            $xz = 0;

            $i = 0;
            foreach ($trucks as $val) {
                // 取车的司机，看有没有gps
                $tmp = M('driver')->where(array('truck_id' => array('in', $val['id'])))->select();
                if ($tmp) {
                    $tmps = array();
                    foreach ($tmp as $v) {
                        $tmps[] = $v['uid'];
                    }
                    $is_gps = M('gps_history')->where(array('uid' => array('in', $tmps)))->order('id desc')->find();
                    if ($is_gps) {
                        // 有gps
                        $data[$i]['truck_id'] = $val['id'];
                        $data[$i]['lic_number'] = $val['lic_number'];
                        $data[$i]['owner_order'] = $val['owner_order'];
                        $data[$i]['x'] = $is_gps['x'];
                        $data[$i]['y'] = $is_gps['y'];
                        $bill = M('bill')->where(array('truck_id' => $val['id'], 'state' => array('lt', 6)))->find();
                        // 区分轻重车,0=>空车,1=>重车,2>闲置。
                        switch ($bill['state']) {
                            case 1:
                                $t_state = 0;
                                $kc++;
                                break;
                            case 2:
                                $t_state = 0;
                                $kc++;
                                break;
                            case 3:
                                $t_state = 0;
                                $kc++;
                                break;
                            case 4:
                                $t_state = 1;
                                $zc++;
                                break;
                            case 5:
                                $t_state = 1;
                                $zc++;
                                break;
                            case 6:
                                $t_state = 2;
                                $xz++;
                                break;
                            case 7:
                                $t_state = 2;
                                $xz++;
                                break;
                            case 8:
                                $t_state = 2;
                                $xz++;
                                break;
                            case 9:
                                $t_state = 2;
                                $xz++;
                                break;
                            default:
                                $t_state = 2;
                                $xz++;
                                break;
                        }
                        doLog($val['lic_number'].'单的状态是：'.$bill['state'],4);
                        $data[$i]['truck_state'] = $t_state;
                        if ($t_state == 1) {
                            $driver_id = 0;
                            $driver_name = '';
                            $driver_phone = '';
                            $driver_photo = '';
                        } else {
                            $driver_info = M('users')->where(array('id' => $bill['driver_id']))->find();
                            if (!$driver_info) {
                                $driver_id = 0;
                                doLog($val['lic_number'].'单的状态是：'.$bill['state'].',司机信息异常',4);
                            } else {
                                $driver_id = $bill['driver_id'];
                            }
                            $driver_id = $bill['driver_id'];
                            $driver_name = $driver_info['real_name'];
                            $driver_phone = $driver_info['phone'];
                            $driver_photo = $driver_info['photo'];
                        }

                        $data[$i]['id'] = $driver_id;
                        $data[$i]['driver_name'] = $driver_name;
                        $data[$i]['driver_phone'] = $driver_phone;
                        $data[$i]['driver_photo'] = getTrueImgSrc($driver_photo);
                        $i++;
                    } else {
                        $xz++;
                        continue;
                    }
                } else {
                    $xz++;
                    continue;
                }
            }
            $data1['code'] = 1;
            $data1['tongji'] = array(
                'kc' => $kc,
                'zc' => $zc,
                'xz' => $trcuk_count - $kc - $zc,
                // 'xz1' => $xz,
            );
            $data1['data'] = $data;
            backJson($data1);
        } else {
            // 看做是贸易商
            $companies_str = implode(',', $companies);
            $bill = M()->query('select * from coal_bill where (buyer in ('.$companies_str.') or seller in ('.$companies_str.')) and state < 6 and state > 1 order by id desc');

            $data = array();
            $kc = 0;
            $zc = 0;
            $xz = 0;

            $i = 0;
            foreach ($bill as $key => $value) {
                // doLog($i);
                // 取车的司机，看有没有gps
                $tmp = M('driver')->where(array('truck_id' => array('in', $value['truck_id'])))->select();
                if ($tmp) {
                    $tmps = array();
                    foreach ($tmp as $v) {
                        $tmps[] = $v['uid'];
                    }
                    // 看有没有gps
                    $is_gps = M('gps_history')->where(array('uid' => array('in', $tmps)))->order('id desc')->find();
                    if ($is_gps) {
                        // 有gps
                        if (!$value['truck_id']) {
                            continue;
                        }
                        $data[$i]['truck_id'] = $value['truck_id'];
                        $tmp_truck = M('truck')->find($value['truck_id']);
                        $data[$i]['lic_number'] = $tmp_truck['lic_number'];
                        $data[$i]['owner_order'] = $tmp_truck['owner_order'];
                        $data[$i]['x'] = $is_gps['x'];
                        $data[$i]['y'] = $is_gps['y'];

                        // 区分轻重车,0=>空车,1=>重车,2>闲置。
                        switch ($value['state']) {
                            case 1:
                                $t_state = 0;
                                $kc++;
                                break;
                            case 2:
                                $t_state = 0;
                                $kc++;
                                break;
                            case 3:
                                $t_state = 0;
                                $kc++;
                                break;
                            case 4:
                                $t_state = 1;
                                $zc++;
                                break;
                            case 5:
                                $t_state = 1;
                                $zc++;
                                break;
                            case 6:
                                $t_state = 2;
                                $xz++;
                                break;
                            case 7:
                                $t_state = 2;
                                $xz++;
                                break;
                            case 8:
                                $t_state = 2;
                                $xz++;
                                break;
                            case 9:
                                $t_state = 2;
                                $xz++;
                                break;
                            default:
                                $t_state = 2;
                                $xz++;
                                break;
                        }
                        doLog($tmp_truck['lic_number'].'单的状态是：'.$value['state'],4);
                        $data[$i]['truck_state'] = $t_state;
                        if ($t_state == 1) {
                            $driver_id = 0;
                            $driver_name = '';
                            $driver_phone = '';
                            $driver_photo = '';
                        } else {
                            $driver_info = M('users')->where(array('id' => $value['driver_id']))->find();
                            if (!$driver_info) {
                                $driver_id = 0;
                                doLog($tmp_truck['lic_number'].'单的状态是：'.$value['state'].',司机信息异常',4);
                            } else {
                                $driver_id = $value['driver_id'];
                            }
                            $driver_name = $driver_info['real_name'];
                            $driver_phone = $driver_info['phone'];
                            $driver_photo = $driver_info['photo'];
                        }

                        $data[$i]['id'] = $driver_id;
                        $data[$i]['driver_name'] = $driver_name;
                        $data[$i]['driver_phone'] = $driver_phone;
                        $data[$i]['driver_photo'] = getTrueImgSrc($driver_photo);
                        $i++;

                    } else {
                        continue;
                    }
                } else {
                    continue;
                }
            }

            $data1['code'] = 1;
            $data1['tongji'] = array(
                'kc' => $kc,
                'zc' => $zc,
                'xz' => 0,
                // 'xz1' => $xz,
            );
            $data1['data'] = $data;
            backJson($data1);
        }
    }

}