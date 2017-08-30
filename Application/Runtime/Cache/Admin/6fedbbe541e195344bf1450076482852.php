<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>煤问题总后台管理系统</title>
<!-- <meta name="Keywords" content="B-JUI,Bootstrap,jquery,ui,前端,框架,开源,OSC,开源框架,knaan"/>
<meta name="Description" content="B-JUI(Best jQuery UI)前端管理框架。轻松开发，专注您的业务，从B-JUI开始！"/>  -->
<!-- bootstrap - css -->
<link href="/Public/B-JUI/themes/css/bootstrap.css" rel="stylesheet">
<!-- core - css -->
<link href="/Public/B-JUI/themes/css/style.css" rel="stylesheet">
<link href="/Public/B-JUI/themes/blue/core.css" id="bjui-link-theme" rel="stylesheet">
<link href="/Public/B-JUI/themes/css/fontsize.css" id="bjui-link-theme" rel="stylesheet">
<!-- plug - css -->
<link href="/Public/B-JUI/plugins/kindeditor_4.1.11/themes/default/default.css" rel="stylesheet">
<link href="/Public/B-JUI/plugins/colorpicker/css/bootstrap-colorpicker.min.css" rel="stylesheet">
<link href="/Public/B-JUI/plugins/nice-validator-1.0.7/jquery.validator.css" rel="stylesheet">
<link href="/Public/B-JUI/plugins/bootstrapSelect/bootstrap-select.css" rel="stylesheet">
<link href="/Public/B-JUI/plugins/webuploader/webuploader.css" rel="stylesheet">
<link href="/Public/B-JUI/themes/css/FA/css/font-awesome.min.css" rel="stylesheet">
<!-- Favicons -->
<link rel="apple-touch-icon-precomposed" href="/Public/assets/ico/apple-touch-icon-precomposed.png">
<link rel="shortcut icon" href="/Public/assets/ico/favicon.png">
<!--[if lte IE 7]>
<link href="/Public/B-JUI/themes/css/ie7.css" rel="stylesheet">
<![endif]-->
<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lte IE 9]>
    <script src="/Public/B-JUI/other/html5shiv.min.js"></script>
    <script src="/Public/B-JUI/other/respond.min.js"></script>
<![endif]-->
<!-- jquery -->
<script src="/Public/B-JUI/js/jquery-1.11.3.min.js"></script>
<script src="/Public/B-JUI/js/jquery.cookie.js"></script>
<!--[if lte IE 9]>
<script src="/Public/B-JUI/other/jquery.iframe-transport.js"></script>
<![endif]-->
<!-- B-JUI -->
<script src="/Public/B-JUI/js/bjui-all.min.js"></script>
<!-- plugins -->
<!-- swfupload for kindeditor -->
<script src="/Public/B-JUI/plugins/swfupload/swfupload.js"></script>
<!--   -->
<script src="/Public/B-JUI/plugins/webuploader/webuploader.js"></script>
<!-- kindeditor -->
<script src="/Public/B-JUI/plugins/kindeditor_4.1.11/kindeditor-all-min.js"></script>
<script src="/Public/B-JUI/plugins/kindeditor_4.1.11/lang/zh-CN.js"></script>
<!-- colorpicker -->
<script src="/Public/B-JUI/plugins/colorpicker/js/bootstrap-colorpicker.min.js"></script>
<!-- ztree -->
<script src="/Public/B-JUI/plugins/ztree/jquery.ztree.all-3.5.js"></script>
<!-- nice validate -->
<script src="/Public/B-JUI/plugins/nice-validator-1.0.7/jquery.validator.js"></script>
<script src="/Public/B-JUI/plugins/nice-validator-1.0.7/jquery.validator.themes.js"></script>
<!-- bootstrap plugins -->
<script src="/Public/B-JUI/plugins/bootstrap.min.js"></script>
<script src="/Public/B-JUI/plugins/bootstrapSelect/bootstrap-select.min.js"></script>
<script src="/Public/B-JUI/plugins/bootstrapSelect/defaults-zh_CN.min.js"></script>
<!-- icheck -->
<script src="/Public/B-JUI/plugins/icheck/icheck.min.js"></script>
<!-- HighCharts -->
<script src="/Public/B-JUI/plugins/highcharts/highcharts.js"></script>
<script src="/Public/B-JUI/plugins/highcharts/highcharts-3d.js"></script>
<script src="/Public/B-JUI/plugins/highcharts/themes/gray.js"></script>
<!-- other plugins -->
<script src="/Public/B-JUI/plugins/other/jquery.autosize.js"></script>
<link href="/Public/B-JUI/plugins/uploadify/css/uploadify.css" rel="stylesheet">
<script src="/Public/B-JUI/plugins/uploadify/scripts/jquery.uploadify.min.js"></script>
<script src="/Public/B-JUI/plugins/download/jquery.fileDownload.js"></script>
    <script type="text/javascript" src='//webapi.amap.com/maps?v=1.3&key=b6dd246b41204b3b71067d3c3e8d52f0'></script>

    <script src="//webapi.amap.com/ui/1.0/main.js?v=1.0.6"></script>
    <script type="text/javascript" src="//webapi.amap.com/demos/js/liteToolbar.js?v=1.0.6"></script>
<!-- init -->
<script type="text/javascript">
$(function() {
    BJUI.init({
        JSPATH       : '/Public/B-JUI/',         //[可选]框架路径
        PLUGINPATH   : '/Public/B-JUI/plugins/', //[可选]插件路径
        loginInfo    : {url:"<?php echo U('Login/login');?>", title:'登录', width:540, height:640}, // 会话超时后弹出登录对话框
        statusCode   : {ok:200, error:300, timeout:301}, //[可选]
        ajaxTimeout  : 300000, //[可选]全局Ajax请求超时时间(毫秒)
        alertTimeout : 3000,  //[可选]信息提示[info/correct]自动关闭延时(毫秒)
        pageInfo     : {total:'totalRow', pageCurrent:'pageCurrent', pageSize:'pageSize', orderField:'orderField', orderDirection:'orderDirection'}, //[可选]分页参数
        keys         : {statusCode:'statusCode', message:'message'}, //[可选]
        ui           : {
                         sidenavWidth     : 220,
                         showSlidebar     : false, //[可选]左侧导航栏锁定/隐藏
                         overwriteHomeTab : false //[可选]当打开一个未定义id的navtab时，是否可以覆盖主navtab(我的主页)
                       },
        debug        : true,    // [可选]调试模式 [true|false，默认false]
        theme        : 'green' // 若有Cookie['bjui_theme'],优先选择Cookie['bjui_theme']。皮肤[五种皮肤:default, orange, purple, blue, red, green]
    })
    //时钟
    var today = new Date(), time = today.getTime()
    $('#bjui-date').html(today.formatDate('yyyy/MM/dd'))
    setInterval(function() {
        today = new Date(today.setSeconds(today.getSeconds() + 1))
        $('#bjui-clock').html(today.formatDate('HH:mm:ss'))
    }, 1000)
})

/*window.onbeforeunload = function(){
    return "确定要关闭本系统 ?";
}*/

//菜单-事件-zTree
function MainMenuClick(event, treeId, treeNode) {
    if (treeNode.target && treeNode.target == 'dialog' || treeNode.target == 'navtab')
        event.preventDefault()
    
    if (treeNode.isParent) {
        var zTree = $.fn.zTree.getZTreeObj(treeId)
        
        zTree.expandNode(treeNode)
        return
    }
    
    if (treeNode.target && treeNode.target == 'dialog')
        $(event.target).dialog({id:treeNode.targetid, url:treeNode.url, title:treeNode.name})
    else if (treeNode.target && treeNode.target == 'navtab')
        $(event.target).navtab({id:treeNode.targetid, url:treeNode.url, title:treeNode.name, fresh:treeNode.fresh, external:treeNode.external})
}

// 满屏开关
var bjui_index_container = 'container_fluid'

function bjui_index_exchange() {
    bjui_index_container = bjui_index_container == 'container_fluid' ? 'container' : 'container_fluid'
    
    $('#bjui-top').find('> div').attr('class', bjui_index_container)
    $('#bjui-navbar').find('> div').attr('class', bjui_index_container)
    $('#bjui-body-box').find('> div').attr('class', bjui_index_container)
}
</script>
<!-- highlight && ZeroClipboard -->
<link href="/Public/assets/prettify.css" rel="stylesheet">
<script src="/Public/assets/prettify.js"></script>
<link href="/Public/assets/ZeroClipboard.css" rel="stylesheet">
<script src="/Public/assets/ZeroClipboard.js"></script>

<!-- zgw maintab css -->
<link href="/Public/hplus/js/plugins/UEditor/themes/default/css/ueditor.css" rel="stylesheet">
<style>
    .bjui-pageContent .article {
        width:300px;
        height:150px;
        background-color:#ff9900;
        -moz-box-shadow: 10px 10px 5px #888888; /* 老的 Firefox */
        box-shadow: 10px 10px 5px #888888;
    }
    
    .article h3 {
        padding-top: 10px;
        text-align: center;
        vertical-align: center;
        font-family: Arial,'微软雅黑';

    }

    .article p {
        padding: 10px 50px;
    }

    .surprise {
        margin: 30px;
    }
</style>
</head>
<body>
    <!--[if lte IE 7]>
        <div id="errorie"><div>您还在使用老掉牙的IE，正常使用系统前请升级您的浏览器到 IE8以上版本 <a target="_blank" href="http://windows.microsoft.com/zh-cn/internet-explorer/ie-8-worldwide-languages">点击升级</a>&nbsp;&nbsp;强烈建议您更改换浏览器：<a href="http://down.tech.sina.com.cn/content/40975.html" target="_blank">谷歌 Chrome</a></div></div>
    <![endif]-->
    <div id="bjui-top" class="bjui-header">
        <div class="container_fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapsenavbar" data-target="#bjui-top-collapse" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
            <nav class="collapse navbar-collapse" id="bjui-top-collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li class="datetime"><a><span id="bjui-date">0000/00/00</span> <span id="bjui-clock">00:00:00</span></a></li>
                    <li><a href="#">账号：<?php echo (session('sys_account')); ?></a></li>
                    <li><a href="#">角色：<?php echo (session('sys_rolename')); ?></a></li>
                    <li><a href="<?php echo U('Manager/changepassword');?>" data-toggle="dialog" data-id="sys_user_changepass" data-mask="true" data-width="400" data-height="300">修改密码</a></li>

                    <li><a onclick="window.open ('https://api2.meiwenti.top/web-im/', 'newwindow', 'height=740, width=1140, top=50%, left=50%, toolbar=no, menubar=no, scrollbars=no, resizable=yes,location=no, status=no')  ">客服系统</a></li>

                    <li><a href="<?php echo U('Login/logout');?>" style="font-weight:bold;">&nbsp;<i class="fa fa-power-off"></i> 注销登陆</a></li>
                    <li class="dropdown"><a href="#" class="dropdown-toggle bjui-fonts-tit" data-toggle="dropdown" title="更改字号"><i class="fa fa-font"></i> 大</a>
                        <ul class="dropdown-menu" role="menu" id="bjui-fonts">
                            <li><a href="javascript:;" class="bjui-font-a" data-toggle="fonts"><i class="fa fa-font"></i> 特大</a></li>
                            <li><a href="javascript:;" class="bjui-font-b" data-toggle="fonts"><i class="fa fa-font"></i> 大</a></li>
                            <li><a href="javascript:;" class="bjui-font-c" data-toggle="fonts"><i class="fa fa-font"></i> 中</a></li>
                            <li><a href="javascript:;" class="bjui-font-d" data-toggle="fonts"><i class="fa fa-font"></i> 小</a></li>
                        </ul>
                    </li>
                    <li class="dropdown active"><a href="#" class="dropdown-toggle theme" data-toggle="dropdown" title="切换皮肤"><i class="fa fa-tree"></i></a>
                        <ul class="dropdown-menu" role="menu" id="bjui-themes">
                            <!--
                            <li><a href="javascript:;" class="theme_default" data-toggle="theme" data-theme="default">&nbsp;<i class="fa fa-tree"></i> 黑白分明&nbsp;&nbsp;</a></li>
                            <li><a href="javascript:;" class="theme_orange" data-toggle="theme" data-theme="orange">&nbsp;<i class="fa fa-tree"></i> 橘子红了</a></li>
                            -->
                            <li><a href="javascript:;" class="theme_purple" data-toggle="theme" data-theme="purple">&nbsp;<i class="fa fa-tree"></i> 紫罗兰</a></li>
                            <li class="active"><a href="javascript:;" class="theme_blue" data-toggle="theme" data-theme="blue">&nbsp;<i class="fa fa-tree"></i> 天空蓝</a></li>
                            <li><a href="javascript:;" class="theme_green" data-toggle="theme" data-theme="green">&nbsp;<i class="fa fa-tree"></i> 绿草如茵</a></li>
                        </ul>
                    </li>
                    <li><a href="javascript:;" onclick="bjui_index_exchange()" title="横向收缩/充满屏幕"><i class="fa fa-exchange"></i></a></li>
                </ul>
            </nav>
        </div>
    </div>
    <header class="navbar bjui-header" id="bjui-navbar">
        <div class="container_fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" id="bjui-navbar-collapsebtn" data-toggle="collapsenavbar" data-target="#bjui-navbar-collapse" aria-expanded="false">
                    <i class="fa fa-angle-double-right"></i>
                </button>
                <a class="navbar-brand" href="http://b-jui.com"><img src="/Public/images/logo.png" height="30"></a>
            </div>
            <nav class="collapse navbar-collapse" id="bjui-navbar-collapse">
                <ul class="nav navbar-nav navbar-right" id="bjui-hnav-navbar">
                    <?php if(is_array($menu1)): foreach($menu1 as $key=>$vo): ?><li>
                            <a href="<?php echo U('menu',array('id'=>$vo['id']),'');?>" data-toggle="sidenav" data-id-key="targetid"><?php echo ($vo["name"]); ?></a>
                        </li><?php endforeach; endif; ?>
                    <script>
                        $('#bjui-hnav-navbar li a').eq(0).click();
                    </script>
                    <!--<li class="active">-->
                        <!--<a href="<?php echo U('Home/Index/menu');?>" data-toggle="sidenav" data-id-key="targetid">文章管理</a>-->
                    <!--</li>-->
                    <!-- <li>
                        <a href="#" data-toggle="dialog" data-id-key="targetid">相册</a>
                    </li> -->
                    <!--<li>-->
                        <!--<a href="javascript:;" data-toggle="dialog" data-options="{id:'login',url:'./html/login.html',title:'登录页面'}">登录</a>-->
                    <!--</li>-->
                </ul>
            </nav>
        </div>
    </header>
    <div id="bjui-body-box">
        <div class="container_fluid" id="bjui-body">
            <div id="bjui-sidenav-col">
                <div id="bjui-sidenav">
                    <div id="bjui-sidenav-arrow" data-toggle="tooltip" data-placement="left" data-title="隐藏左侧菜单">
                        <i class="fa fa-long-arrow-left"></i>
                    </div>
                    <div id="bjui-sidenav-box">
                        
                    </div>
                </div>
            </div>
            <div id="bjui-navtab" class="tabsPage">
                <div id="bjui-sidenav-btn" data-toggle="tooltip" data-title="显示左侧菜单" data-placement="right">
                    <i class="fa fa-bars"></i>
                </div>
                <div class="tabsPageHeader">
                    <div class="tabsPageHeaderContent">
                        <ul class="navtab-tab nav nav-tabs">
                            <li><a href="javascript:;"><span><i class="fa fa-home"></i> #maintab#</span></a></li>
                        </ul>
                    </div>
                    <div class="tabsLeft"><i class="fa fa-angle-double-left"></i></div>
                    <div class="tabsRight"><i class="fa fa-angle-double-right"></i></div>
                    <div class="tabsMore"><i class="fa fa-angle-double-down"></i></div>
                </div>
                <ul class="tabsMoreList">
                    <li><a href="javascript:;">#maintab#</a></li>
                </ul>
                <div class="navtab-panel tabsPageContent">
                    <div class="navtabPage unitBox">
                        <div class="bjui-pageContent" style="padding: 0 10px 0 0">
                            <div class="row">
                                <!--用户统计信息-->
                                <div class="ibox float-e-margins col-sm-3">
                                    <div class="ibox-title">
                                        <h5>用户统计信息</h5>
                                    </div>
                                    <div class="ibox-content">
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th>账号类型</th>
                                                <th>数量</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>未认证用户</td>
                                                <td><?php echo ($num["unauthorized"]); ?></td>
                                            </tr>
                                            <tr>
                                                <td>认证用户</td>
                                                <td><?php echo ($num["authorized"]); ?></td>
                                            </tr>
                                            <tr>
                                                <td>个人车主</td>
                                                <td><?php echo ($num["trucker_truck"]); ?></td>
                                            </tr>
                                            <tr>
                                                <td>公司车主</td>
                                                <td><?php echo ($num["coo_company_truck"]); ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!--车辆统计信息-->
                                <div class="ibox float-e-margins col-sm-3">
                                    <div class="ibox-title">
                                        <h5>车辆统计信息</h5>
                                    </div>
                                    <div class="ibox-content">
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th>车辆状态</th>
                                                <th>数量</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>空车</td>
                                                <td><?php echo ($num["truck_state1"]); ?></td>
                                            </tr>
                                            <tr>
                                                <td>待接单</td>
                                                <td><?php echo ($num["truck_state2"]); ?></td>
                                            </tr>
                                            <tr>
                                                <td>在路上</td>
                                                <td><?php echo ($num["truck_state3"]); ?></td>
                                            </tr>
                                            <tr>
                                                <td>故障车</td>
                                                <td><?php echo ($num["truck_state4"]); ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!--司机统计信息-->
                                <div class="ibox float-e-margins col-sm-3">
                                    <div class="ibox-title">
                                        <h5>司机统计信息</h5>
                                    </div>
                                    <div class="ibox-content">
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th>账号状态</th>
                                                <th>数量</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>待审核司机</td>
                                                <td><?php echo ($num["driver0"]); ?></td>
                                            </tr>
                                            <tr>
                                                <td>认证司机</td>
                                                <td><?php echo ($num["driver1"]); ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!--公司统计信息-->
                                <div class="ibox float-e-margins col-sm-3">
                                    <div class="ibox-title">
                                        <h5>公司统计信息</h5>
                                    </div>
                                    <div class="ibox-content">
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th>账号状态</th>
                                                <th>数量</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>待认领公司</td>
                                                <td><?php echo ($num["company2"]); ?></td>
                                            </tr>
                                            <tr>
                                                <td>待审核公司</td>
                                                <td><?php echo ($num["company0"]); ?></td>
                                            </tr>
                                            <tr>
                                                <td>已审核公司</td>
                                                <td><?php echo ($num["company1"]); ?></td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <!--待处理信息-->
                                <div class="col-sm-6">
                                    <div class="ibox float-e-margins">
                                        <div class="ibox-title">
                                            <h5>待处理信息</h5>
                                        </div>
                                        <div class="ibox-content">
                                            <table class="table table-bordered ">
                                                <thead>
                                                <tr>
                                                    <th>账号类型</th>
                                                    <th>待审数量</th>
                                                    <th>已审数量</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr>
                                                    <td>公司</td>
                                                    <td><?php echo ($num["company0"]); ?></td>
                                                    <td><?php echo ($num["company1"]); ?></td>
                                                </tr>
                                                <tr>
                                                    <td>车主</td>
                                                    <td><?php echo ($num["trucker0"]); ?></td>
                                                    <td><?php echo ($num["trucker1"]); ?></td>
                                                </tr>
                                                <tr>
                                                    <td>司机</td>
                                                    <td><?php echo ($num["driver0"]); ?></td>
                                                    <td><?php echo ($num["driver1"]); ?></td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                                <!--发布信息统计-->
                                <div class="col-sm-6">
                                    <div class="ibox float-e-margins">
                                        <div class="ibox-title">
                                            <h5>发布信息统计</h5>
                                        </div>
                                        <div class="ibox-content">

                                            <table class="table table-bordered ">
                                                <thead>
                                                <tr>
                                                    <th>信息类型</th>
                                                    <th>待审数量</th>
                                                    <th>已审数量</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <!--<tr>-->
                                                    <!--<td>长途信息</td>-->
                                                    <!--<td><?php echo ($num["long_haul0"]); ?></td>-->
                                                    <!--<td><?php echo ($num["long_haul1"]); ?></td>-->
                                                <!--</tr>-->
                                                <!--<tr>-->
                                                    <!--<td>火运信息</td>-->
                                                    <!--<td><?php echo ($num["train0"]); ?></td>-->
                                                    <!--<td><?php echo ($num["train1"]); ?></td>-->
                                                <!--</tr>-->
                                                <!--<tr>-->
                                                    <!--<td>船运信息</td>-->
                                                    <!--<td><?php echo ($num["ship0"]); ?></td>-->
                                                    <!--<td><?php echo ($num["ship1"]); ?></td>-->
                                                <!--</tr>-->
                                                <tr>
                                                    <td>招聘信息</td>
                                                    <td><?php echo ($num["recruit1"]); ?></td>
                                                    <td><?php echo ($num["recruit0"]); ?></td>
                                                </tr>
                                                <tr>
                                                    <td>求职信息</td>
                                                    <td><?php echo ($num["apply1"]); ?></td>
                                                    <td><?php echo ($num["apply0"]); ?></td>
                                                </tr>
                                                <tr>
                                                    <td>货源信息</td>
                                                    <td><?php echo ($num["logistics_goods1"]); ?></td>
                                                    <td><?php echo ($num["logistics_goods0"]); ?></td>
                                                </tr>
                                                <tr>
                                                    <td>货源承运车主待放空处理</td>
                                                    <td><?php echo ($num["empty_truck5"]); ?></td>
                                                    <td><?php echo ($num["empty_truck67"]); ?></td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <!-- 登录信息 -->
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="ibox float-e-margins">
                                        <div class="ibox-title">
                                            <h5>登录信息</h5>
                                        </div>
                                        <div class="ibox-content">
                                            <div class="well">
                                                <h3>
                                                    欢迎使用煤问题管理后台
                                                </h3> 您上次退出时间为：<?php echo ($loginout_time); ?>。
                                            </div>
                                            <div class="well well-lg">
                                                <h3>
                                                    版权信息：
                                                </h3> 深圳磐客信息科技有限公司Copyright © 2017-2018
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- 站长统计 -->
                            <script type="text/javascript">var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1262381038'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s19.cnzz.com/z_stat.php%3Fid%3D1262381038%26show%3Dpic2' type='text/javascript'%3E%3C/script%3E"));</script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="/Public/B-JUI/other/ie10-viewport-bug-workaround.js"></script>

</body>
</html>