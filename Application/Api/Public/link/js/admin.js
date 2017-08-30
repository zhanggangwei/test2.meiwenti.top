// JavaScript Document
$(function () {
    'use strict';
	//提交页面
	$(document).on("pageInit", "#Admin_index", function(e, pageId, $page) {
		console.log('pageName:后台登陆');
		$('.login').click(function(){
				dologin();
			})
	});
	//错误页面
	$(document).on("pageInit", "#Erro_page", function(e, pageId, $page) {
		$.alert('账号或者密码错误',function(){
				window.history.back(-1);
			});
	});
	//最终展示页面
	$(document).on("pageInit", "#Show_img", function(e, pageId, $page) {
		var myPhotoBrowserStandalone = $.photoBrowser({
      	photos : ['/Uploads/images/2016-09-08/57d0ec571ed25.png','/Uploads/images/2016-09-08/57d0ec5b1e062.png',]
 		 });
	  //点击时打开图片浏览器
		  $(document).on('click','.pb-standalone',function () {
			myPhotoBrowserStandalone.open();
		  });
	});
	$.init();
	//报名选项
});
//登陆提交验证
function dologin(){
	var formv=$('#loginform');
		if(!formv.find('.acct').val()){
		//验证账号
			$.alert('账号不能为空')
		}else if(!formv.find('.psd').val()){	
		//验证密码
			$.alert('密码不能为空')
		}else{
			document.getElementById('loginform').submit();
		}
}
//错误跳转
function erro_tiaozhuan(){
		
	}