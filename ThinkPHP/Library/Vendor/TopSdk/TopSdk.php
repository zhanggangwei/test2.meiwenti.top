<?php
//发送短信类
require("Include.php");
class Message extends \Think\Controller {
    //发送验证码短信
    function regSendCode($tel,$verify){
        //公共参数
        $c = new \TopClient;
        $c ->appkey = '23469693';
        $c ->secretKey = '43a0d5c38aed37f034fe2566cbf6fa19';
        $c->format='json';
        //请求参数
        $req = new \AlibabaAliqinFcSmsNumSendRequest;
        $req ->setExtend( "1" );
        $req ->setSmsType( "normal" );
        $req ->setSmsFreeSignName( "煤问题" );
        $req ->setSmsParam( "{num_code:'".$verify."',product:'煤问题'}" );
        $req ->setRecNum( $tel );
        $req ->setSmsTemplateCode( "SMS_33645454" );
        $resp = $c ->execute( $req );
        $res=object_array($resp);
        if($res['result']['success']){
            return true;
        }else{
            return false;
        }
    }
    //派单成功通知车主
    function informTruckerBill($trucker,$company,$truck,$tel){
        //公共参数
        $c = new \TopClient;
        $c ->appkey = '23469693';
        $c ->secretKey = '43a0d5c38aed37f034fe2566cbf6fa19';
        $c->format='json';
        //请求参数
        $req = new \AlibabaAliqinFcSmsNumSendRequest;
        $req ->setExtend( "1" );
        $req ->setSmsType( "normal" );
        $req ->setSmsFreeSignName( "煤问题" );
        $req ->setSmsParam( "{name:'".$trucker."',company:'".$company."',truck:'".$truck."'}" );
        $req ->setRecNum( $tel );
        $req ->setSmsTemplateCode( "SMS_25845146" );
        $resp = $c ->execute( $req );
        $res=object_array($resp);
    }
    //派单成功通知司机
    function informDriverBill($driver,$tel){
        //公共参数
        $c = new \TopClient;
        $c ->appkey = '23469693';
        $c ->secretKey = '43a0d5c38aed37f034fe2566cbf6fa19';
        $c->format='json';
        //请求参数
        $req = new \AlibabaAliqinFcSmsNumSendRequest;
        $req ->setExtend( "1" );
        $req ->setSmsType( "normal" );
        $req ->setSmsFreeSignName( "煤问题" );
        $req ->setSmsParam( "{name:'".$driver."'}" );
        $req ->setRecNum( $tel );
        $req ->setSmsTemplateCode( "SMS_25955147" );
        $resp = $c ->execute( $req );
        $res=object_array($resp);
    }
    //公司审核结果通知公司管理员,待做，阿里后台配置短信
    function informCompanyAuditResult($company,$tel){
        // //公共参数
        // $c = new \TopClient;
        // $c ->appkey = '23469693';
        // $c ->secretKey = '43a0d5c38aed37f034fe2566cbf6fa19';
        // $c->format='json';
        // //请求参数
        // $req = new \AlibabaAliqinFcSmsNumSendRequest;
        // $req ->setExtend( "1" );
        // $req ->setSmsType( "normal" );
        // $req ->setSmsFreeSignName( "煤问题" );
        // $req ->setSmsParam( "{name:'".$driver."'}" );
        // $req ->setRecNum( $tel );
        // $req ->setSmsTemplateCode( "SMS_25955147" );
        // $resp = $c ->execute( $req );
        // $res=object_array($resp);
    }
    /*******************************************短信分享没有绑定司机的提煤单********************************************/
    function shareBill($tel,$url_str){
        //公共参数
        $c = new \TopClient;
        $c ->appkey = '23469693';
        $c ->secretKey = '43a0d5c38aed37f034fe2566cbf6fa19';
        $c->format='json';
        //请求参数
        $req = new \AlibabaAliqinFcSmsNumSendRequest;
        $req ->setExtend( "1" );
        $req ->setSmsType( "normal" );
        $req ->setSmsFreeSignName( "煤问题" );
        $req ->setRecNum( $tel );
        $req ->setSmsParam( "{url_str:'".$url_str."'}" );
        $req ->setSmsTemplateCode( "SMS_33655428" );
        $resp = $c ->execute( $req );
        $res=object_array($resp);
        if($res['result']['success']){
            return true;
        }else{
            return false;
        }
    }
    /*******************************************Java程序失连SAP报警********************************************/
    function javaLoseSap($tel,$time){
        //公共参数
        $c = new \TopClient;
        $c ->appkey = '23469693';
        $c ->secretKey = '43a0d5c38aed37f034fe2566cbf6fa19';
        $c->format='json';
        //请求参数
        $req = new \AlibabaAliqinFcSmsNumSendRequest;
        $req ->setExtend( "1" );
        $req ->setSmsType( "normal" );
        $req ->setSmsFreeSignName( "煤问题" );
        $req ->setRecNum( $tel );
        $req ->setSmsParam( "{time:'".$time."'}" );
        $req ->setSmsTemplateCode( "SMS_78795011" );
        $resp = $c ->execute( $req );
        $res=object_array($resp);
        if($res['result']['success']){
            return true;
        }else{
            return $res;
        }
    }
    /*******************************************Java重启报警********************************************/
    function noticeJavaDie($tel,$time,$company,$reason){
        //公共参数
        $c = new \TopClient;
        $c ->appkey = '23469693';
        $c ->secretKey = '43a0d5c38aed37f034fe2566cbf6fa19';
        $c->format='json';
        //请求参数
        $req = new \AlibabaAliqinFcSmsNumSendRequest;
        $req ->setExtend( "1" );
        $req ->setSmsType( "normal" );
        $req ->setSmsFreeSignName( "煤问题" );
        $req ->setRecNum( $tel );
        $req ->setSmsParam("{time:'".$time."',company:'".$company."',reason:'".$reason."'}" );
        $req ->setSmsTemplateCode( "SMS_78985010" );
        $resp = $c ->execute( $req );
        $res=object_array($resp);
        if($res['result']['success']){
            return true;
        }else{
            return $res;
        }
    }
    /*******************************************Java恢复SAP连接通知********************************************/
    function javaSapOK($tel,$time){
        //公共参数
        $c = new \TopClient;
        $c ->appkey = '23469693';
        $c ->secretKey = '43a0d5c38aed37f034fe2566cbf6fa19';
        $c->format='json';
        //请求参数
        $req = new \AlibabaAliqinFcSmsNumSendRequest;
        $req ->setExtend( "1" );
        $req ->setSmsType( "normal" );
        $req ->setSmsFreeSignName( "煤问题" );
        $req ->setRecNum( $tel );
        $req ->setSmsParam( "{time:'".$time."'}" );
        $req ->setSmsTemplateCode( "SMS_78795016" );
        $resp = $c ->execute( $req );
        $res=object_array($resp);
        if($res['result']['success']){
            return true;
        }else{
            return $res;
        }
    }
}
