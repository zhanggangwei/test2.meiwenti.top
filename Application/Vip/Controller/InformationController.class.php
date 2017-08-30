<?php
/**
 * Created by PhpStorm.
 * 所有车辆控制器
 * User: zgw
 * Date: 2017-05-10
 * Time: 2:48
 */
namespace Vip\Controller;

class InformationController extends CommonController {

    // 新增线路
    public function addInfo(){
        if(IS_POST){
            //验证添加信息
            if(!I('buyer')||!I('seller')||!I('price')){
                alert('提交信息不完整', 300);
            }
            //验证卖家、买家是否正确
            $buyer=M('company')->where(array('id'=>I('buyer')))->find();
            $seller=M('company')->where(array('id'=>I('seller')))->find();
            if(!$buyer||!$seller){
                alert('没有找到卖家或买家', 300);
            }
            //验证线路是否已经添加
            $information=M('information')->where(array('company_id'=>session('company_id'),'buyer_id'=>I('buyer'),'seller_id'=>I('seller')))->find();
            if($information){
                alert('线路已经添加过了', 300);

            }
            //添加信息
            $data=I('post.');
            $data['company_id']=session('company_id');
            $data['buyer_id']=I('buyer');
            $data['seller_id']=I('seller');
            $data['addate']=date("Y-m-d");
            if($data['debit_mode']==0){
                $data['debit_w']=0;
            }
            $res=M('information')->add($data);
            if($res){
                alert('添加成功', 200);
            }else{
                alert('添加失败,请稍后再试', 300);
            }
        }
        $this->display();
    }
    //线路列表
    function getInfo(){
        $page_num  = I('pageCurrent',1) - 1;
        $page_size = I('pageSize', 2);
       $data=M('information i')
           ->join('coal_company c1 on c1.id=i.seller_id')
           ->join('coal_company c2 on c2.id=i.buyer_id')
           ->field('c1.name as seller,c2.name as buyer,i.id as id,i.price,i.oli_price,i.road_toll,i.debit,i.debit_ratio,i.addate,i.debit_w,i.debit_mode')
           ->limit($page_size * $page_num, $page_size)
           ->where(array('company_id'=>session('company_id')))
           ->select();
       //让损方式
        $arr=array('0容忍','范围内容忍','范围内容忍+严厉惩罚');

        foreach ($data as $key=>$value){

            $option1= "{
                   type:'get',
                   data:{id:'".$value['id']."'},
                   url:'".U('deleteInfo')."',
                   confirmMsg:'确定要删除吗？'
                   }";
            $option2 = "{
                id:'company_information_edit',
                data:{id:'".$value['id']."'},
                url:'".U('editInfo')."',
                width:'800',
                height:'600',
                }";
            $data[$key]['dostr'] = create_button($option2, 'dialog', '编辑').create_button($option1,'doajax','删除');
            $data[$key]['debit_mode']=$arr[$value['debit_mode']];
        }
       $count=M('information')->where(array('company_id'=>session('company_id')))->count();
        $info = array(
            'totalRow' => $count,
            'pageSize' => $page_size,
            'list'     => $data,
        );
        echo json_encode($info);
    }
    //删除线路
    function deleteInfo(){
        if(!I('id')){
            alert('提交信息不完整',300);
        }
        $res=M('information')->where(array('id'=>I('id')))->delete();
        if($res){
            alert('删除成功',200);
        }else{
            alert('删除失败，请稍后再试',300);
        }
    }
    //编辑线路
    function editInfo(){
        if(IS_POST){
            //验证添加信息
            if(!I('buyer')||!I('seller')||!I('price')){
                alert('提交信息不完整', 300);
            }
            //验证卖家、买家是否正确
            $buyer=M('company')->where(array('id'=>I('buyer')))->find();
            $seller=M('company')->where(array('id'=>I('seller')))->find();
            if(!$buyer||!$seller){
                alert('没有找到卖家或买家', 300);
            }
            //验证线路是否已经添加
            $information=M('information')->where(array('company_id'=>session('company_id'),'buyer_id'=>I('buyer'),'seller_id'=>I('seller')))->where("id != ".I('id') )->find();
            if($information){
                alert('线路已经添加过了', 300);

            }
            //添加信息
            $data=I('post.');
            $data['company_id']=session('company_id');
            $data['buyer_id']=I('buyer');
            $data['seller_id']=I('seller');
            $data['addate']=date("Y-m-d");
            if($data['debit_mode']==0){
                $data['debit_w']=0;
            }
            $res=M('information')->where(array('id'=>I('id')))->save($data);
            if($res!==false){
                after_alert(array('closeCurrent' => true, 'tabid' => 'Information_infoList'));
            }else{
                alert('添加失败,请稍后再试', 300);
            }
        }
        $information=M('information')->where(array('id'=>I('id')))->find();
        if(!$information){
            alert('信息有误', 300);
        }else{
            $information['buyer']=M('company')->where(array('id'=>$information['buyer_id']))->getField('name');
            $information['seller']=M('company')->where(array('id'=>$information['seller_id']))->getField('name');
        }
        $this->assign('data',$information);
        $this->display();
    }
}