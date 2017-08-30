<?php 
return array(
	// 'SHOW_PAGE_TRACE' =>true,
    //session跨域
    'SESSION_OPTIONS'=>array(
        'domain'=>'.meiwenti.top',
        'expire'=>  24*3600*15,
    ),
    //session配置
    'COOKIE_DOMAIN'=>'.meiwenti.top',//cookie域名

    // 状态的集合
    // 1、bill_state
    'BILL_STATE' => array('待司机确认','司机已经确认','卖家已经已进行进矿扫描','卖家已经进行回批扫描',
        '买家已经进行进矿扫描','已经完成','物流公司收回','买卖双方收回','窜矿处理'),

    // 异常页面的模板文件
//    'TMPL_EXCEPTION_FILE'  =>  './Application/Vip/View/exception.html',




);


 
