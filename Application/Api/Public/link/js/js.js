// JavaScript Document
$(function () {
    'use strict';
	//提交页面
	$(document).on("pageInit", "#submit-page", function(e, pageId, $page) {
		console.log('pageName:submit');
	});
	//报名选项
	$(document).on('click','.create-actions', function () {
      var buttons1 = [
        {
          text: '请选择报名项目',
          label: true
        },
        {
          text: '足球比赛',
          onClick: function() {
            window.location.href=football_url; 
          }
        },
        {
          text: '田径比赛',
          onClick: function() {
            window.location.href=tianjing_url; 
          }
        },
        {
          text: '跆拳道比赛',
          onClick: function() {
            window.location.href=taiquan_url; 
          }
        },
        {
          text: '武术比赛',
          onClick: function() {
            window.location.href=wushu_url; 
          }
        },
        {
          text: '跳绳比赛',
          onClick: function() {
            window.location.href=tiaosheng_url; 
          }
        },
        {
          text: '健美操啦啦操比赛',
          onClick: function() {
            window.location.href=jianmei_url; 
          }
        }
      ];
      var buttons2 = [
        {
          text: '取消',
          bg: 'danger'
        }
      ];
      var groups = [buttons1, buttons2];
      $.actions(groups);
  });

//足球提交
$(document).on("pageInit", "#football_post", function(e, pageId, $page) {
		console.log('pageName:submit');
		var count = -1;
		//$('#image1').bind('change',function(){up($('#image1'))}) 
		
		/*$("#image1").live("change", function () {
		alert(100)
        count++;
		up($('#image1'));
		$("#image1").replaceWith("<input type='file' id='image1' name='p' accept='image/*' class='col-100'  title=" + count + "' />");
		})*/
	
		 $("#image1").bind("change", function () {
        		count++;
				up($('#image1'));
				$("#image1").replaceWith("<input type='file' onchange="+'"'+"up($('#image1'))"+'"'+"' id='image1' name='p' accept='image' class='col-100'  title='" + count + "' />");
			})
		
		$("#image2").bind("change", function () {
        		count++;
				up($('#image2'));
				$("#image2").replaceWith("<input type='file' onchange="+'"'+"up($('#image2'))"+'"'+"' id='image2' name='p' accept='image/*' class='col-100'  title='" + count + "' />");
			})
			
		$("#image3").bind("change", function () {
        		count++;
				up($('#image3'));
				$("#image3").replaceWith("<input type='file' onchange="+'"'+"up($('#image3'))"+'"'+"' id='image3' name='p' accept='image/*' class='col-100'  title='" + count + "' />");
			})
			
		$("#image4").bind("change", function () {
        		count++;
				up($('#image4'));
				$("#image4").replaceWith("<input type='file' onchange="+'"'+"up($('#image4'))"+'"'+"' id='image4' name='p' accept='image/*' class='col-100'  title='" + count + "' />");
			})
			
		$("#image5").bind("change", function () {
        		count++;
				up($('#image5'));
				$("#image5").replaceWith("<input type='file' onchange="+'"'+"up($('#image5'))"+'"'+"' id='image5' name='p' accept='image/*' class='col-100'  title='" + count + "' />");
			})
			
		$("#image6").bind("change", function () {
        		count++;
				up($('#image6'));
				$("#image6").replaceWith("<input type='file' onchange="+'"'+"up($('#image6'))"+'"'+"' id='image6' name='p' accept='image/*' class='col-100'  title='" + count + "' />");
			})
			
		$("#image7").bind("change", function () {
        		count++;
				up($('#image7'));
				$("#image7").replaceWith("<input type='file' onchange="+'"'+"up($('#image7'))"+'"'+"' id='image7' name='p' accept='image/*' class='col-100'  title='" + count + "' />");
			})
			
		$("#image8").bind("change", function () {
        		count++;
				up($('#image8'));
				$("#image8").replaceWith("<input type='file' onchange="+'"'+"up($('#image8'))"+'"'+"' id='image8' name='p' accept='image/*' class='col-100'  title='" + count + "' />");
			})
			
		/*$('#image2').change(function(){
				up($('#image2'))
			})
		$('#image3').change(function(){
				up($('#image3'))
			})
		$('#image4').change(function(){
				up($('#image4'))
			})
		$('#image5').change(function(){
				up($('#image5'))
			})
		$('#image6').change(function(){
				up($('#image6'))
			})
		$('#image7').change(function(){
				up($('#image7'))
			})
		$('#image8').change(function(){
				up($('#image8'))
			})*/
		//向上一页
		$('.pull-left').click(function(){
			window.history.back(-1); 
			})
			
});
//成功页面跳转
$(document).on("pageInit", "#Success_page", function(e, pageId, $page) {
	$.alert(message,function(){
				window.location.href=jumpurl;
			});
	});
//错误页面跳转
$(document).on("pageInit", "#Error_page", function(e, pageId, $page) {
	$.alert(message,function(){
				window.history.back(-1); 
			});
	});
    $.init();
});


//防止一次提交失效
function changeone(){
	count++;
	up($('#image1'));
	$("#image1").replaceWith("<input type='file' id='image1' name='p' accept='image/*' class='col-100'  title='" + count + "' />");
	$("#image1").bind("change", function () {
		 changeone();
		})
	}
//表单提交验证
function dosubmit(){
		var formv=$('#subform');
		if(!$('.school').val()){
		//验证失败
		alert('学校不能为空')
		return;
		}else if(!$('.group').val()){
		alert('组别不能为空')	
		return;
		}else if($("input[type='password']").val()!='888888'){	
		alert('报名密码不正确')
		return;
		
		}else{
		//验证成功
		document.getElementById('subform').submit();
			}
	}

//图片上传
   function up(inid) {
	   		console.log('开始上传');
	   		id=inid.attr('id');
			parent=inid.parent();
			img=parent.find('img');
			span=parent.find('span');
			text=parent.find('.text');
			img.attr('src',loading_gif);
			img.show();
			span.hide();
            $.ajaxFileUpload
            (
                {
                    url: upload_url, //用于文件上传的服务器端请求地址
                    secureuri: false, //是否需要安全协议，一般设置为false
                    fileElementId: id, //文件上传域的ID
                    dataType: 'json', //返回值类型 一般设置为json
                    success: function (data, status)  //服务器成功响应处理函数
                    {
                        if (data.error==0) {
                                 alert(data.msg);
								 img.hide();
								 span.show();
								 text.val('');
								 return;
                         } 
						img.attr('src',data.true_url);
						text.val(data.url);
                    },
                    error: function (data, status, e)//服务器响应失败处理函数
                    {
                       //
					   alert('服务器响应失败');
                    }
                }
            )
            return false;
        }