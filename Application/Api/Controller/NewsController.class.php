<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/30
 * Time: 13:44
 * API公用控制器
 */
namespace Api\Controller;
use Think\Controller;
use OSS\OssClient;
use OSS\Core\OssException;
//加载上传类
use Think\Upload;
class NewsController extends ApiController {
    /*******************************************上传图片*******************************************************/
    function uploadImg(){
        vendor('OSS.autoload');
        //oss上传
        $bucketName = C('OSS_TEST_BUCKET');
        $ossClient = new OssClient(C('OSS_ACCESS_ID'), C('OSS_ACCESS_KEY'), C('OSS_ENDPOINT'), false);
        $web=C('OSS_WEB_SITE');
        //图片
        $fFiles=$_FILES['file'];
        if(in_array($fFiles['type'],array('video/mp4','video/rmvb','video/rm','video/mpeg','video/mov')))
             $img_folder='mwt_upfield/videos/'.date('Y-m-d');
        else $img_folder='mwt_upfield/images/'.date('Y-m-d');

        $rs=ossUpPic($fFiles,$img_folder,$ossClient,$bucketName,$web,0);

        if($rs['code']==1){
            //图片
            $img = $rs['msg'];
            //如返回里面有缩略图：
            $thumb=$rs['thumb'];
            $res['code']=1;
            $res['img_src']=$img;
            $res['statusCode']=200;
            $res['filename']=$img;
            backJson($res);
        }else{
            $res['code']=0;
            $res['message']='图片有误';
            backJson($res);
        }
    }
    /*************************************************验证码***************************************************/
    public function verify(){
        $Verify = new \Think\Verify();
        $Verify->fontSize = 15;
        $Verify->imageW = 0;
        $Verify->imageH = 0;
        $Verify->useCurve = false;
        $Verify->useNoise = false;
        $Verify->length = 4;
        $Verify->codeSet = '0123456789';
        // $Verify->fontttf = 'simfang.ttf';
        // $Verify->useZh = true;
        $Verify->entry();
    }
}