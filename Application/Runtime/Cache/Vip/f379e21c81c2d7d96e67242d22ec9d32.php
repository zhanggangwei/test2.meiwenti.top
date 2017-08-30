<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<title>煤问题PC端管理系统</title>

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
<!-- Webuploader -->
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

<script type="text/javascript" src="//webapi.amap.com/maps?v=1.3&key=b6dd246b41204b3b71067d3c3e8d52f0"></script>

<script src="//webapi.amap.com/ui/1.0/main.js?v=1.0.6"></script>
<script type="text/javascript" src="//webapi.amap.com/demos/js/liteToolbar.js?v=1.0.6"></script>
<!-- init -->
<script type="text/javascript">
$(function() {
    BJUI.init({
        JSPATH       : '/Public/B-JUI/',         //[可选]框架路径
        PLUGINPATH   : '/Public/B-JUI/plugins/', //[可选]插件路径
        loginInfo    : {url:'<?php echo U('Login/login');?>', title:'登录', width:440, height:300}, // 会话超时后弹出登录对话框
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

</script>
<!-- highlight && ZeroClipboard -->
<link href="/Public/assets/prettify.css" rel="stylesheet">
<script src="/Public/assets/prettify.js"></script>
<link href="/Public/assets/ZeroClipboard.css" rel="stylesheet">
<script src="/Public/assets/ZeroClipboard.js"></script>

<!-- zgw maintab css -->
<style>

    ul.wl-index{
        list-style: none;
    }
    ul.wl-index li{float: left;
        background-color: #ffffff;
        -webkit-border-image: none;
        -o-border-image: none;
        border-image: none;
        border:1px solid #e7eaec;
        color: inherit;
        margin-bottom: 0;
        padding: 14px 15px 7px;
        min-height: 48px;
        width: 14%;
        min-width: 176px;
        margin-right:10pt;
        border-radius: 10pt;
        -moz-box-shadow: 5px 5px 5px #1f73b6; /* 老的 Firefox */
        box-shadow: 5px 5px 5px #1f73b6;
        /*transform:rotateY(0deg);*/
        /*transition: transform 3s;*/
        height:140px;
        margin-top: 10px;
    }
    ul.wl-index h3{
        border-bottom:1px solid #DDDDDD;
        padding-bottom: 10pt;
    }

    #container_main {
        position: absolute;
        top: 200px;
        left: 0;
        right: 0;
        bottom: 0;
        width: 100%;
        height: 100%;
    }

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
    .info-tip {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 12px;
        background-color: #fff;
        height: 35px;
        text-align: left;
    }
</style>

    <link rel="stylesheet" href="/Public/mwt/css/cover_style.css">
</head>
<body>
    <!--[if lte IE 7]>
        <div id="errorie"><div>您还在使用老掉牙的IE，正常使用系统前请升级您的浏览器到 IE8以上版本 <a target="_blank" href="http://windows.microsoft.com/zh-cn/internet-explorer/ie-8-worldwide-languages">点击升级</a>&nbsp;&nbsp;强烈建议您更改换浏览器：<a href="http://down.tech.sina.com.cn/content/40975.html" target="_blank">谷歌 Chrome</a></div></div>
    <![endif]-->

    <header class="navbar bjui-header" id="bjui-navbar">
        <div class="container_fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" id="bjui-navbar-collapsebtn" data-toggle="collapsenavbar" data-target="#bjui-navbar-collapse" aria-expanded="false">
                    <i class="fa fa-angle-double-right"></i>
                </button>
                 <img src="/Public/images/logo.png" />
                <a href="<?php echo U('Vip/Index/index');?>" style="font-size: 20px;color: #fff;"><?php echo (session('company_name')); ?></a>
            </div>
            <nav class="collapse navbar-collapse" id="bjui-navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li class="datetime"><a><span id="bjui-date">0000/00/00</span> <span id="bjui-clock">00:00:00</span></a></li>
                    <li><a href="<?php echo U('Vip/Company/editCompanyInfo');?>" title="修改信息" data-toggle="navtab" data-options="{id:'index_edit_company_info',width:'800pt',height:'600pt',title:'修改信息'}">账号：<?php echo (session('account')); ?></a></li>
                    <li><a href="<?php echo U('Vip/Company/editCompanyInfo');?>" title="修改信息" data-toggle="navtab" data-options="{id:'index_edit_company_info',width:'800pt',height:'600pt',title:'修改信息'}">修改信息</a></li>
                    <?php if(!empty($_SESSION['rolename'])): ?><li><a href="#">角色：<?php echo (session('rolename')); ?></a></li><?php endif; ?>
                    <li><a href="<?php echo U('Login/changepassword');?>" data-toggle="dialog" data-id="sys_user_changepass" data-mask="true" data-width="400" data-height="300">修改密码</a></li>
                    <li><a href="<?php echo U('Login/logout');?>" style="font-weight:bold;">&nbsp;<i class="fa fa-power-off"></i> 注销登陆</a></li>
                    <li style="background-color: red;"><a class="" href="javascript:;" onClick="return alertmsg_test();" title="菜单切换"><i class="fa fa-exchange"></i></a></li>
                    <script type="text/javascript">
                        function alertmsg_test() {
                            BJUI.alertmsg('confirm', '确定要切换到<?php if($menu_type == 1): ?>物流管理页面<?php else: ?>贸易商管理页面<?php endif; ?>吗？', {
                                okCall: function() {
                                    location.href='<?php echo U('Vip/Index/setDefMenu',array('type'=>$menu_type==1?2:1));?>';
                                },
                                cancelCall:function(){
                                    return false;
                                }
                            })
                        }
                    </script>

                </ul>
                <ul class="nav navbar-nav navbar-right" id="bjui-hnav-navbar">
                    <script>
                        $('#bjui-hnav-navbar li a').eq(0).click();
                    </script>
                    <li class="active" style="display: none">
                        <a href="<?php echo U('Vip/Index/menu/menu_type/'.$menu_type);?>" data-toggle="sidenav" data-id-key="targetid">文章管理</a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>
    <div id="bjui-body-box">
        <div class="container_fluid" id="bjui-body">
            <div id="bjui-sidenav-col">
                <?php if($menu_type == 1): ?><h4>贸易菜单</h4>
                <?php else: ?>
                    <h4>物流菜单</h4><?php endif; ?>
                <div id="bjui-sidenav" style="padding-top: 10px">
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
                        <div class="bjui-pageContent" style="width: 100%;padding-right: 20px">
                            <?php if($menu_type == 1): ?><!--贸易商首页  start-->
                                <?php if ($data['code']==1) { ?>
                                <ul class="wl-index" id="dynamic-data2">
                                    <li><h3>空车数量</h3><span><?php echo ($data["kc_count"]); ?></span>车</li>
                                    <li><h3>重车数量</h3><span><?php echo ($data["zc_count"]); ?></span>车</li>
                                    <li><h3>总计</h3><span><?php echo ($data['zc_count']+$data['kc_count']); ?></span>车</li>
                                    <li><h3>在途来站车</h3><span>0</span>车</li>
                                    <li><h3>在途离站车</h3><span>0</span>车</li>
                                </ul>
                                <div id="container_main"></div>
                                <!--<div class="button-group">-->
                                    <!--<input id="setFitView" class="button" type="button" value="地图自适应显示"/>-->
                                <!--</div>-->
                                <div class="info-tip">
                                    <div id="centerCoord"></div>
                                    <div id="tips"></div>
                                </div>
                                <script type="text/javascript">
                                    //初始化地图对象，加载地图
                                    var map = new AMap.Map("container_main", {
                                        resizeEnable: true,
                                        center: [<?php echo $data['company_data']['x'].','.$data['company_data']['y'] ?>],
                                    zoom: 16 //地图显示的缩放级别
                                });

                                marker = new AMap.Marker({
                                    icon: "http://webapi.amap.com/theme/v1.3/markers/n/loc.png",
                                    position: [<?php echo $data['company_data']['x'].','.$data['company_data']['y'] ?>],
                                });
                                map.clearMap();  // 清除地图覆盖物

                                // 公司地址
                                marker.setMap(map);
                                marker.setLabel({
                                    offset: new AMap.Pixel(20, 20),//修改label相对于maker的位置
                                    content: "<?php echo $_SESSION['company_name'] ?>"
                                });

                                // 车辆
                                    // 初始化一个信息窗体
                                    var infoWindow = new AMap.InfoWindow();
                                var markers = [
                                    <?php foreach($data['data1'] as $v) {?>
                                    {
                                        icon: 'http://img.meiwenti.top/map_icon/<?php echo ($v['truck_state']); ?>',
                                        position: [<?php  echo $v['x'].','.$v['y']?>],
                                        content:'<?php echo ($v['real_name']); ?>'+'<br>'+'<?php echo ($v['phone']); ?>'+'<br>'+'<?php echo ($v['lic_number']); ?>'+'<br>目的地：'+'<?php echo ($v['destination']); ?>'
                                        },
                                        <?php }?>
                                    ];

                                markers.forEach(function(marker) {
                                    tmp_marker = new AMap.Marker({
                                        map: map,
                                        icon: marker.icon,
                                        position: [marker.position[0], marker.position[1]],
                                        offset: new AMap.Pixel(-20, 0),
                                    });
                                    tmp_marker.content=marker.content;
//                                    //给Marker绑定单击事件
                                    tmp_marker.on('click', markerClick);
                                });
                                var center = map.getCenter();
                                var centerText = '公司中心点坐标：' + center.getLng() + ',' + center.getLat();
//                                    document.getElementById('centerCoord').innerHTML = centerText;
//                                document.getElementById('tips').innerHTML = '<?php  echo $data['info'] ?>';

                                    function markerClick(e){
                                        infoWindow.setContent(e.target.content);
                                        infoWindow.open(map, e.target.getPosition());
                                    }
                                </script>

                                 <?php } else { echo $data['data1']; } ?>

                            <!--贸易商首页  end-->
                            <?php else: ?>
                            <!--物流公司首页  start-->

                            <script>
                                var wl_index_latest_logis_url = '<?php echo U('Vip/Company/getAssignedLogistics');?>';
                                var wl_index_wait_truck_url = '<?php echo U('Vip/Company/getWaitTrucks');?>';
                                var wl_index_change_auto_url = '<?php echo U('Vip/Company/changeAutoState');?>';
                                var wl_index_change_order_url = '<?php echo U('Vip/Company/changeIndexOrder');?>';
                            </script>
                            <ul class="wl-index" id="dynamic-data">
                                <li><h3>本月招标量</h3><span><?php echo ($dynamic_data["tender"]); ?></span>吨
                                    <button class="btn btn-green" data-toggle="navtab" data-options="{id:'index_edit_company_info',url:'<?php echo U('Vip/Company/editCompanyInfo');?>',width:'800pt',height:'600pt',title:'修改信息'}">修改</button></li>
                                <li><h3>本月已拉吨数</h3><span><?php echo ($dynamic_data["quantity_count"]); ?></span>吨</li>
                                <li><h3>本月已拉车次</h3><span><?php echo ($dynamic_data["timers_count"]); ?></span>车</li>
                                <li><h3>本日已拉吨数</h3><span><?php echo ($dynamic_data["day_quantity_count"]); ?></span>吨</li>
                                <li><h3>本日已拉车次</h3><span><?php echo ($dynamic_data["day_timers_count"]); ?></span>车</li>
                                <li><h3>待安排吨数</h3><span><?php echo ($dynamic_data["res_quantity"]); ?></span>吨</li>
                            </ul>
                            
                            <div class="row" style="clear: both; padding-top: 20px">
                                <div class="col-sm-12"><?php if($auto_arrbill == 0): ?><span style="color: red">需要手动派单</span>
                                    <button type="button" class="btn btn-blue" data-toggle="doajax" data-options="{
                                        url:wl_index_change_auto_url,
                                        data:{type:1},
                                        type:'get',
                                        confirmMsg:'确定要切换到自动派单吗',
                                        callback:'function(json){location.reload();}',
                                        okalert:false
                                        }">切换到自动派单</button>
                                    <?php else: ?>
                                    <span style="color: green">自动派单中……</span>
                                    <button type="button" class="btn btn-blue" data-toggle="doajax" data-options="{
                                        url:wl_index_change_auto_url,
                                        data:{type:0},
                                        type:'get',
                                        okalert:false,
                                        callback:'function(json){location.reload();}',
                                        confirmMsg:'确定要切换到手动派单吗'
                                        }">切换到手动派单</button><?php endif; ?>
                                    <?php if($is_hide_index_order == 1): ?><button type="button" class="btn btn-blue" data-toggle="doajax" data-options="{
                                        url:wl_index_change_order_url,
                                        data:{type:0},
                                        type:'get',
                                        okalert:false,
                                        callback:'latest_logis_show'
                                        }">显示最新订单</button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-blue" data-toggle="doajax" data-options="{
                                        url:wl_index_change_order_url,
                                        data:{type:1},
                                        type:'get',
                                        okalert:false,
                                        callback:'latest_logis_hide'
                                        }">隐藏最新订单</button><?php endif; ?>
                                </div>
                                <div class="col-sm-12" id="p-latest-logistics-filter">
                                    <table class="table table-bordered" data-toggle="datagrid" data-options="{
                                        height:'350',
                                        width:'100%',
                                        gridTitle: '最新订单',
                                        showLinenumber:false,
                                        dataUrl: wl_index_latest_logis_url,
                                        fieldSortable:false,
                                        filterMult:false,
                                        columnMenu:false,
                                        local:'remote',
                                        filterThead:false,
                                        paging:{pageSize:5}
                                    }" id="latest-logistics-filter">
                                        <thead>
                                        <tr>
                                            <th data-options="{name:'order_id',align:'center'}" style="width:10%;min-width: 241px;">订单号</th>
                                            <th data-options="{name:'buy_sell',align:'center'}" style="width:10%;min-width: 100px;">贸易方</th>
                                            <th data-options="{name:'coal_type_name',align:'center'}" style="width:15%;min-width: 70px;">煤种</th>
                                            <th data-options="{name:'assign_name',align:'center'}" style="width:10%;min-width: 100px;">计划发布方</th>
                                            <?php if($auto_arrbill == 0): ?><th data-options="{name:'dostr',align:'center'}" style="width:10%;min-width: 50px;">操作</th><?php endif; ?>
                                        </tr>
                                        </thead>
                                    </table>
                                    <script>
                                        function latest_logis_show(){
                                            $('#p-latest-logistics-filter').show();
                                            location.reload();
                                        }
                                        function latest_logis_hide(){
                                            $('#p-latest-logistics-filter').hide();
                                            location.reload();
                                        }
                                        <?php if($is_hide_index_order == 1): ?>$('#p-latest-logistics-filter').hide();
                                        <?php else: ?>
                                            $('#p-latest-logistics-filter').show();<?php endif; ?>
                                    </script>
                                </div>
                                <div class="col-sm-12" style="padding: 30px">
                                    <table class="table table-bordered" data-toggle="datagrid" data-options="{
                                        height:'600',
                                        width:'100%',
                                        gridTitle: '排队车辆',
                                        showToolbar: true,
                                        toolbarItem: 'refresh',
                                        showLinenumber:false,
                                        dataUrl: wl_index_wait_truck_url,
                                        fieldSortable:false,
                                        filterMult:false,
                                        columnMenu:false,
                                        local:'remote',
                                        filterThead:false,
                                        paging:{pageSize:300}
                                    }" id="wait-truck-filter">
                                        <thead>
                                        <tr>
                                            <th data-options="{name:'owner_order',align:'center'}" style="width:10%;">车辆编号</th>
                                            <th data-options="{name:'lic_number',align:'center'}" style="width:10%;">车牌号</th>
                                            <th data-options="{name:'owner_name',align:'center'}" style="width:10%;">车主</th>
                                            <th data-options="{name:'last_time',align:'center'}" style="width:10%;">等待时长</th>
                                            <th data-options="{name:'last_time1',align:'center'}" style="width:15%;">上次结束时间</th>
                                            <th data-options="{name:'last_driver',align:'center'}" style="width:15%;">上次拉货司机</th>
                                            <th data-options="{name:'feedback',align:'left'}" style="width:30%">车辆排队情况反馈</th>
                                        </tr>
                                        </thead>
                                    </table>
                                </div>
                                <!--<div class="col-sm-12" style="padding:20px">-->
                                    <!--&lt;!&ndash;车辆动态&ndash;&gt;-->
                                    <!--<div id="j-index-message" style="font-size:90%; color:#900;"></div>-->

                                <!--</div>-->
                            </div>
                            <!-- 铃声 -->
                            <audio id="bgMusic">
                                <source src="/Public/media/newplan.ogg" type="audio/ogg">
                            </audio>
                            <!-- 定时获取最新计划 -->
                            <script>
                                // 定时计划
                                var listen_clock = setInterval('listen()', 20000);
                                listen_clock1 = listen_clock;
                                var index_fresh = 0;
//                                console.log(listen_clock);
                                // 如果有单就请求
                                function listen(){
                                    $.get('<?php echo U('Vip/Company/listening');?>', {}, function(msg){
                                        // 301错误
                                        if (msg.statusCode == 301) {
                                            // session过期请求失败，停止定时计划
                                            stop_clock();
                                            location.reload();
                                        }

                                        if (parseInt(msg.count) > 0) {
                                            // 有单
                                            if (msg.is_change == '1') {
                                                // 刷新计划
                                                fresh_logistics();
                                            }
                                            index_fresh = 2;
                                            // 手动才放，播放提醒
                                            if (msg.auto_state == 0) {
                                                play_reminder();
                                            }
                                        } else {
                                            if (index_fresh == 2) {
                                                // 从有单到没有单，页面的单要消失
                                                // 刷新计划
                                                fresh_logistics();
                                                index_fresh = 0;
                                            }
                                            stop_clock();
                                            start_clock();
                                        }
                                        // 有没有单都刷新车辆
                                        fresh_truck();
                                        // 请求剩余数量
                                        get_dynamic_data();
                                    },'json');
                                }

                                // 刷新计划
                                function fresh_logistics(){
                                    $('#latest-logistics-filter').datagrid('refresh', true);
                                }
                                // 刷新车辆
                                function fresh_truck(){
                                    $('#wait-truck-filter').datagrid('refresh', true);
                                }
                                // 请求剩余数量
                                function get_dynamic_data() {
                                    BJUI.ajax('doajax',{
                                        url:'<?php echo U('Vip/Company/getDynamicData/type/2');?>',
                                        type:'get',
                                        okCallback: function(json, options) {
                                            var j = 0;
                                            $.each(json, function(i, n) {
                                                var t = $('#dynamic-data span').eq(j).html(n);
                                                j++;
                                            });
                                        },
                                        errCallback:function(json, options){
                                            BJUI.alertmsg('error', '请求失败，请关闭后重新打开');
                                        }
                                    });
                                }
                                // 播放提醒
                                function play_reminder() {
                                    $('#bgMusic')[0].play();
                                }

                                // 点击派单时停止音乐
                                function stop_clock(){
                                    clearInterval(listen_clock1);
                                    clearInterval(listen_clock);
                                }
                                function start_clock(){
                                    setTimeout("var listen_clock = setInterval('listen()', 20000);listen_clock1 = listen_clock;", '3000');
                                }
                            </script>
                            <!--物流公司首页  end--><?php endif; ?>
                            <!-- 站长统计 -->
                            <script type="text/javascript">var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1262381038'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s19.cnzz.com/z_stat.php%3Fid%3D1262381038%26show%3Dpic2' type='text/javascript'%3E%3C/script%3E"));</script>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
    <script src="/Public/B-JUI/other/ie10-viewport-bug-workaround.js"></script>
    <!---------------------------------高德地图----------------------------->

</body>
</html>