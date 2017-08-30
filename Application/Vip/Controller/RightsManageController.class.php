<?php
/**
 * Created by PhpStorm.
 * 权限管理：分公司管理、管理员管理
 * User: zgw
 * Date: 2017/4/28
 * Time: 14:23
 */
namespace Vip\Controller;

class RightsManageController extends CommonController {
	// 分公司列表
	public function getSubCompany(){
        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        $map = array();
        if (I('phone')) {
            $map['u.phone'] = I('phone');
        }
        if (I('company_name')) {
            $map['c.name'] = array('like', '%' . I('company_name') . '%');
        }
        if (I('account')) {
            $map['u.account'] = I('account');
        }

        $limit="$page_size * $page_num.$page_size";

        $data=D('Company','Logic')->getSubsidiarys(session('company_id'),$limit,$map);
        // dump($data);exit;
        foreach ($data['rows'] as $key => $value) {
            $option1 = "{
                id:'RightsManage_subCompanyAuth',
                url:'".U('subCompanyAuth')."',
                data:{sub_auth_ids:'".$value['auth_list']."'},
                type:'get',
                height:'450',
                width:'600'
            }";
            $option2 = "{
                id:'RightsManage_editSubCompany',
                url:'".U('editSubCompany')."',
                data:{id:'".$value['id']."'},
                fresh:true
            }";
        	 // $option2 = "{
          //        type:'get',
          //        data:{id:'".$value['id']."'},
          //        url:'".U('delSubCompany')."',
          //        confirmMsg:'确定要删除吗？'
          //        }";
            $dostr = create_button($option1, 'dialog', '查看权限') . create_button($option2, 'navtab', '详情/修改');
            $data['rows'][$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => $data['total_row'],
            'pageSize' => $page_size,
            'list'     => $data['rows'],
        );
        // dump($info);exit;
        echo json_encode($info);
	}

	// 新增子公司
	public function addSubCompany(){
		if (IS_POST) {
      // dump($_POST);exit;
			$company_id  = session('company_id');
			$admin_phone = I('phone');
			$admin_auth  = I('ruleids_level3');
			$admin_name  = I('username');
			$password    = I('password');
			$company_name  = I('sub_company_name');
			$photo  = I('photo');
			$gps=explode(',', I('sub_company_address'));
			$longitude = $gps[0];
			$latitude = $gps[1];
			$address_p = I('address_p');
			$address_s = I('address_s');
			$address_q = I('address_q');
			$address_d = I('address_d');
			M()->startTrans();
			$res = D('Company', 'Logic')->addSubsidiary($company_id,$admin_phone,$admin_auth,$admin_name,$password,$company_name,$photo,$longitude,$latitude,$address_p,$address_s,$address_q,$address_d);
			if ($res['code'] == 1) {
				M()->commit();
				after_alert(array('message' => $res['message'], 'closeCurrent' => true, 'tabid' => 'RightsManage_subCompany'));
			} else {
				M()->rollback();
				alert($res['message'], 300);
			}
		}
      //获得所有菜单权限的节点
      $one = getAuthListAll();
      $this->auth_info = $one;
		$this->assign('province',get_provice());
		$this->display();
	}

  //显示分公司权限
  public function subCompanyAuth(){
    $subcompany_auth_list = I('sub_auth_ids');
    $this->assign('auth_info' , getMenuListByAuthList($subcompany_auth_list));
    $this->display();
  } 

	// 编辑子公司
	public function editSubCompany(){
    if (IS_POST) {
      // dump($_POST);exit;
      M()->startTrans();
      // 分公司信息
      $subcompany_data = array(
        'id'        => I('post.subcompany_id'), 
        'name'      => I('post.sub_company_name'),
        'province'  => I('post.address_p'),
        'city'      => I('post.address_s'),
        'area'      => I('post.address_q'),
        'detail' => I('post.address_d'),
        'sub_company_address' => I('post.sub_company_address'),
        );

      // $res = D('Company')->save($subcompany_data);
      // 分公司管理员信息
      $sub_manager_data = array(
        'id'            => I('post.submanager_id'),
        'real_name'     => I('post.username'),
        'auth_list'     => implode(',', I('post.ruleids_level3')),
        );
      // 手机号唯一
      $info = M('users')->find(I('post.submanager_id'));
      if (I('post.phone') != $info['phone']) {
          $tmp_info = M('users')->where(array('phone' => I('post.phone')))->find();
      } else {
          $tmp_info = false;
      }
      
      if (I('post.phone') && !$tmp_info) {
        $sub_manager_data['phone'] = I('post.phone');
      } else {
        alert('手机号已经存在', 300);
      }
      if (I('post.photo')) {
        $sub_manager_data['photo'] = I('post.photo');
      }
      if (I('post.password')) {
        $sub_manager_data['password'] = authcode(trim(I('post.password')));
      }
      // if (D('Users')->isPhoneUnique(I('post.phone'))) {
      //     alert('手机号已经存在', 300);
      // }
      // $res1 = D('Users')->save($sub_manager_data);

      $res = D('Company', 'Logic')->editSubsidiary(session('company_id'),$subcompany_data,$sub_manager_data);
      if ($res['code'] == 1) {
        M()->commit();
        after_alert(array('closeCurrent' => true, 'tabid' => 'RightsManage_subCompany'));
      } else {
        M()->rollback();
        alert_false();
      }
    }
    $subcompany_id = I('get.id');
    $company_info = M('company')->find($subcompany_id); // 子公司图片怎么是放在users表里面呢？
    // dump($company_info);exit;
    $user_info = M('users')->where(array('company_id' => $subcompany_id, 'is_admin' => 1))->find();
    // dump($user_info);exit;
    // $current_company_auth = M('users')->where(array('id' => session('user_id')))->getField('auth_list');
    $auth_info = getAuthListAll(); // 给出当前公司（总公司管理员的权限）的所有权限
    // dump($auth_info);exit;
    $data = array(
      'company_info' => $company_info,
      'user_info'    => $user_info,
      'auth_info'    => $auth_info,
      'current_auth_list'    => $user_info['auth_list'],
      );
    // dump($data['current_auth_list']);exit;
    $this->assign($data);
    $this->assign('province',get_provice());
    $this->assign('shiqu',get_shiqu($company_info['province']));
    $this->assign('diqu',get_diqu($company_info['province'],$company_info['city']));
    //获取gps_x
    $sql="select ST_X(add_gps) as x,ST_Y(add_gps) as y from coal_company WHERE (id=".I('id').")";
    $gps=M()->query($sql);
    $gps=$gps[0];
    $this->assign('gps',$gps);
    $this->display();
	}

	// 删除子公司(先不做删除，逻辑复杂)
	public function delSubCompany(){
		// $id = I('id') + 0;
  //       if ($id) {
  //           //检查该车辆是否是当前公司的车辆
  //           $truck = M('truck')->where(array('id' => $id, 'owner_id' => session('company_id'), 'owner_type' => 2))->find();
  //           if(!$truck){
  //               alert('车辆信息有误', 300);
  //           }
  //           //检查该车辆是否有正在绑定的司机
  //           $trucker=M('driver')->where(array('truck_id'=>I('id')))->find();
  //           if($trucker){
  //               $this->error('该车辆绑定的还有司机，不能删除');
  //           }
  //           $res = M('truck')->delete($id);
  //           show_res($res);
  //       } else {
  //           alert('操作有误', 300);
  //       }
	}
	//新增员工
    public function addManager(){
        if (IS_POST) {
            $company_id  = I('company_id')?I('company_id'):session('company_id');
            $admin_phone = I('phone');
            $admin_auth  = I('ruleids_level3');
            $admin_name  = I('username');
            $password    = I('password');
            $photo  = I('photo');
            M()->startTrans();
            $res = D('Company', 'Logic')->addSManager($company_id,$admin_phone,$admin_auth,$admin_name,$password,$photo);
            if ($res['code'] == 1) {
                M()->commit();
                after_alert(array('message' => $res['message'], 'closeCurrent' => true, 'tabid' => 'RightsManage_managers'));
            } else {
                M()->rollback();
                alert($res['message'], 300);
            }
        }

        //获得所有菜单的节点
        if(!session('is_check_auth')){
            $one = M('menu')->where(array('is_admin' => 0, 'pid' => 0))->select();
            foreach ($one as $key => $value) {
                $two = M('menu')->where(array('is_admin' => 0, 'pid' => $value['id']))->select();
                $one[$key]['list'] = $two;
                foreach ($two as $k => $val) {
                    $rule_name = 'Vip/'.$value['name'].'/'.$val['name'];
                    $tmp_auth_id = M('auth_rule')->where(array('name' => $rule_name))->getField('id');
                    $one[$key]['list'][$k]['auth_id'] = $tmp_auth_id;
                }
            }
        }else{
            $one=array();
            $cur_auth_list = M('users')->where('id = '.session('user_id'))->getField('auth_list');
            $auth_list=explode(',',$cur_auth_list);
            $i=0;
            foreach ($auth_list as $key => $value){
                $auth_name=M('auth_rule')->where(array('id'=>$value))->getField('name');
                $arr=explode('/',$auth_name);
                $menu=M('menu')->where(array('name' => $arr[1]))->find();
                $two_menu = M('menu')->where(array('name'=>$arr[2]))->find();

                $rule_name = 'Vip/'.$menu['name'].'/'.$two_menu['name'];
                $tmp_auth_id = M('auth_rule')->where(array('name' => $rule_name))->getField('id');
                $two_menu['auth_id'] = $tmp_auth_id;
                if ($one[$i-1]['id']!=$menu['id']){
                    $one[$i]=$menu;
                    $one[$i]['list'][]=$two_menu;
                    $i++;
                }else{
                    $one[$i-1]['list'][]=$two_menu;
                }
            }
        }
        //获取分公司列表
        $subCompany=D('Company',"Logic")->getSubsidiarys(session('company_id'));
        if($subCompany['total_row']>0){
            //是否是总公司
            $this->assign('is_zong',1);
            $this->assign('company_id',session('company_id'));
            $this->assign('subCompany',$subCompany['rows']);
        }else{
            $this->assign('is_zong',0);
        }
        $this->auth_info = $one;
        $this->assign('province',get_provice());
        $this->display();
    }
    //员工列表
    public function getManagers(){
        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);

        $map = array();
        if (I('phone')) {
            $map['u.phone'] = I('phone');
        }
        if (I('company_name')) {
            $map['c.name'] = array('like', '%' . I('company_name') . '%');
        }
        if (I('account')) {
            $map['u.account'] = I('account');
        }

        $limit="$page_size * $page_num.$page_size";
        //获取子公司
        $subCompany=M('company')->where(array('pid'=>session('company_id')))->field('id')->select();
        $subCompany_arr=array();
        foreach ($subCompany as $key=>$value){
            $subCompany_arr[$key]=$value['id'];
        }
        if($key>0){
            $subCompany_arr[$key+1]=session('company_id');
        }else{
            $subCompany_arr[0]=session('company_id');
        }
        $subCompany_str=implode(',',$subCompany_arr);
        if($subCompany_str){
            $where['company_id']=array('in',$subCompany_str);
            //去掉总公司超级管理员
            $managers=M('users u')
                ->join('coal_company c on u.company_id=c.id')
                ->where($where)
                ->where($map)
                ->limit($limit)
                ->where(array('is_admin'=>0))
                ->field('c.name as company_name,u.real_name as name,u.phone,u.account,u.create_time,u.id as id,u.auth_list,is_admin')
                ->select();
        }else{
            $managers=array();
        }
        foreach ($managers as $key => $value) {
            $option1 = "{
                id:'RightsManage_subManagerAuth',
                url:'".U('subManagerAuth')."',
                data:{sub_auth_ids:'".$value['auth_list']."'},
                type:'get',
                height:'500',
                width:'600'
            }";
            $option2 = "{
                id:'RightsManage_editSubCompany',
                url:'".U('editManager')."',
                data:{id:'".$value['id']."'},
                type:'get',
                height:'500',
                width:'800'
            }";
            $option3 = "{
                   type:'get',
                   data:{id:'".$value['id']."'},
                   url:'".U('delSubManager')."',
                   confirmMsg:'确定要删除吗？'
                   }";
            $dostr = create_button($option1, 'dialog', '拥有权限') . create_button($option2, 'dialog', '详情/修改'). create_button($option3, 'doajax', '删除');
            $managers[$key]['dostr'] = $dostr;
        }
        $info = array(
            'totalRow' => count($managers),
            'pageSize' => $page_size,
            'list'     => $managers,
        );
        // dump($info);exit;
        echo json_encode($info);
    }
    //显示员工权限
    public function subManagerAuth(){
        $subcompany_auth_list = I('sub_auth_ids');
        $this->assign('auth_info' , getMenuListByAuthList($subcompany_auth_list));
        $this->display();
    }

    // 编辑员工
    public function editManager(){
      if (IS_POST) {
          $id = I('post.id');
          
          $sub_manager_data = array(
            'real_name'     => I('post.username'),
            'auth_list'     => implode(',', I('post.ruleids_level3')),
            'is_login'     => I('is_login'),
            );
          // 手机号唯一
          $info = M('users')->find($id);
          if (I('post.phone') != $info['phone']) {
              $tmp_info = M('users')->where(array('phone' => I('post.phone')))->find();
          } else {
              $tmp_info = false;
          }
          
          if (I('post.phone') && !$tmp_info) {
            $sub_manager_data['phone'] = I('post.phone');
          } else {
            alert('手机号已经存在', 300);
          }
          if (I('post.photo')) {
            $sub_manager_data['photo'] = I('post.photo');
          }
          if (I('post.password')) {
            $sub_manager_data['password'] = authcode(trim(I('post.password')));
          }
          $sub_manager_data['company_id']=I('company_id')?I('company_id'):session('company_id');
          //是否是切换公司了,如果切换了则需要更换所属公司群
          $old_company_id=M('users')->where(array('id'=>$id))->getField('company_id');
          if(I('company_id')!=$old_company_id){
              //把司机拉进公司群
              vendor('Emchat.Easemobclass');
              $old_group_id=M('company')->where(array('id'=>$old_company_id))->getField('group_id');
              $new_group_id=M('company')->where(array('id'=>I('company_id')))->getField('group_id');
              $h=new \Easemob();
              $h->deleteGroupMember($old_group_id,$id);
              $h->addGroupMember($new_group_id,$id);
          }
          // dump($sub_manager_data);exit;
          $res = D('Users')->editData(array('id' => $id), $sub_manager_data);
          if ($res['code'] == 1 && $res['message'] !== false) {
              after_alert(array('closeCurrent' => true, 'tabid' => 'RightsManage_managers'));
          } else {
              alert($res['message'], 300);
          }
      }

      //获得所有菜单的节点
      if(!session('is_check_auth')){
          $one = M('menu')->where(array('is_admin' => 0, 'pid' => 0))->select();
          foreach ($one as $key => $value) {
              $two = M('menu')->where(array('is_admin' => 0, 'pid' => $value['id']))->select();
              $one[$key]['list'] = $two;
              foreach ($two as $k => $val) {
                  $rule_name = 'Vip/'.$value['name'].'/'.$val['name'];
                  $tmp_auth_id = M('auth_rule')->where(array('name' => $rule_name))->getField('id');
                  $one[$key]['list'][$k]['auth_id'] = $tmp_auth_id;
              }
          }
      }else{
          $one=array();
          $cur_auth_list = M('users')->where('id = '.session('user_id'))->getField('auth_list');
          $auth_list=explode(',',$cur_auth_list);
          $i=0;
          foreach ($auth_list as $key => $value){
              $auth_name=M('auth_rule')->where(array('id'=>$value))->getField('name');
              $arr=explode('/',$auth_name);
              $menu=M('menu')->where(array('name' => $arr[1]))->find();
              $two_menu = M('menu')->where(array('name'=>$arr[2]))->find();

              $rule_name = 'Vip/'.$menu['name'].'/'.$two_menu['name'];
              $tmp_auth_id = M('auth_rule')->where(array('name' => $rule_name))->getField('id');
              $two_menu['auth_id'] = $tmp_auth_id;
              if ($one[$i-1]['id']!=$menu['id']){
                  $one[$i]=$menu;
                  $one[$i]['list'][]=$two_menu;
                  $i++;
              }else{
                  $one[$i-1]['list'][]=$two_menu;
              }
          }
      }
      $id = I('get.id');
      // $auth_info = getAuthListAll(); // 给出当前公司（总公司管理员的权限）的所有权限
      // dump($auth_info);exit;

        //获取分公司列表
        $subCompany=D('Company',"Logic")->getSubsidiarys(session('company_id'));
        if($subCompany['total_row']>0){
            //是否是总公司
            $this->assign('is_zong',1);
            $this->assign('company_id',session('company_id'));
            $this->assign('subCompany',$subCompany['rows']);
        }else{
            $this->assign('is_zong',0);
        }
      $this->auth_info = $one;
      // $this->assign('province',get_provice());
      $this->user_info = M('users')->find($id);
      $this->current_auth_list = $this->user_info['auth_list'];
      $this->display();
    }

    // 删除员工
    public function delSubManager(){
      $id = I('id');
      // 用户表
      $res = M('users')->delete($id);
      //剔除公司群
        $company_id=M('users')->where(array('id'=>I('id')))->getField('company_id');
        $group_id=M('company')->where(array('id'=>$company_id))->getField('group_id');
        vendor('Emchat.Easemobclass');
        $h=new \Easemob();
        $h->deleteGroupMember($group_id,I('id'));
        $h->deleteUser(I('id'));
      show_res($res);
    }

    //子公司以及员工组织架构
    function orgChart(){
        $data=M('users u')
            ->join('coal_company c on u.company_id=c.id')
            ->where(array('c.id'=>session('company_id')))
            ->field('u.real_name as 管理员,u.phone as 手机号,c.name as name,c.id as id')
            ->find();
        //获取子公司
        $data['children']=M('company c')
            ->join('coal_users u on u.company_id=c.id')
            ->where(array('c.pid'=>$data['id'],'u.is_admin'=>1))
            ->field('u.real_name as 管理员,u.phone as 手机号,c.name as name,c.id as id')
            ->group('c.name')
            ->select();
        //获取子公司管理员
        foreach ($data['children'] as $key =>$value){
            $data['children'][$key]['children']=M('users')
                ->where(array('company_id'=>$value['id'],'is_admin'=>0))
                ->field('phone as 手机号,real_name as name')
                ->select();
        }
        //获取总公司员工
        $manager=M('users')
            ->where(array('company_id'=>session('company_id'),'is_admin'=>0))
            ->field('phone as 手机号,real_name as name')
            ->select();
        $i=count($data['children']);
        $data['children'][$i]=array('name'=>'本公司员工');
        $data['children'][$i]['children']=$manager;
        $this->assign('data',json_encode($data,false));
        $this->display();
    }
    private function get_array($where,$id = 0){
        $str = implode(',',$where);
        $sql = "SELECT * FROM `coal_admin_auth_rule` WHERE `pid` = $id and `is_menu`=1 and `id` in (".$str.") Order by `sort` DESC";
        $result = M()->query($sql);
        // print_r($result);exit;
        $arr = array();
        if($result){//如果有子类
            foreach($result as $rows){
                $rows['list'] = $this->get_array($where,$rows['id']); //调用函数，传入参数，继续查询下级
                $arr[] = $rows; //组合数组
            }
            return $arr;
        }
    }

}