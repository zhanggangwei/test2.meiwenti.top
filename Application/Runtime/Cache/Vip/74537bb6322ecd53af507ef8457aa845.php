<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>操作失败</title>
	<link href="/Public/hplus/css/bootstrap.min.css?v=3.3.6" rel="stylesheet">
    <link href="/Public/hplus/css/font-awesome.css?v=4.4.0" rel="stylesheet">
    <!-- jqgrid-->
    <link href="/Public/hplus/css/plugins/jqgrid/ui.jqgrid.css?0820" rel="stylesheet">
    <link href="/Public/hplus/css/animate.css" rel="stylesheet">
    <link href="/Public/hplus/css/style.css?v=4.1.0" rel="stylesheet">   
    <link href="__IPUBLIC__/css/admin.css?v=4.1.0" rel="stylesheet"> 
    <link href="/Public/link/css/font.css?v=4.1.0" rel="stylesheet"> 
    <style>
        /* Additional style to fix warning dialog position */
        #alertmod_table_list_2 {
            top: 900px !important;
        }
    </style>
</head>

<body class="gray-bg top-navigation">
     


    <script src="/Public/hplus/js/jquery.min.js?v=2.1.4"></script>
    <script src="/Public/hplus/js/bootstrap.min.js?v=3.3.6"></script>
    

    <!-- jqGrid -->
    <script src="/Public/hplus/js/plugins/jqgrid/i18n/grid.locale-cn.js?0820"></script>
    <script src="/Public/hplus/js/plugins/jqgrid/jquery.jqGrid.min.js?0820"></script>
   
  	 <!-- layer -->
   	<script src="/Public/hplus/js/plugins/layer/layer.min.js"></script>
    
    
   <script>
		layer.open({
			title: '<?php echo ($msgTitle); ?>',
			content: "<?php echo ($error); ?>",
			skin: 'alert_error',
			end : function(){
				window.location.href="<?php echo ($jumpUrl); ?>";
				}
			 })
		
   </script>
  
    
    
    
    

</body>

</html>