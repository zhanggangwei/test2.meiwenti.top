<?php
namespace Common\Logic;
use Org\Util\String;
use xmpush\Builder;
use xmpush\HttpBase;
use xmpush\Sender;
use xmpush\Constants;
use xmpush\Stats;
use xmpush\Tracer;
use xmpush\Feedback;
use xmpush\DevTools;
use xmpush\Subscription;
use xmpush\TargetedMessage;

use xmpush\IOSBuilder;

//提煤单模型
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2017/3/22
 * Time: 17:33
 */
//use Common\Model\BaseModel;
class PushLogic {
    //关闭自动检测
      private $autoCheckFields=false;

    //安卓运输版定义
    private function android_ys_costant()
      {
          vendor('MiPush.MiPush');
          $secret = 'JuKa196KqR0zdqE6OIJmVw==';
          $package = 'com.jr.findcoal';
          // 常量设置必须在new Sender()方法之前调用
          Constants::setPackage($package);
          Constants::setSecret($secret);
      }
    //苹果运输版定义
    private function ios_ys_costant()
    {
        vendor('MiPush.MiPush');
        $secret = 'zkY7Sk5P46FOhWupJ1gxig==';
        $bundleId = 'com.meiwentitwo.panke';
        Constants::setBundleId($bundleId);
        Constants::setSecret($secret);
    }
    //安卓运输版定义
    private function android_qy_costant(){
        vendor('MiPush.MiPush');
        $secret = 'NcBr5fMl//ocs6j0fMk93g==';
        $package = 'com.ponkr.meiwenti_company';
        // 常量设置必须在new Sender()方法之前调用
        Constants::setPackage($package);
        Constants::setSecret($secret);
    }
    //苹果企业版定义
    private function ios_qy_costant(){
        vendor('MiPush.MiPush');
        $secret = 'MrKh9AyLvKfvZgQpW4z2MA==';
        $bundleId = 'com.Panke.CoalProblemEnterprise';
        Constants::setBundleId($bundleId);
        Constants::setSecret($secret);
    }
    //提煤单推送
    function arrBill($account,$message,$title,$code){
        $title = $title;
        $desc = $message;
        $this->ios_ys_costant();
        $this->ios_ys_send($desc,$code,$account,2);
        $this->android_ys_costant();
        $this->android_ys_send($title,$desc,$code,$account,2);
      }
    //通知车主有新的绑定申请
    function noticeTruckerBind($driver_id,$trucker_id){
        $trucker_phone=M('users')->where(array('id'=>$trucker_id))->getField('phone');
        $code=1200;
        $driver_name=M('users')->where(array('id'=>$driver_id))->getField('real_name');
        $title = '[煤问题]绑定通知';
        $desc = "您接收到一个司机绑定申请,申请人[$driver_name]";
        $this->android_ys_costant();
        $this->android_ys_send($title,$desc,$code,$trucker_phone);
        $this->ios_ys_costant();
        $this->ios_ys_send($desc,$code,$trucker_phone);
    }
    //通知司机通过车主绑定
    function noticeDriverBind($driver_id,$trucker_id,$truck_id){
        $trucker_name=M('users')->where(array('id'=>$trucker_id))->getField('real_name');
        $driver_phone=M('users')->where(array('id'=>$driver_id))->getField('phone');
        $truck_lic_number=M('truck')->where(array('id'=>$truck_id))->getField('lic_number');
        $code=1200;
        $title = '[煤问题]绑定成功';
        $desc = "恭喜您,您已成功绑定在[$trucker_name]车主的[$truck_lic_number]上面";
        $this->android_ys_costant();
        $this->android_ys_send($title,$desc,$code,$driver_phone);
        $this->ios_ys_costant();
        $this->ios_ys_send($desc,$code,$driver_phone);
    }
    //通知司机车主拒绝绑定
    function noticeDriverRefuse($driver_id,$trucker_id){
        $trucker_name=M('users')->where(array('id'=>$trucker_id))->getField('real_name');
        $driver_phone=M('users')->where(array('id'=>$driver_id))->getField('phone');
        $code=1200;
        $title = '[煤问题]绑定失败';
        $desc = "很遗憾,您的绑定申请没有通过[$trucker_name]的审核";
        $this->android_ys_costant();
        $this->android_ys_send($title,$desc,$code,$driver_phone);
        $this->ios_ys_costant();
        $this->ios_ys_send($desc,$code,$driver_phone);
    }
    //通知司机被车主解绑
    function noticeDriverFire($driver_id,$trucker_name){
        $driver_phone=M('users')->where(array('id'=>$driver_id))->getField('phone');
        $code=1200;
        $title = '[煤问题]解除合作';
        $desc = "您和[$trucker_name]车主的合作已经解除,请知悉";
        $this->android_ys_costant();
        $this->android_ys_send($title,$desc,$code,$driver_phone);
        $this->ios_ys_costant();
        $this->ios_ys_send($desc,$code,$driver_phone);
    }
    //通知车主有公司合作申请
    function noticeTruckerCompanyCooperation($trucker_id,$company_id){
        $trucker_phone=M('users')->where(array('id'=>$trucker_id))->getField('phone');
        $company=M('company')->where(array('id'=>$company_id))->getField('name');
        $code=1200;
        $title = '[煤问题]合作通知';
        $desc = "您收到[$company]的合作申请,请确认";
        $this->android_ys_costant();
        $this->android_ys_send($title,$desc,$code,$trucker_phone);
        $this->ios_ys_costant();
        $this->ios_ys_send($desc,$code,$trucker_phone);
    }
    //通知车主有公司解除合作
    function noticeTruckerCompanyBreakCooperation($trucker_id,$company_id){
        $trucker_phone=M('users')->where(array('id'=>$trucker_id))->getField('phone');
        $company=M('company')->where(array('id'=>$company_id))->getField('name');
        $code=1200;
        $title = '[煤问题]合作通知';
        $desc = "您和[$company]合作已经解除,请知悉";
        $this->android_ys_costant();
        $this->android_ys_send($title,$desc,$code,$trucker_phone);
        $this->ios_ys_costant();
        $this->ios_ys_send($desc,$code,$trucker_phone);
    }
    //退单受理_退单成功
    function acceptRbill($driver_id){
        $diver_phone=M('users')->where(array('id'=>$driver_id))->getField('phone');
        $title = '退单成功';
        $desc = '恭喜您,您的退单申请已经通过,请知悉!';
        $code=1200;
        $this->ios_ys_costant();
        $this->ios_ys_send($desc,$code,$diver_phone);
        $this->android_ys_costant();
        $this->android_ys_send($title,$desc,$code,$diver_phone);
    }
    //退单受理_退单失败
    function refuseRbill($driver_id){
        $diver_phone=M('users')->where(array('id'=>$driver_id))->getField('phone');
        $title = '退单失败';
        $desc = '很遗憾,您的退单申请被公司拒绝,请知悉!';
        $code=1200;
        $this->ios_ys_costant();
        $this->ios_ys_send($desc,$code,$diver_phone);
        $this->android_ys_costant();
        $this->android_ys_send($title,$desc,$code,$diver_phone);
    }
    //过磅单审核通过
    function acceptBlance($driver_id){
        $diver_phone=M('users')->where(array('id'=>$driver_id))->getField('phone');
        $title = '确认成功';
        $desc = '成功,您提交的过磅单公司已经确认完成!';
        $code=1200;
        $this->ios_ys_costant();
        $this->ios_ys_send($desc,$code,$diver_phone);
        $this->android_ys_costant();
        $this->android_ys_send($title,$desc,$code,$diver_phone);
    }
    //提煤单买方扫描完成
    function finishBill($driver_id){
        $diver_phone=M('users')->where(array('id'=>$driver_id))->getField('phone');
        $title = '订单完成';
        $desc = '成功,您的货运订单已成功确认完成!';
        $code=1200;
        $this->ios_ys_costant();
        $this->ios_ys_send($desc,$code,$diver_phone);
        $this->android_ys_costant();
        $this->android_ys_send($title,$desc,$code,$diver_phone);
    }
    //司机退单时,通知公司管理员
    function noticeCompanyRebill($driver_id,$company_id){
        $diver_name=M('users')->where(array('id'=>$driver_id))->getField('real_name');
        $admin_phone=M('users')->where(array('company_id'=>$company_id,'is_admin'=>1))->getField('phone');
        $title = '司机退单';
        $desc = '['.$diver_name.']有一个退单申请,请相关人员登录PC后台处理!';
        $code=1200;
        $this->android_qy_costant();
        $this->android_qy_send($title,$desc,$code,$admin_phone);
        $this->ios_qy_costant();
        $this->ios_qy_send($desc,$code,$admin_phone);
    }

    // 通知公司管理员的通用方法
    function noticeManager($title, $message, $company_id,$code = 1200){
        $phone = M('users')->where(array('company_id' =>$company_id, 'is_admin' => 1))->getField('phone');
        $this->android_qy_costant();
        $this->android_qy_send($title,$message,$code,$phone);
        $this->ios_qy_costant();
        $this->ios_qy_send($message,$code,$phone);
    }

    //通知车主
    function noticeTrucker($title,$message, $trucker_id,$code =1200){
        $phone=M('users')->where(array('id'=>$trucker_id))->getField('phone');
        $this->android_ys_costant();
        $this->android_ys_send($title,$message,$code,$phone);
        $this->ios_ys_costant();
        $this->ios_ys_send($message,$code,$phone);
    }
    //通知司机
    function noticeDriver($title,$message, $driver_id,$code =1200){
        $phone=M('users')->where(array('id'=>$driver_id))->getField('phone');
        $this->android_ys_costant();
        $this->android_ys_send($title,$message,$code,$phone);
        $this->ios_ys_costant();
        $this->ios_ys_send($message,$code,$phone);
    }
    // 通知某个用户
    function noticeUser($title, $message, $phone){
        $type = M('users')->where(array('phone' => $phone))->getField('type');
        if ($type == 1) {
            // 运输版
            $this->android_ys_costant();
            $this->android_ys_send($title,$message,1200,$phone);
            $this->ios_ys_costant();
            $this->ios_ys_send($message,1200,$phone);
        } else {
            // 企业版
            $this->android_qy_costant();
            $this->android_qy_send($title,$message,1200,$phone);
            $this->ios_qy_costant();
            $this->ios_qy_send($message,1200,$phone);
        }
    }
    private function android_ys_send($title,$desc,$code,$phone,$type=1){
        $sender = new Sender();
        $message = new Builder();
        $message->title($title);  // 通知栏的title
        $message->description($desc); // 通知栏的descption
        $message->passThrough(0);  // 这是一条通知栏消息，如果需要透传，把这个参数设置成1,同时去掉title和descption两个参数
//        $message1->payload($payload); // 携带的数据，点击后将会通过客户端的receiver中的onReceiveMessage方法传入。
        $message->extra(Builder::notifyForeground, 1);
        $message->extra('code', $code);//传状态码
        if($type==1){
            $message->extra('sound_uri', "android.resource://com.jr.findcoal/raw/".C("NEW_NOTICE"));
            $message->extra('sound',C("NEW_NOTICE").'aif');
        }else{
            $message->extra('sound_uri', "android.resource://com.jr.findcoal/raw/".C("BILL_NOTICE"));
            $message->extra('sound',C("BILL_NOTICE").'.aif');
        }
        $message->notifyId(2); // 通知类型。最多支持0-4 5个取值范围，同样的类型的通知会互相覆盖，不同类型可以在通知栏并存
        $message->build();
        $sender->sendToUserAccount($message ,$phone,2)->getErrorCode();
    }
    private function android_qy_send($title,$desc,$code,$phone){
        $sender = new Sender();
        $message = new Builder();
        $message->title($title);  // 通知栏的title
        $message->description($desc); // 通知栏的descption
        $message->passThrough(0);  // 这是一条通知栏消息，如果需要透传，把这个参数设置成1,同时去掉title和descption两个参数
        $message->extra(Builder::notifyForeground, 1);
        $message->extra('code', $code);//传状态码
        $message->extra('sound_uri', "android.resource://com.ponkr.meiwenti_company/raw/".C("NEW_NOTICE"));
        $message->notifyId(2); // 通知类型。最多支持0-4 5个取值范围，同样的类型的通知会互相覆盖，不同类型可以在通知栏并存
        $message->build();
        $sender->sendToUserAccount($message ,$phone,2)->getErrorCode();
    }
    private function ios_ys_send($desc,$code,$phone,$type=1){
        $sender = new Sender();
        $message = new IOSBuilder();
        $message->description($desc); // 通知栏的descption
        $message->extra(Builder::notifyForeground, 1);
        $message->extra('code', $code);//传状态码
        if($type==1){
            $message->extra('sound',C("NEW_NOTICE").'.aif');
        }else{
            $message->extra('sound',C("BILL_NOTICE").'.aif');
        }
        $message->build();
        $sender->sendToUserAccount($message ,$phone,2)->getErrorCode();
    }
    private function ios_qy_send($desc,$code,$phone){
        $sender = new Sender();
        $message = new IOSBuilder();
        $message->description($desc); // 通知栏的descption
        $message->extra(Builder::notifyForeground, 1);
        $message->extra('code', $code);//传状态码
        $message->extra('sound',C("NEW_NOTICE").'.aif');
        $message->build();
        $sender->sendToUserAccount($message ,$phone,2)->getErrorCode();
    }

}