<?php if (!defined('THINK_PATH')) exit();?><script>
    var dataurl="<?php echo U('Vip/Bill/getReturnBill');?>";
</script>
<div class="bjui-pageHeader" style="background-color:#fefefe; border-bottom:none;">
    <form action="" data-toggle="ajaxsearch" data-options="{searchDatagrid:$.CurrentNavtab.find('#datagrid-return-bill-filter')}" id="return-bill-form">
    <fieldset>
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

<div class="pageContent" style="position:absolute;top:40pt;bottom: 10pt">
    <table class="table table-bordered" data-toggle="datagrid" data-options="{
        gridTitle: '司机退单',
        showLinenumber:false,
        dataUrl: dataurl,
        fieldSortable:false,
        filterMult:false,
        columnMenu:false,
        local:'remote',
        filterThead:false,
        width:'100%'
    }" id="datagrid-return-bill-filter">
        <thead>
        <tr>
            <th data-options="{name:'order_id',align:'center'}" style="min-width:200px">提煤单号</th>
            <th data-options="{name:'buyer_name',align:'center'}" style="min-width:110px">买家</th>
            <th data-options="{name:'seller_name',align:'center'}" style="min-width:110px">卖家</th>
            <th data-options="{name:'lic_number',align:'center'}" style="min-width:80px">车牌号</th>
            <!--<th data-options="{name:'arrange_w',align:'center'}" style="min-width:50px;width: 50px">安排<br>吨数</th>-->
            <!--<th data-options="{name:'trucker_name',align:'center'}" style="min-width:100px">车主</th>-->
            <th data-options="{name:'driver',align:'center'}" style="min-width:100px">司机</th>
            <!--<th data-options="{name:'driver_phone',align:'center'}" style="min-width:100px">司机电话</th>-->
            <th data-options="{name:'do_time',align:'center'}" style="min-width:100px">耗时</th>
            <th data-options="{name:'reason',align:'center'}" style="min-width:100px">退单原因</th>
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