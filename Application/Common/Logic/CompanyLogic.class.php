<?php
//贸易商模型
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/3/22
 * Time: 17:33
 */
namespace Common\Logic;
//use Common\Model\BaseModel;
class CompanyLogic extends \Think\Model{
    // 权限验证
    function authCheck($user_id,$auth){
        $user=M('users')->where(array('id'=>session('user_id')))->find();
        $company = M('company')->where(array('id' => $user['company_id']))->find();
        if($company['pid']==0&&$user['is_admin']==1){
            return true;
        }
        $user_auth=explode(',',M('users')->where(array('id'=>$user_id))->getField('auth_list'));
       // dump($user_auth);
        if(in_array($auth,$user_auth)) return true;
        else return false;
    }

    //认证(输入认证)
    function authentication(){
        //is_company:1=》企业认证，2=》个人认证
        $company['name']=I('lic_name');
        $company['lic_number']=I('lic_number');
        $company['lic_pic']=I('lic_pic');
        switch (I('is_company')){
            case 1:
                $company['is_vip']=1;
                //填写个人姓名
                $user_info = M('users')->find(session('user_id'));
                if($user_info['is_authentication']==0) {
                    if (!I('name')) {
                        return array('code' => 0, 'message' => '个人姓名不能为空');
                    }
                    $res=D('Company')->addData($company);
                    if($res['code']==1){
                        //修改users表对应的company_id
                        $user['company_id']=$res['id'];
                        $user['real_name'] = I('name');
                        $user['is_authentication'] = 1;
                        $ret=D('Users')->editData(array('account'=>session('user')),$user);
                        if($ret['code']){
                            return array('code'=>1,'message'=>'企业类型认证成功');
                        }
                    }else{
                        return $res;
                    }
                }else if($user_info['is_authentication']==1){
                    // is_authentication = 1是指已经提交（含审核通过和未通过的）
                    return array('code'=>0,'message'=>'您已提交过认证，不能重复提交');
                }
                break;
            case 2:
                $company['is_vip']=0;
                //填写个人姓名
                if(M('users')->where(array('id'=>session('user_id')))->getField('is_authentication')!=1){
                    if(!I('name')){
                        return array('code'=>0,'message'=>'个人姓名不能为空');
                    }
                    $res=D('Company')->addData($company);
                    if($res['code']==1){
                        //修改users表对应的company_id
                        $user['company_id']=$res['id'];
                        $user['real_name']=I('name');
                        $user['is_authentication'] = 1;
                        $ret=D('Users')->editData(array('account'=>session('user')),$user);
                        if($ret['code']){
                            return array('code'=>1,'message'=>'个体类型认证成功');
                        }
                    }else{
                        return $res;
                    }

                }else{
                   return array('code'=>0,'message'=>'您已提交过认证，不能重复提交');
                }
                break;
            default:
                $res['code']=0;
                $res['message']='认证类型填写有误';
                backJson($res);
        }
    }
    //认证(已经存在,进行认领)
    function authentication1(){
        if(!I('company_id')){
            $ret['code']=0;
            $ret['message']='参数填写不完整';
            backJson($ret);
        }
        //is_company:1=》企业认证，2=》个人认证
        switch (I('is_company')){
            case 1:
                $company['lic_pic']=I('lic_pic');
                $company['is_vip']=1;
                $company['id']=I('company_id');
                $res=D('Company')->save($company);
                if($res!==false){
                    //修改users表对应的company_id
                    $user['company_id']=I('company_id');
                    //填写个人姓名
                    if(M('users')->where(array('id'=>session('user_id')))->getField('is_authentication')!=1) {
                        if (!I('name')) {
                            return array('code' => 0, 'message' => '个人姓名不能为空');
                        }
                        $user['real_name'] = I('name');
                        $user['is_authentication'] = 1;
                        $ret=D('Users')->editData(array('account'=>session('user')),$user);
                        if($ret['code']){
                            return array('code'=>1,'message'=>'企业类型认证成功');
                        }
                    }else{
                        return array('code'=>0,'message'=>'您已提交过认证，不能重复提交');
                    }

                }else{
                    return $res;
                }
                break;
            case 2:
                $company['lic_number']=I('lic_number');
                $company['lic_pic']=I('lic_pic');
                $company['is_vip']=0;
                $company['id']=I('company_id');
                $res=D('Company')->save($company);
                if($res!==false){
                    //修改users表对应的company_id
                    $user['company_id']=I('company_id');
                    //填写个人姓名
                    if(M('users')->where(array('id'=>session('user_id')))->getField('is_authentication')!=1){
                        if(!I('name')){
                            return array('code'=>0,'message'=>'个人姓名不能为空');
                        }
                        $user['real_name']=I('name');
                        $user['is_authentication'] = 1;
                        $ret=D('Users')->editData(array('account'=>session('user')),$user);
                        if($ret['code']){
                            return array('code'=>1,'message'=>'个体类型认证成功');
                        }
                    }else{
                        return array('code'=>0,'message'=>'您已提交过认证，不能重复提交');
                    }
                }else{
                    return $res;
                }
                break;
            default:
                $res['code']=0;
                $res['message']='认证类型填写有误';
                backJson($res);
        }
    }
    //添加子公司

    /**
     * 得到公司的gps
     * @param $company_id 公司id
     * @return array
     */
//    public function getCompanyGps($company_id){
//        if (!$company_id) {
//            $company_id = session('company_id');
//        }
//        $sql = "SELECT ST_X(add_gps) AS x,ST_Y(add_gps) AS y FROM coal_area WHERE (`relation_id` = $company_id and `is_company` = 1 and `type` = 2)";
//        $res = M()->query($sql);
//        if ($res[0]['x'] != null) {
//            $data = array(
//                'gps' => $res[0]['x'].','.$res[0]['y'],
//                'gps_sql' => $res[0]['x'].' '.$res[0]['y'],
//                'gps_x' =>$res[0]['x'],
//                'gps_y' =>$res[0]['y']
//            );
//        } else {
//            $data = array();
//        }
//        return $data;
//    }

    //获得公司还没完成的物流计划
    public function logisticsList($company_id){
        $logistics = M('logistics')->where(array('assigned_id'=>$company_id,'state'=>1))->where('res_quantity>0')->select();
        return $logistics;
    }
    //获取物流公司可以使用用的车辆
    function getUseTrukList($company_id){
        $trucks=M('truck')->where(array('user_id'=>$company_id,'state'=>1))->where('is_comperation!=2 and user_id!=0')->select();
        $arr=[];
        $i=0;
        foreach ($trucks as $v){
            if(D('Truck','Logic')->is_driver($v['id'])){
                $arr[$i]=$v;
                $i++;
            }
        }
        dump($arr);
    }

    /**
     * 获取合作中的车的数量
 */
    public function getCooTruckNum($id, $owner_type = 1, $type = 1){
        $num = M('truck')
            ->where(array('anchored_id' => session('company_id'), 'owner_id' => $id))
            ->count();
        // sql();
        return $num;
    }

    //获取合作中公司的车的数量
    // public function getCooCompanyTruckNum($trucker_id){
    //     $num = M('truck')
    //         ->where(array('anchored_id' => session('company_id'), 'owner_id' => $trucker_id))
    //         ->count();
    //     // sql();
    //     return $num;
    // }

    // 是否能接收
    public function isCanTakeOver($truck_id){
        $truck = M('truck')->find($truck_id);
        if ($truck['is_passed'] == 0) {
            return array('code' => 0, 'message' => '车辆还没有审核');
        }
        if ($truck['anchored_id'] != 0 || $truck['is_anchored'] != 0 || $truck['user_id'] != 0 || $truck['is_comperation'] != 0) {
            return array('code' => 0, 'message' => '车辆已经被接收了');
        }
        return array('code' => 1, 'message' => '可以接收');
    }

    // 接收
    public function takeOver($truck_id){
        $data = array(
            'id'              => $truck_id,
            'anchored_id'     => session('company_id'),
            'user_id'         => session('company_id'),
            'is_anchored'     => 1,
            'is_comperation'  => 1
        );
        $res = D('Truck')->save($data);
        if($res!==false){
            //把司机拉进公司群
            vendor('Emchat.Easemobclass');
            $group_id=M('company')->where(array('id'=>session('company_id')))->getField('group_id');
            $driver_id=M('driver')->where(array('truck_id'=>$truck_id))->field('uid')->select();
            $h=new \Easemob();
            foreach ($driver_id as $value){
                $h->addGroupMember($group_id,$value['uid']);
            }


        }
        return $res !== false?true:false;
    }

    // 获取公司自动派单的状态
    public function getAutoArrBillState(){
        $state = M('company')->where('id = '.session('company_id'))->getField('auto_arrbill');
        return $state;
    }
    /**
     * 创建子公司
     * @param $company_id 操作公司id
     * @param $admin_phone 子公司管理员手机号
     * @param $admin_auth 子公司管理员权限列表(数组)
     * @param $admin_name 子公司管理员名称
     * @param $password 子公司管理员密码
     * @param $company_name 子公司名称
     * @param $photo 子公司图片
     * @param $gps 子公司地址
     * @param $longitude 经度
     * @param $latitude 纬度
     * @return array
     */
    function addSubsidiary($company_id,$admin_phone,$admin_auth,$admin_name,$password,$company_name,$photo,$longitude,$latitude,$province,$city,$area,$detail){
        //判定公司必须是总公司才能创建子公司
        $company=M('company')->where(array('id'=>$company_id))->find();
        if($company['pid']!=0){
            $ret['code']=0;
            $ret['message']='操作公司不是总公司';
            return $ret;
        }
        $subsidiary['name']=$company_name;
        $subsidiary['is_vip']=$company['is_vip'];
        $subsidiary['pid']=$company_id;
        $subsidiary['is_passed']=1;
        $subsidiary['auto_arrbill']=$company['auto_arrbill'];
        $subsidiary['province']=$province;
        $subsidiary['city']=$city;
        $subsidiary['area']=$area;
        $subsidiary['detail']=$detail;
        $res1=D('Company')->addData($subsidiary);
        if($res1['code']!=1){
            return $res1;
        }else{
            $user['company_id']=$res1['id'];
        }
        $user['real_name']=$admin_name;
        $user['password']=$password;
        $user['phone']=$admin_phone;
        $user['type']=2;
        $user['photo']=$photo;
        $user['is_admin']=1;
        $user['auth_list']=implode(',',$admin_auth);
        $user['default_set_menu']=M('users')->where(array('company_id'=>$company_id,'is_admin'=>1))->getField('default_set_menu');
        $user['is_authentication']=1;
        $res2=D('Users')->addData($user);
        if($res2['code']!=1){
            return $res2;
        }else{
            //添加子公司群,群主为总公司管理员
            $admin_id=M('users')->where(array('company_id'=>$company_id,'is_admin'=>1))->getField('id');
            vendor('Emchat.Easemobclass');
            $h=new \Easemob();
            $options ['groupname'] = $company_name;
            $options ['desc'] = '这个群是'.$company_name.'的群';
            $options ['public'] = true;
            $options ['owner'] = $admin_id;
            $group=$h->createGroup($options);
            //添加子公司管理员到该群
            $h->createUser($res2['id'],$password);
            $h->addGroupMember($group['data']['groupid'],$res2['id']);
            $gps_str="'POINT(".$longitude." ".$latitude.")'";
            $sql="update coal_company SET `add_gps`= ST_GeomFromText(".$gps_str."),group_id='".$group['data']['groupid']."' WHERE (id='".$res1['id']."')";

            $res3=M()->execute($sql);

            if($res3!==false){
                return array('code'=>1,'message'=>'添加成功');
            }
        }
    }
    /**
     * 编辑子公司
     * @param $company_id 操作公司id
     * @param $subcompany_data 要保存的分公司的数据（必须带公司id）
     * @param $sub_manager_data 要保存的分公司管理员的数据(必须带管理员id)
     * @return array
     */
    function editSubsidiary($company_id,$subcompany_data,$sub_manager_data){
        //判定公司必须是总公司才能创建子公司
        $company=M('company')->find($company_id);
        if($company['pid']!=0){
            $ret['code']=0;
            $ret['message']='操作公司不是总公司';
            return $ret;
        }
        // $subsidiary['name']=$company_name;
        // $subsidiary['province']=$province;
        // $subsidiary['city']=$city;
        // $subsidiary['area']=$area;
        // $subsidiary['detail']=$detail;
        $res = D('Company')->editData(array('id' => $subcompany_data['id']), $subcompany_data);
        if ($res['code'] == 1 && $res['message'] !== false) {
            $gps = explode(',', $subcompany_data['sub_company_address']);
            $longitude = $gps[0];
            $latitude = $gps[1];
            // 更新gps
            $gps_str="'POINT(".$longitude." ".$latitude.")'";
            $sql="update coal_company SET `add_gps`= ST_GeomFromText(".$gps_str.") WHERE (id='".$subcompany_data['id']."')";
            $res3=M()->execute($sql);
        } else {
            return $res;
        }
        // $user['real_name']=$admin_name;
        // $user['password']=$password;
        // $user['phone']=$admin_phone;
        // $user['photo']=$photo;
        // $user['auth_list']=implode(',',$admin_auth);
        $res2 = D('Users')->editData(array('id' => $sub_manager_data['id']), $sub_manager_data);
        if ($res2['code'] != 1 && $res3) {
            return array('code' => 1, 'message' => '处理成功');
        } else {
            return $res2;
        }

    }
    /**
     * 获取子公司列表
     * @param $company_id 操作公司id
     * @param $limit 字符串分页获取用
     * @param $map 筛选条件，数组格式
     * @return array
     */
    function getSubsidiarys($company_id,$limit='',$map = array()){
        $where = array('c.pid'=>$company_id,'u.is_admin'=>1);
        if ($map) {
            $where = array_merge($where, $map);
        }
        $total_row=M('company as c')
            ->join('coal_users as u  ON u.company_id=c.id')
            ->where($where)
            ->count();
        $subsidiary=M('company as c')
            ->join('left join coal_users as u  ON u.company_id=c.id')
            ->where($where)
            ->field('c.name as company_name,c.id as id,u.account,u.create_time as create_time,u.photo as photo,c.province,c.city,c.area,c.detail,u.phone,u.real_name as admin_name,u.auth_list')
            ->limit($limit)
            ->select();
        foreach ($subsidiary as $key=>$value){
            $managers=M('users')->where(array('company_id'=>$value['id'],'is_admin'=>0))->field('real_name')->select();
            foreach ($managers as $k=>$v){
                $managers_arr[$k]=$v['real_name'];
            }
            $subsidiary[$key]['managers']=count($managers)?implode(',',$managers_arr):'还没分配员工';
        }
        if(!count($subsidiary)){
            $ret['code']=0;
            $ret['message']='数据为空';
            return $ret;
        }
        $data['total_row']=$total_row;
        $data['rows']=$subsidiary;
        return $data;
    }

    /**
     * 获取子公司详情用于修改
     * @param $company_id 操作公司id
     * @param $subsidiary_id 子公司id
     * @return array
     */
    function getSubsidiary($company_id,$subsidiary_id){
        $subsidiary=M('company as c')
            ->join('coal_users as u  ON u.company_id=c.id')
            ->where(array('c.pid'=>$company_id,'c.id'=>$subsidiary_id))
            ->field('c.name as company_name,u.photo as photo,c.province,c.city,c.area,c.detail,u.phone,u.real_name as admin_name,u.auth_list')
            ->find();
        if(!$subsidiary){
            $ret['code']=0;
            $ret['message']='传递参数有误';
            return $ret;
        }
        //获取gps_x
        $sql="select ST_X(add_gps) as longitude,ST_Y(add_gps) as latitude from coal_company WHERE (id=".$subsidiary_id.")";
        $gps=M()->query($sql);
        $gps=$gps[0];
        $subsidiary['longitude']=$gps['longitude'];
        $subsidiary['latitude']=$gps['latitude'];
        return $subsidiary;
    }

    /**
     * 创建子公司员工
     * @param $company_id    操作公司id
     * @param $admin_phone 手机
     * @param $admin_auth 权限
     * @param $admin_name 名字
     * @param $password 密码
     * @param $photo 照片
     * @return array
     */
    function addSManager($company_id,$admin_phone,$admin_auth,$admin_name,$password,$photo){
        $user['company_id']=$company_id;
        $user['real_name']=$admin_name;
        $user['password']=$password;
        $user['phone']=$admin_phone;
        $user['type']=2;
        $user['photo']=$photo;
        $user['is_admin']=0;
        $user['auth_list']=implode(',',$admin_auth);
        $user['default_set_menu']=M('users')->where(array('company_id'=>$company_id,'is_admin'=>1))->getField('default_set_menu');
        $user['is_authentication']=1;
        $res=D('Users')->addData($user);
        if($res['code']!=1){
            return $res;
        }else{
            vendor('Emchat.Easemobclass');
            $h=new \Easemob();
            //环信新增用户
            $h->createUser($res['id'],$password);
            //拉人进群
            $group_id=M('company')->where(array('id'=>$company_id))->getField('group_id');

            $h->addGroupMember($group_id,$res['id']);
            return array('code'=>1,'message'=>'添加成功');
        }
    }

    /**
     * 审核通过
     * @param $ids 一维数组
     * @return bool
     */
    public function auditPass($ids){
        $res = M('company')->where(array('id' => array('in', $ids)))->save(array('is_passed' => 1));
        if ($res !== false) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * 公司周边的拉运空重车数量
     * @param $compnay_id 一维数组
     * @return bool
     */
    function getTrucks($compnay_id){
        //当公司为卖方的时候
        $res['empty']=M('bill')->where(array('seller'=>$compnay_id))->where('state=2 or state=3')->count();
        //当公司为买方的时候
        $res['full']=M('bill')->where(array('buyer'=>$compnay_id))->where('state=5 or state=4')->count();
        return $res;
    }
    /**
     * 获取物流公司的排队车辆
     * $compnay_id 公司id
     */
    function getListTrucks($compnay_id){
            $trucks=M('truck t')
            ->order('last_time asc,t.id asc')
            ->join('coal_driver d on d.truck_id=t.id')
            ->where(array('d.is_work'=>1))
            ->where(array('t.user_id'=>$compnay_id))
            ->where(array('t.is_passed'=>1,'t.state'=>1,'t.is_comperation'=>1))
            ->where('t.user_id!=0')
            ->group('t.id')
            ->field('t.lic_number,t.jiyun,t.user_id as company_id,t.owner_order,t.user_id,t.id')
            ->select();
            $ret=array();
            $i=0;
            foreach ($trucks as $key=>$value){
                $res=D('Truck',"Logic")->isDispatch($value['id'],$compnay_id);
                if($res['code']==1){
                    $ret[$i]=$value;
                    $i++;
                }
            }
            return $ret;
    }
}