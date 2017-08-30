<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>个人简历</title>
</head>
<body>
	你好，<?php echo ($name); ?>!欢迎进入！
	<!-- 菜单 -->
	<ul>
		<li>
			<ul>
			<?php if(is_array($menu)): $i = 0; $__LIST__ = $menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><li><?php echo ($vo); ?></li><?php endforeach; endif; else: echo "" ;endif; ?>
			</ul>
		</li>
	</ul>
</body>
</html>