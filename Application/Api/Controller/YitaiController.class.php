<?php
namespace Api\Controller;
use Think\Controller;
//伊泰过磅对接
class YitaiController extends ApiController  {
    /****************************************************自动登陆1*********************************************/
    function _initialize(){
        if(!I('account')||!I('pwd')){
            $ret['code']='0';
            $ret['message']='传递信息不完整';
            die(json_encode($ret));
        }
        //过磅账号密码测试
        $res=D('Users','Logic')->balanceLogin(I('account'),I('pwd'));
        if(!$res['code'])
        {
            $ret['code']=2;
            $ret['message']='账号密码提交有误';
            backJson($ret);
        }else{
            $ret['id']=M('users')->where(array('id'=>session('user_id')))->getField('company_id');
//            $ret['code']=1;
//            $ret['message']='登录成功';
//            backJson($ret);
        }
    }
    /****************************************************登录返回ID*********************************************/
    function login(){
        if(!I('account')||!I('pwd')){
            $ret['code']='0';
            $ret['message']='传递信息不完整';
            die(json_encode($ret));
        }
        //过磅账号密码测试
        $res=D('Users','Logic')->balanceLogin(I('account'),I('pwd'));
        if(!$res['code'])
        {
            $ret['code']=2;
            $ret['message']='账号密码提交有误';
            backJson($ret);
        }else{
            $ret['id']=M('users')->where(array('id'=>session('user_id')))->getField('company_id');
            $ret['code']=1;
            $ret['message']='登录成功';
            backJson($ret);
        }
    }
    /****************************************************提货点进矿扫描*********************************************/
    //建立在过磅扫描已经可以获取提煤单信息的基础上
    function beginFirstRead(){
        if(!I('bill')||!I('account')||!I('pwd')){
            $ret['code']=0;
            $ret['message']='传递信息不完整';
            die(json_encode($ret));
        }
        //过磅账号密码测试
        $res=D('Users','Logic')->balanceLogin(I('account'),I('pwd'));
        if(!$res['code'])
        {
            $ret['code']=2;
            $ret['message']='账号密码提交有误';
            backJson($ret);
        }
        //获取公司ID
        $users=M('users')->where(array('id'=>session('user_id')))->find();
        D('Bill','Logic')->beginFirstRead($users['company_id'],I('bill'),I('type'));
    }
    /****************************************************提货点回批扫描*********************************************/
    //提交空车重车数据从煤矿拉走
    function beginSecondRead(){
        if(!I('bill')||!I('account')||!I('pwd')){
            $ret['code']=0;
            $ret['message']='传递信息不完整';
            die(json_encode($ret));
        }
        //过磅账号密码测试
        $res=D('Users','Logic')->balanceLogin(I('account'),I('pwd'));
        if(!$res['code'])
        {
            $ret['code']=2;
            $ret['message']='账号密码提交有误';
            backJson($ret);
        }
        //获取公司ID
        $users=M('users')->where(array('id'=>session('user_id')))->find();
        D('Bill','Logic')->beginSecondRead($users['company_id'],I('bill'),I('type'),I('begin_w'),I('end_w'));
    }
    /****************************************************送货点进矿扫描*********************************************/
    //建立在过磅扫描已经可以获取提煤单信息的基础上
    function endFirstRead(){
        if(!I('bill')||!I('account')||!I('pwd')){
            $ret['code']=0;
            $ret['message']='传递信息不完整';
            die(json_encode($ret));
        }
        //过磅账号密码测试
        $res=D('Users','Logic')->balanceLogin(I('account'),I('pwd'));
        if(!$res['code'])
        {
            $ret['code']=2;
            $ret['message']='账号密码提交有误';
            backJson($ret);
        }
        //获取公司ID
        $users=M('users')->where(array('id'=>session('user_id')))->find();
        D('Bill','Logic')->endFirstRead($users['company_id'],I('bill'),I('type'));
    }
    /****************************************************送货点回批完成*********************************************/
    //提交空车重车数据从煤矿拉走
    function endSecondRead(){
        if(!I('bill')||!I('account')||!I('pwd')){
            $ret['code']='0';
            $ret['message']='传递信息不完整';
            die(json_encode($ret));
        }
        //过磅账号密码测试
        $res=D('Users','Logic')->balanceLogin(I('account'),I('pwd'));
        if(!$res['code'])
        {
            $ret['code']=2;
            $ret['message']='账号密码提交有误';
            backJson($ret);
        }
        //获取公司ID
        $users=M('users')->where(array('id'=>session('user_id')))->find();
        D('Bill','Logic')->endSecondRead($users['company_id'],I('bill'),I('type'),I('begin_w'),I('end_w'));
    }
    /****************************************************送货点确认提提货点的提煤单数据*********************************************/
    function confirmBalance(){
        if(!I('bill')||!I('account')||!I('pwd')){
            $ret['code']=0;
            $ret['message']='传递信息不完整';
            die(json_encode($ret));
        }
        //过磅账号密码测试
        $res=D('Users','Logic')->balanceLogin(I('account'),I('pwd'));
        if(!$res['code'])
        {
            $ret['code']=2;
            $ret['message']='账号密码提交有误';
            backJson($ret);
        }
        //获取公司ID
        $users=M('users')->where(array('id'=>session('user_id')))->find();
        D('Bill','Logic')->confirmFirstBalance($users['company_id'],I('bill'),I('type'),I('balance_weight'));
    }
    /****************************************************获取煤矿和集运站的数据接口*********************************************/
    function getCompanys(){
        if(!I('account')||!I('pwd')){
            $ret['code']=0;
            $ret['message']='传递信息不完整';
            die(json_encode($ret));
        }
        //过磅账号密码测试
        $res=D('Users','Logic')->balanceLogin(I('account'),I('pwd'));
        if(!$res['code'])
        {
            backJson($res);
        }
        $companys=M('company c')
            ->join('left join coal_users u on u.company_id=c.id')
            ->field('c.name,c.id')
            ->where(array('c.is_passed'=>1,'u.is_admin'=>1))->select();
        backJson($companys);
    }
    /****************************************************获取煤种信息*********************************************/
    function getCoalTypes(){
        if(!I('account')||!I('pwd')){
            $ret['code']=0;
            $ret['message']='传递信息不完整';
            die(json_encode($ret));
        }
        //过磅账号密码测试
        $res=D('Users','Logic')->balanceLogin(I('account'),I('pwd'));
        if(!$res['code'])
        {
            backJson($res);
        }
        $coal_type=M('coal_type')->field('name,id')->select();
        backJson($coal_type);
    }
    /**********************************************测试解码*******************************************************/
    function testReadCode(){
        $type_str=substr(I('code'),0,5);

        if($type_str=='MWT$$'){
            $code_str=substr(I('code'),5);
        }else{
            $code_str=I('code');
        }

        $key=substr($code_str,0,4);//截取二维码数据前四个字符为秘钥
        $data=substr($code_str,4);//截取从第五个字符开始,后面为储存的真正数据
        backJson(decrypt1($data,$key));

    }
    function base64(){
        $str='d3d3LmpiNTEubmV0IOiEmuacrOS5i+Wutg==';     //定义字符串
        echo base64_decode($str); //输出解码后的内容
    }
}