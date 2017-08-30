<?php
$config=array(
    //'配置项'=>'配置值'
    'URL_MODEL' => 2,
    // 数据库
    'DB_TYPE'    => 'mysql',
//	'DB_HOST'    => 'rm-2zepex5z822a4680g.mysql.rds.aliyuncs.com', //正式上线数据库地址
//    'DB_HOST'    => 'rm-2zepex5z822a4680g.mysql.rds.aliyuncs.com', //测试上线数据库地址
//    'DB_NAME'    => 'mwt',
//    'DB_USER'    => 'zgw',
//    'DB_PWD'     => 'XXoo1314',
    // 'DB_HOST' => 'localhost',
    // 'DB_NAME'    => 'mwt',
    // 'DB_USER'    => 'root',
    // 'DB_PWD'     => 'root',
    'DB_PORT'    => '3306',
    'DB_PREFIX'  => 'coal_',
    'DB_CHARSET' => 'UTF8',
    //模板标签
    'TMPL_L_DELIM'=>'<{',
    'TMPL_R_DELIM'=>'}>',
    //目录太深
    'TMPL_FILE_DEPR'=>'_',
    //默认访问VIP模块
    'DEFAULT_MODULE' => 'Vip',
    //密钥
    'CODE_KEY' => 'mwt2.0',

    //auth权限验证
    'AUTH_CONFIG'=>array(
        'AUTH_ON' => true, //认证开关
        'AUTH_TYPE' => 1, // 认证方式，1为实时认证；2为登录认证。
        'AUTH_GROUP' => 'coal_auth_group', //用户组数据表名
        'AUTH_GROUP_ACCESS' => 'coal_auth_group_access', //用户组明细表
        'AUTH_RULE' => 'coal_auth_rule', //权限规则表
        'AUTH_USER' => 'coal_users'//用户信息表
    ),
    //session 驱动
    // 'SESSION_TYPE' => 'Db',
    // 'SESSION_DB_NAME'    => 'mwt',
    'SESSION_OPTIONS'=> array(
        'expire'              =>  24*3600*15,
    ),
//    'SESSION_TYPE' => 'redis', //session保存类型
//    'SESSION_PREFIX' => 'sess_', //session前缀
//    'REDIS_HOST' => '127.0.0.1', //REDIS服务器地址
//    'REDIS_PORT' => 6379, //REDIS连接端口号
//     'SESSION_EXPIRE' => 24*3600*15, //SESSION过期时间

    //阿里上传配置
    'OSS'=>array(
        'AccessKeyId'=>'LTAIJlt9kIQ8NkUN',
        'AccessKeySecret'=>'Gy1bWWtVABmOjDCCHI0sVus1K56xSV',
        'endpoint'=>'oss-cn-beijing.aliyuncs.com',
        'bucket'=>'mwt-img',
    ),


    //oss配置
    "OSS_ACCESS_ID" => 'LTAIJlt9kIQ8NkUN',
    "OSS_ACCESS_KEY"=> 'Gy1bWWtVABmOjDCCHI0sVus1K56xSV',
    "OSS_ENDPOINT"  => 'oss-cn-beijing.aliyuncs.com',
    "OSS_TEST_BUCKET" => 'mwt-img',
    "OSS_WEB_SITE" =>'mwt-img.oss-cn-beijing.aliyuncs.com',    //上面4个就不用介绍了，这个OSS_WEB_SITE是oss的bucket创建后的外网访问地址，如需二级域名，可以指向二级域名，具体可以参考阿里云控制台里面的oss
    "OSS_WEB_URL" =>'https://mwt-img.oss-cn-beijing.aliyuncs.com/',

    //oss文件上传配置
    'oss_maxSize'=>10485760,    //1oM
    'oss_exts'   =>array(// 设置附件上传类型
        'image/jpg',
        'image/gif',
        'image/png',
        'image/jpeg',
        'video/mp4',
        'video/rmvb',
        'video/rm',
        'video/mpeg',
        'video/mov',
        'application/octet-stream',//阿里云好像都是通过二进制上传，似乎上面4个后缀设置起到什么用？
    ),
    //自定义success和error的提示页面模板
    'TMPL_ACTION_SUCCESS'=>'Success_jump',
    'TMPL_ACTION_ERROR'=>'Error_jump',
    //'配置项'=>'配置值'
    //模板相关配置项
    //模板相关配置项
    'TMPL_PARSE_STRING'=>array(
        '__HPLUS__' =>"/Public/hplus",
    ),
    //逻辑模型请求类型
    'POST_TYPE'=>'web',
    //提示音名称
    'BILL_NOTICE'=>'bill',
    'NEW_NOTICE'=>'message',
    'HTTP_TYPE' => 'https://',//http协议类型，用于API输出图像

    'LOG_RECORD'            =>  false,  // 进行日志记录
    'LOG_EXCEPTION_RECORD'  =>  true,    // 是否记录异常信息日志
    'LOG_LEVEL'             =>  '',  // 允许记录的日志级别
    'DB_FIELDS_CACHE'       =>  false, // 字段缓存信息
    'DB_DEBUG'				=>  false,
);

if($_SERVER['HTTP_HOST']=='test.meiwenti.top'){
    $config['DB_HOST']='localhost';
    $config['DB_NAME']='mwt';
    $config['DB_USER']='root';
    $config['DB_PWD']='mwt123';
}
if($_SERVER['HTTP_HOST']=='www.mwt.com'){
    $config['DB_HOST']='localhost';
    $config['DB_NAME']='mwt';
    $config['DB_USER']='root';
    $config['DB_PWD']='root';
}
if($_SERVER['HTTP_HOST']=='test2.meiwenti.top'){
    $config['DB_HOST']='localhost';
    $config['DB_NAME']='mwt';
    $config['DB_USER']='root';
    $config['DB_PWD']='mwt123';
}
return $config;