<?php
/**
短信发送已测
**/
    include "Include.php";
    date_default_timezone_set('Asia/Shanghai'); 
    $c = new TopClient;
    $c ->appkey = '23469693';
    $c ->secretKey = '43a0d5c38aed37f034fe2566cbf6fa19';
    $req = new AlibabaAliqinFcSmsNumSendRequest;
    $req ->setExtend( "1" );
    $req ->setSmsType( "normal" );
    $req ->setSmsFreeSignName( "煤问题" );
    $req ->setSmsParam( "{code:'104120',product:'煤问题APP'}" );
    $req ->setRecNum( "18002665202" );
    $req ->setSmsTemplateCode( "SMS_16750453" );
    $resp = $c ->execute( $req );
?>