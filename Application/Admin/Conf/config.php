<?php
return array(
	//'配置项'=>'配置值'
    //auth权限验证
    'AUTH_CONFIG'=>array(
        'AUTH_GROUP' => 'coal_admin_auth_group', //用户组数据表名
        'AUTH_GROUP_ACCESS' => 'coal_admin_auth_group_access', //用户组明细表
        'AUTH_RULE' => 'coal_admin_auth_rule', //权限规则表
        'AUTH_USER' => 'coal_admin'//用户信息表
    ),
    //'配置项'=>'配置值'
    //模板相关配置项
    //模板相关配置项
    'TMPL_PARSE_STRING'=>array(
    '__HPLUS__' =>"/Public/hplus",
    '__IPUBLIC__' =>"/".APP_PATH."Admin/Public",
    ),
);