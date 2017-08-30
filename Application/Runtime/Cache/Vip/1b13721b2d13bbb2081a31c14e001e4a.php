<?php if (!defined('THINK_PATH')) exit();?><script>
    var dataurl="<?php echo U('Vip/Bill/getTakeBill');?>";
</script>
<div class="bjui-pageHeader" style="background-color:#fefefe; border-bottom:none;">
    <form action="" data-toggle="ajaxsearch" data-options="{searchDatagrid:$.CurrentNavtab.find('#datagrid-take-bill-filter')}" id="take-bill-form">
    <fieldset>
    <legend style="font-weight:normal;">页头搜索：</legend>
    <div style="margin:0; padding:1px 5px 5px;">
    <span>车牌号：</span>
    <input type="text" name="lic_number" class="form-control" size="15">

    <span>内部编号：</span>
    <input type="text" name="owner_order" class="form-control" size="15">

    <span>卖家：</span>
    <select data-toggle="selectpicker" name="seller" class="form-control" >
        <option value="">全部</option>
        <?php if(is_array($sellers)): foreach($sellers as $key=>$vo): ?><option value="<?php echo ($vo["id"]); ?>" <?php if($vo['id'] == $seller): ?>selected<?php endif; ?>><?php echo ($vo["name"]); ?></option><?php endforeach; endif; ?>
    </select>

    <div class="btn-group">
    <button type="submit" class="btn-green" data-icon="search">开始搜索！</button>
    </div>
    </div>
    </fieldset>
    </form>
</div>
<div class="pageContent" style="position:absolute;top:69pt;bottom: 40pt">
    <table class="table table-bordered" data-toggle="datagrid" data-options="{
        gridTitle: '待接单',
         showLinenumber:false,
        dataUrl: dataurl,
        fieldSortable:false,
        filterMult:false,
        columnMenu:false,
        local:'remote',
        filterThead:false,
        width:'100%'
    }" id="datagrid-take-bill-filter">
        <thead>
        <tr>
            <th data-options="{name:'id',align:'center'}" style="min-width:30px;width: 60px;">订单号</th>
            <th data-options="{name:'order_id',align:'center'}" style="width:15%;min-width: 260px;">提煤单号</th>
            <th data-options="{name:'buy_sell',align:'center'}" style="width:10%;min-width: 100px;">贸易方</th>
            <th data-options="{name:'lic_number',align:'center'}" style="width:100px">车牌号</th>
            <th data-options="{name:'take_time',align:'center'}" style="min-width:100px">耗时</th>
            <!--<th data-options="{name:'arrange_w',align:'center'}" style="min-width:50px">安排吨数</th>-->
            <th data-options="{name:'trucker_name',align:'center'}" style="min-width:100px">车主</th>
            <th data-options="{name:'driver',align:'center'}" style="min-width:100px">上班中司机</th>
            <th data-options="{name:'dostr',align:'center'}" style="min-width:100px">操作</th>
        </tr>
        </thead>
    </table>
</div>
<script>
    var win_h = $(window).height();
//    $('#datagrid-take-bill-filter').css({'height':win_h - 140, 'overflow-y':'scroll'});
    var win_w = $('.tabsPageHeader').width();
    $('.pageContent').css({'width':win_w - 40, 'overflow-x':'scroll'});


</script>