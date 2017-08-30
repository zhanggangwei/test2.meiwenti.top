<?php
/**
 * Created by PhpStorm.
 * User: 王佩佩
 * Date: 2017/7/20
 * Time: 10:39
 */

namespace Api\Controller;
use Think\Controller;

//通知Java小程序的预警
class NoticejavaController extends ApiController
{
    private $telArr=array('13428718144','13682341896','18147311117');
    //通知SAP断网
     function noticeSap(){
         foreach ($this->telArr as $tel){
             vendor('TopSdk.TopSdk');
             $mes=new \Message();
             $res=$mes->javaLoseSap($tel,I('time'));
             if($res!==true){
                 dump($res);
             }
         }
     }
     //通知软件重启
    function noticeJavaDie(){
        foreach ($this->telArr as $tel){
            vendor('TopSdk.TopSdk');
            $mes=new \Message();
            $res=$mes->noticeJavaDie($tel,I('time'),I('company'),I('reason'));
            if($res!==true){
                dump($res);
            }
        }
    }
    //通知SAP恢复
    function noticeSapOk(){
        foreach ($this->telArr as $tel){
            vendor('TopSdk.TopSdk');
            $mes=new \Message();
            $res=$mes->javaSapOK($tel,I('time'));
            if($res!==true){
                dump($res);
            }
        }
    }
}