<?php if (!defined('THINK_PATH')) exit();?><script>
    var dataurl="<?php echo U('Vip/CompanyTruck/getCompanyTrucks');?>";
//    var dataurl1="<?php echo U('Vip/CompanyTruck/getCooTrucks', array('trucker_id' => $info['id'], 'coo_state' => 0));?>";
</script>
<div class="bjui-pageHeader" style="background-color:#fefefe; border-bottom:none;">
    <form action="" data-toggle="ajaxsearch" data-options="{searchDatagrid:$.CurrentNavtab.find('#datagrid-com-truck-t-filter')}" id="com-truck-form">
    <fieldset>
    <legend style="font-weight:normal;">页头搜索：</legend>
    <div style="margin:0; padding:1px 5px 5px;">
    <span>车牌号：</span>
    <input type="text" name="lic_number" class="form-control" size="15">

    <!--<span>手机：</span>-->
    <!--<input type="text" name="phone" class="form-control" size="15">-->

    <div class="btn-group">
    <button type="submit" class="btn-green" data-icon="search">开始搜索！</button>
    <button type="reset" class="btn-orange" data-icon="times">重置</button>
    </div>
    </div>
    </fieldset>
    </form>
</div>

<div class="pageContent" style="position:absolute;top:69pt;bottom: 40pt">
    <table class="table table-bordered" data-toggle="datagrid" data-options="{
        gridTitle: '自有车辆列表',
		 showLinenumber:false,
        dataUrl: dataurl,
        fieldSortable:false,
        filterMult:false,
        columnMenu:false,
        local:'remote',
        filterThead:false,
        width:'100%'
	}" id="datagrid-company-truck-filter">
        <thead>
        <tr>
            <th data-options="{name:'id',align:'center'}" style="min-width:50px">id</th>
            <th data-options="{name:'owner_order',align:'center'}" style="min-width:80px">内部编号</th>
            <th data-options="{name:'lic_number',align:'center'}" style="min-width:80px">车牌号</th>
            <th data-options="{name:'driver',align:'center'}" style="min-width:100px">绑定司机</th>
            <th data-options="{name:'maximum',align:'center'}" style="min-width:50px">吨位</th>
            <th data-options="{name:'lic_date',align:'center'}" style="min-width:100px">上牌时间</th>
            <th data-options="{name:'create_time',align:'center'}" style="min-width:100px">添加时间</th>
            <th data-options="{name:'state',align:'center'}" style="min-width:100px">状态</th>
            <th data-options="{name:'dostr',align:'center'}" style="min-width:100px">操作</th>
        </tr>
        </thead>
    </table>
</div>
<script>
    var win_h = $(window).height();
//    $('#datagrid-company-truck-filter').css({'height':win_h - 140, 'overflow-y':'scroll'});
    var win_w = $('.tabsPageHeader').width();
    $('.pageContent').css({'width':win_w - 40, 'overflow-x':'scroll'});


</script>