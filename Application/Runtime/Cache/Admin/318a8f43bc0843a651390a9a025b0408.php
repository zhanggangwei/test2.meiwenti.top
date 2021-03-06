<?php if (!defined('THINK_PATH')) exit();?><script>
    var dataurl="<?php echo U('Admin/Industry/getTmarket');?>";
</script>

<div class="pageContent" style="position:absolute;top:0;bottom: 10pt;width:100%">
    <button type="button" class="btn btn-default" data-toggle="dialog" data-options="{id:'todaymarket', url:'<?php echo U('Admin/Industry/addTmarket');?>', title:'添加今日行情',height:500}">添加今日行情</button>
    <button type="button" class="btn btn-default" data-toggle="doajax" data-options="{url:'<?php echo U('Admin/Industry/collectTodaymarket');?>', title:'采集行情数据'}">采集行情数据</button>
    <table class="table table-bordered" data-toggle="datagrid" data-options="{
        height:'97%',
        width:'100%',
        gridTitle: '今日行情',
		 showLinenumber:false,
        dataUrl: dataurl,
        fieldSortable:false,
        filterMult:false,
        columnMenu:false,
        local:'remote',
        filterThead:false,
        paging:{pageSize:10}
	}" id="today-market-filter">
        <thead>
        <tr>
            <th data-options="{name:'id',align:'center'}" style="width: 10%">ID</th>
            <th data-options="{name:'system',align:'center'}" style="width: 20%">体系</th>
            <th data-options="{name:'name',align:'center'}" style="width: 20%">名称</th>
            <th data-options="{name:'update_time',align:'center'}" style="width: 20%">更新日期</th>
            <th data-options="{name:'price',align:'center'}" style="width: 10%">价格</th>
            <th data-options="{name:'in_out',align:'center'}" style="width: 10%">变化</th>
            <th data-options="{name:'dostr',align:'center'}" style="width: 10%">操作</th>
        </tr>
        </thead>
    </table>

</div>