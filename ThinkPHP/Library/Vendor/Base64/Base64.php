<?php
/**
 * Created by PhpStorm.
 * User: fastdream
 * Date: 16/12/7
 * Time: 下午4:21
 */
class base64{
    public $base64_config = ['A','K','U','e','o','y','8','B','L','V','f','p','z','9','C','M','W','g','q','0','+','D','N','X','h','r','1','/','E','O','Y','i','s','2','F','P','Z','j','t','3','G','Q','a','k','u','4','H','R','b','l','v','5','I','S','c','m','w','6','J','T','d','n','x','7'];

    public function getBytes($string) {
        $data = iconv("UTF-8","GBK",$string);
        return unpack("C*",$data);
    }

    public function array_index($t){
        return array_search($t, $this->base64_config);
    }
    //解密
    public function decode($str){
        $str = str_replace("!","",$str);
        $slen = strlen($str);
        $mod = $slen%4;
        $num = floor($slen/4);
        $desc = [];
        for($i=0;$i<$num;$i++){
            $arr = array_map("base64::array_index",str_split(substr($str,$i*4,4)));
            $desc_0 = ($arr[0]<<2)|(($arr[1]&48)>>4);
            $desc_1 = (($arr[1]&15)<<4)|(($arr[2]&60)>>2);
            $desc_2 = (($arr[2]&3)<<6)|$arr[3];
            $desc = array_merge($desc,[$desc_0,$desc_1,$desc_2]);
        }
        if($mod == 0) return implode('', array_map("chr",$desc));
        $arr = array_map("base64::array_index", str_split(substr($str,$num*4,4)));
        if(count($arr) == 1) {
            $desc_0 = $arr[0]<<2;
            if($desc_0 != 0) $desc = array_merge($desc,[$desc_0]);
        }else if(count($arr) == 2) {
            $desc_0 = ($arr[0]<<2)|(($arr[1]&48)>>4);
            $desc = array_merge($desc,[$desc_0]);
        }else if(count($arr) == 3) {
            $desc_0 = ($arr[0]<<2)|(($arr[1]&48)>>4);
            $desc_1 = ($arr[1]<<4)|(($arr[2]&60)>>2);
            $desc = array_merge($desc,[$desc_0,$desc_1]);
        }
        return implode('', array_map("chr",$desc));
    }
    //加密
    public function encode($str){
        $byte_arr = $this->getBytes($str);
        $slen=count($byte_arr);
        $smod = ($slen%3);
        $snum = floor($slen/3);
        $desc = array();
        for($i=1;$i<=$snum;$i++){
            $index_num = ($i-1)*3;
            $_dec0= $byte_arr[$index_num+1]>>2;
            $_dec1= (($byte_arr[$index_num+1]&3)<<4)|($byte_arr[$index_num+2]>>4);
            $_dec2= (($byte_arr[$index_num+2]&0xF)<<2)|($byte_arr[$index_num+3]>>6);
            $_dec3= $byte_arr[$index_num+3]&63;
            $desc = array_merge($desc,array($this->base64_config[$_dec0],$this->base64_config[$_dec1],$this->base64_config[$_dec2],$this->base64_config[$_dec3]));
        }
        if($smod==0) return implode('',$desc);
        $n = ($snum*3)+1;
        $_dec0= $byte_arr[$n]>>2;
        ///只有一个字节
        if(!isset($byte_arr[$n+1])){
            $_dec1= (($byte_arr[$n]&3)<<4);
            $_dec2=$_dec3="!";
        }else{
        ///2个字节
            $_dec1= (($byte_arr[$n]&3)<<4)|($byte_arr[$n+1]>>4);
            $_dec2= $this->base64_config[($byte_arr[$n+1]&0xF)<<2];
            $_dec3="!";
        }
        $desc = array_merge($desc,array($this->base64_config[$_dec0],$this->base64_config[$_dec1],$_dec2,$_dec3));

        return implode('',$desc);
    }
}

