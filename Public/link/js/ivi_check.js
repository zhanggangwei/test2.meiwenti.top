// JavaScript Document
$(document).ready(function(e) {
			var isformok=false;
			check_num();
			check_true();
        });
//数字验证
function check_num(){
		$(".check_num").attr('placeholder','请填写数值');
			$(".check_num").focus(function(){
					$(this).next().remove();
					$(this).after('<img width="20" src="/Public/link/images/login.gif"/>');
				})
			$(".check_num").blur(function(){
					if(!$(this).val()){
							$(this).next().remove();
						}
				})
            $(".check_num").change(function(){
				if(!isNaN($(this).val())){
						$(this).next().remove();
						$(this).after('<i class="iconfont check_ico success_ico">&#xe60f;</i>');
						isformok=true;
					}else{
						$(this).next().remove();
						$(this).after('<i class="iconfont check_ico error_ico">&#xe643;</i>');
						isformok=false;
						}
					})
	}
//必填验证
function check_true(){
	$('.check_true').after('<i class="iconfont check_ico true_ico">&#xe624;</i>');
	$('.check_true').blur(function(){
		if(!$(this).val()){
							$(this).next().remove();
							$(this).after('<i class="iconfont check_ico true_ico">&#xe624;</i>');
						}else{
							isformok=true;
							}
		})
	}
//提交验证
function checkform(){
	$('.check_true').each(function(index, element) {
		if(!$(element).val()){
			$(this).next().remove();
			$(element).after('<i class="iconfont check_ico error_ico">&#xe643;</i>');
			isformok=false;
			}else{
			$(this).next().remove();
			$(element).after('<i class="iconfont check_ico success_ico">&#xe60f;</i>');
				}
    });
	if(isformok){
		return true;
		}else{
		return false;
			}
	}