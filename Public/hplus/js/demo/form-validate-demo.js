//以下为修改jQuery Validation插件兼容Bootstrap的方法，没有直接写在插件中是为了便于插件升级
        $.validator.setDefaults({
            highlight: function (element) {
                $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
            },
            success: function (element) {
                element.closest('.form-group').removeClass('has-error').addClass('has-success');
            },
            errorElement: "span",
            errorPlacement: function (error, element) {
                if (element.is(":radio") || element.is(":checkbox")) {
                    error.appendTo(element.parent().parent().parent());
                } else {
                    error.appendTo(element.parent());
                }
            },
            errorClass: "help-block m-b-none",
            validClass: "help-block m-b-none"


        });

        //以下为官方示例
        $().ready(function () {
            // validate the comment form when it is submitted
            $("#commentForm").validate();

            // validate signup form on keyup and submit
            var icon = "<i class='fa fa-times-circle'></i> ";
            $("#signupForm").validate({
                rules: {
                    firstname: "required",
                    lastname: "required",
    
                    password: {
                        required: true,
                        minlength: 6
                    },
                    password1: {
                        required: true,
                        minlength: 6
                    },
                    confirm_password1: {
                        required: true,
                        minlength: 6,
                        equalTo: "#password1"
                    },
                    tel: {
                        required: true,
                        tel: true
                    },
   
                    agree: "required"
                },
                messages: {
                    firstname: icon + "没有获取到用户名",
                    lastname: icon + "没有获取到用户名",
                    username: {
                        required: icon + "没有获取到用户名",
                        minlength: icon + "用户名必须两个字符以上"
                    },
                    password: {
                        required: icon + "请输入您的密码",
                        minlength: icon + "密码必须6个字符以上"
                    },
                   password1: {
                        required: icon + "请输入您的新密码",
                        minlength: icon + "新密码必须6个字符以上"
                    },
                    confirm_password1: {
                        required: icon + "请再次输入新密码",
                        minlength: icon + "新密码必须6个字符以上",
                        equalTo: icon + "两次输入的新密码不一致"
                    },
                    tel:{ 
					required: icon + "请输入您的手机验证码",
		
					},
                }
            });

            // propose username by combining first- and lastname
     );
        });
