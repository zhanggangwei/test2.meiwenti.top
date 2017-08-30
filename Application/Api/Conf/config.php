<?php
return array(
	//'配置项'=>'配置值'
    //模板相关配置项
    //模板相关配置项
    'TMPL_PARSE_STRING'=>array(
        '__HPLUS__' =>"/Public/hplus",
        '__IPUBLIC__' =>"/".APP_PATH."Api/Public",
    ),
    //模板变量输出
    'TMPL_L_DELIM'=>'{',
    'TMPL_R_DELIM'=>'}',
    //session跨域
    'SESSION_OPTIONS'=>array(
        'domain'=>'.meiwenti.top',
        'expire'=>  24*3600*15,
    ),
    //session配置
    'COOKIE_DOMAIN'=>'.meiwenti.top',//cookie域名
    //逻辑模型请求类型
    'POST_TYPE'=>'app',
);