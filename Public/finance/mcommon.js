define("mcommon",["zepto"],function($){function isKeyinPath(a){var b=location.pathname.indexOf(a);return b>=0}function isKeyinReferrer(a){var b=document.referrer.indexOf(a);return b>=0}function refreshPage(){window.history.go(0)}function errorHandler(a,b,c){var d=b.status;console.log(d+" -- ajax error..");var e=[400,401,403,404,500,409],f={401:"请重新登录...",403:"您没有权限访问该网页...",404:"您访问的数据不存在或已被修改，请稍后刷新重试...",400:"服务器请求出错，请稍后重试...",500:"服务器出错，请稍后重试..."},g="",h="";if($.inArray(d,e)>=0){if(401==d)h="/m/login",g=f[d];else if(409==d){var i=JSON.parse(b.responseText);g=i.message,h=i.url}else g=f[d];showServerError(g,h)}}function offModal1Events(){$("button.md-off").tap(function(){$("#md-btn-2").off()})}function closeEvent4AllModals(){$("button.md-close").tap(function(){hideAllModal()})}function hideAllModal(){setTimeout("$('.md-modal').removeClass('md-show');",200)}function commonShowModal(a){$(a).addClass("md-show")}function commonHideModal(a){$(a).removeClass("md-show")}function showModal(a){$.trim(a)&&$("#modal-content").html(a),commonShowModal("#modal-1")}function hideModal(){commonHideModal("#modal-1")}function showNewModal(a){$.trim(a)&&$("#newModal-content").html(a),commonShowModal("#new-modal")}function hideNewModal(){setTimeout("$('#new-modal').removeClass('md-show');",800)}function showModalMsg(a){$.trim(a)&&$("#modal-msg-content").html(a),commonShowModal("#modal-msg")}function hideModalMsg(){commonHideModal("#modal-msg")}function showLoading(){showModalMsg("Loading...玩命加载中...")}function hideLoading(){hideModalMsg()}function showError(a){var b=$("#modal-content");""!=$.trim(a)?b.html(a):b.html("出错了..."),commonShowModal("#modal-1")}function hideError(){commonHideModal("#modal-1")}function checkIsLogin(){return isCheck()?!0:($("#gotoUrl").val(window.se.get("url")),location.href="/m/login",!1)}function isCheck(){var a=null;return $.ajax({async:!1,type:"get",url:"/m/checkLogin",success:function(b){a=b?!0:!1}}),a}function checkSupplyAmount(){var a=$("#supplyquantity"),b=a.val();return""==b?BaseError(a,"请输入供应吨数",0):regluar.test(b)?Number(b)>=999999?BaseError(a,"供应吨数应小于999999吨",0):Number(b)<50?BaseError(a,"供应吨数不可以小于50吨",0):BaseError(a,""):BaseError(a,"供应吨数应为正整数",0)}function checkDigit(a){var b=!0;return-1!=a.indexOf(".")?a.substr(0,a.indexOf(".")).length>=2&&0==a.substr(0,1)&&(b=!1):a.length>=2&&0==a.substr(0,1)&&(b=!1),b}function checkDemandAmount(){var a=$("#demandamount"),b=a.val();return""==b?BaseError(a,"请输入吨数",0):regluar.test(b)?Number(b)<50?BaseError(a,"吨数需要大于50",0):Number(b)>999999?BaseError(a,"吨数不可以大于999999",0):BaseError(a,""):BaseError(a,"吨数为正整数",0)}function checkBrandName(){var a=currCoalTypeMapping[$("#pname").val()],b=a.el.find("[name=brandname]"),c=b.val();return c.length>6?BaseError(b,"品名最多为6个字",0):BaseError(b,"")}function checkNCV(){var a=currCoalTypeMapping[$("#pname").val()],b=[];a.el.find("[name=NCV02]").length?($this=a.el.find("[name=NCV02]"),b.push($this.val()),b.push(a.el.find("[name=NCV]").val())):($this=a.el.find("[name=NCV]"),b.push($this.val()));var c=null;if(a.required.indexOf("NCV")>-1){if(""==$.trim(b[0]))return BaseError($this,"请输入低位热值",0);if(b[1]&&""==$.trim(b[1]))return BaseError($this,"请输入低位热值",0)}else if(""==$.trim(b[0])&&"undefined"!=typeof b[1]&&""==$.trim(b[1]))return BaseError($this,"");return $.each(b,function(a,b){return Number(b)>7500||Number(b)<=0||!regluar.test(b)?(c=BaseError($this,"请填写1-7500之间的整数",0),!1):checkDigit(b)?""==$.trim(b)?(c=BaseError($this,"请输入低位热值",0),!1):void 0:(c=BaseError($this,"请输入合法的数字格式",0),!1)}),null!==c?c:BaseError($this,"")}function checkRS(){var a=currCoalTypeMapping[$("#pname").val()],b=a.el.find("[name=RS]"),c=(b.val(),null),d=[];a.el.find("[name=RS02]").length?(b=a.el.find("[name=RS02]"),d.push(b.val()),d.push(a.el.find("[name=RS]").val())):(b=a.el.find("[name=RS]"),d.push(b.val()));var c=null;return $.each(d,function(a,d){return""==$.trim(d)?(c=BaseError(b,"请输入收到基硫分",0),!1):checkDigit(d)?Number(d)>=10||Number(d)<=0||!digit.test(d)?(c=BaseError(b,"请填写0-10之间的数值[不包括0和10]",0),!1):reg.test(d)?void 0:(c=BaseError(b,"收到基硫分最多包含两位小数",0),!1):(c=BaseError(b,"请输入合法的数字格式",0),!1)}),null!==c?c:BaseError(b,"")}function checkADV(){var a=currCoalTypeMapping[$("#pname").val()],b=[];a.el.find("[name=ADV02]").length?($this=a.el.find("[name=ADV02]"),b.push($this.val()),b.push(a.el.find("[name=ADV]").val())):($this=a.el.find("[name=ADV]"),b.push($this.val()));var c=null;return $.each(b,function(a,b){return""==$.trim(b)?(c=BaseError($this,"请输入空干基挥发分",0),!1):checkDigit(b)?Number(b)>50||Number(b)<=0||!digit.test(b)?(c=BaseError($this,"请填写0-50之间的数值[不包括0]",0),!1):reg.test(b)?void 0:(c=BaseError($this,"空干基挥发分最多包含两位小数",0),!1):(c=BaseError($this,"请输入合法的数字格式",0),!1)}),null!==c?c:BaseError($this,"")}function checkTM(){var a=currCoalTypeMapping[$("#pname").val()],b=a.el.find("[name=TM]"),c=(b.val(),[]);a.el.find("[name=TM02]").length?(b=a.el.find("[name=TM02]"),c.push(b.val()),c.push(a.el.find("[name=TM]").val())):(b=a.el.find("[name=TM]"),c.push(b.val()));var d=null;return $.each(c,function(a,c){return""==$.trim(c)?(d=BaseError(b,"请输入全水分",0),!1):checkDigit(c)?Number(c)>50||Number(c)<=0||!digit.test(c)?(d=BaseError(b,"请填写0-50之间的数值[不包括0]",0),!1):reg.test(c)?void 0:(d=BaseError(b,"全水分最多包含两位小数",0),!1):(d=BaseError(b,"请输入合法的数字格式",0),!1)}),null!==d?d:BaseError(b,"")}function checkADS(){var a=currCoalTypeMapping[$("#pname").val()],b=[];a.el.find("[name=ADS02]").length?($this=a.el.find("[name=ADS02]"),b.push($this.val()),b.push(a.el.find("[name=ADS]").val())):($this=a.el.find("[name=ADS]"),b.push($this.val()));var c=null;if(a.required.indexOf("ADS")>-1){if(""==$.trim(b[0]))return BaseError($this,"请输入空干基硫分",0);if(b[1]&&""==$.trim(b[1]))return BaseError($this,"请输入空干基硫分",0)}else if(""==$.trim(b[0])&&"undefined"!=typeof b[1]&&""==$.trim(b[1]))return BaseError($this,"");return $.each(b,function(a,b){return""==$.trim(b)?(c=BaseError($this,"请输入空干基硫分",0),!1):checkDigit(b)?Number(b)>10||Number(b)<=0||!digit.test(b)?(c=BaseError($this,"请填写0-10之间的数值[不包括0]",0),!1):reg.test(b)?void 0:(c=BaseError($this,"空干基硫分最多包含两位小数",0),!1):(c=BaseError($this,"请输入合法的数字格式",0),!1)}),null!==c?c:BaseError($this,"")}function checkRV(){var a=currCoalTypeMapping[$("#pname").val()],b=[];a.el.find("[name=RV02]").length?($this=a.el.find("[name=RV02]"),b.push($this.val()),b.push(a.el.find("[name=RV]").val())):($this=a.el.find("[name=RV]"),b.push($this.val()));var c=null;if(a.required.indexOf("RV")>-1){if(""==$.trim(b[0]))return BaseError($this,"请输入收到基挥发分",0);if(b[1]&&""==$.trim(b[1]))return BaseError($this,"请输入收到基挥发分",0)}else if(""==$.trim(b[0])&&"undefined"!=typeof b[1]&&""==$.trim(b[1]))return BaseError($this,"");return $.each(b,function(a,b){return Number(b)>50||Number(b)<=0||!digit.test(b)?(c=BaseError($this,"请填写0-50之间的数值[不包括0]",0),!1):reg.test(b)?checkDigit(b)?void 0:(c=BaseError($this,"请输入合法的数字格式",0),!1):(c=BaseError($this,"收到基挥发分最多包含两位小数",0),!1)}),null!==c?c:BaseError($this,"")}function checkIM(){var a=currCoalTypeMapping[$("#pname").val()],b=[];a.el.find("[name=IM02]").length?($this=a.el.find("[name=IM02]"),b.push($this.val()),b.push(a.el.find("[name=IM]").val())):($this=a.el.find("[name=IM]"),b.push($this.val()));var c=null;if(a.required.indexOf("IM")>-1){if(""==$.trim(b[0]))return BaseError($this,"请输入内水分",0);if(b[1]&&""==$.trim(b[1]))return BaseError($this,"请输入内水分",0)}else if(""==$.trim(b[0])&&"undefined"!=typeof b[1]&&""==$.trim(b[1]))return BaseError($this,"");return $.each(b,function(a,b){return""==$.trim(b)?(c=BaseError($this,"请输入内水分",0),!1):checkDigit(b)?Number(b)>50||Number(b)<=0||!digit.test(b)?(c=BaseError($this,"请填写0-50之间的数值[不包括0]",0),!1):reg.test(b)?void 0:(c=BaseError($this,"内水分最多包含两位小数",0),!1):(c=BaseError($this,"请输入合法的数字格式",0),!1)}),null!==c?c:BaseError($this,"")}function checkAFT(){var a=currCoalTypeMapping[$("#pname").val()],b=a.el.find("[name=AFT]"),c=b.val();return""!=c?regluar.test(c)?Number(c)>1600||Number(c)<900?BaseError(b,"请填写900-1600之间的整数",0):BaseError(b,""):BaseError(b,"请填写900-1600之间的整数",0):BaseError(b,"")}function checkASH(){var a=currCoalTypeMapping[$("#pname").val()],b=a.el.find("[name=ASH]"),c=(b.val(),[]);a.el.find("[name=ASH02]").length?(b=a.el.find("[name=ASH02]"),c.push(b.val()),c.push(a.el.find("[name=ASH]").val())):(b=a.el.find("[name=ASH]"),c.push(b.val()));var d=null;if(a.required.indexOf("ASH")>-1){if(""==$.trim(c[0]))return BaseError(b,"请输入灰分",0);if(c[1]&&""==$.trim(c[1]))return BaseError(b,"请输入灰分",0)}else if(""==$.trim(c[0])&&"undefined"!=typeof c[1]&&""==$.trim(c[1]))return BaseError(b,"");return $.each(c,function(a,c){return""==$.trim(c)?(d=BaseError(b,"请输入灰分",0),!1):checkDigit(c)?Number(c)>50||Number(c)<=0||!digit.test(c)?(d=BaseError(b,"请填写0-50之间的数值[不包括0]",0),!1):pattern.test(c)?void 0:(d=BaseError(b,"灰分最多包含一位小数",0),!1):(d=BaseError(b,"请输入合法的数字格式",0),!1)}),null!==d?d:BaseError(b,"")}function checkHGI(){var a=currCoalTypeMapping[$("#pname").val()],b=a.el.find("[name=HGI]"),c=b.val();return""!=c?regluar.test(c)?Number(c)>100?BaseError(b,"请填写0-100之间的整数[不包括0]",0):BaseError(b,""):BaseError(b,"请填写0-100之间的整数[不包括0]",0):BaseError(b,"")}function checkQuoteEndDate(){var a=$("#quoteenddate"),b=($("#deliverydate").val(),$("#currentTime").val()),c=a.val();return""==c?BaseError(a,"请输入报价截止日",0):dateCompare(b,c)?BaseError(a,""):BaseError(a,"报价截止日不能少于当前日","0")}function checkDeliveryDate(){var a=$("#deliverydate"),b=$("#quoteenddate").val(),c=a.val();return""==c?BaseError(a,"请输入提货时间",0):""!=b?days(b,c)<3?BaseError(a,"提货时间至少要比报价截止日期晚3天",0):BaseError(a,""):(BaseError(a,""),void checkQuoteEndDate())}function checkDeliveryDateStart(){var a=$("#deliverydatestart"),b=$("#quoteenddate").val(),c=a.val();return""==c?BaseError(a,"请输入提货开始时间",0):""==b?(BaseError(a,""),checkQuoteEndDate(),!1):days(b,c)<3?BaseError(a,"提货时间至少要比报价截止日期晚3天",0):BaseError(a,"")}function checkDeliveryDateEnd(){var a=$("#deliverydateend"),b=$("#deliverydatestart").val(),c=a.val();return""==c?BaseError(a,"请输入提货结束时间",0):""==b?(BaseError(a,""),checkDeliveryDateStart(),!1):dateCompareNotEqual(b,c)?BaseError(a,""):BaseError(a,"结束时间必须在开始时间之后",0)}function checkQuoteTime1(){var a,b=$("#deliveryTime1");a="港口平仓"==$("#deliveryMode").val()||"到岸舱底"==$("#deliveryMode").val()?$("#deliverySingleDate").val():$("#deliveryDoubleDate").val();var c=b.val();return""==c?BaseError(b,"请输入供货开始时间",0):dateCompareQuote(a,c)?BaseError(b,""):BaseError(b,"您填写的时间不能小于提货时间",0)}function checkQuoteTime2(){var a,b=$("#deliveryTime2"),c=$("#deliveryTime1").val();a="港口平仓"==$("#deliveryMode").val()||"到岸舱底"==$("#deliveryMode").val()?$("#deliverySingleDate").val():$("#deliveryDoubleDate").val();var d=b.val();return""==d?BaseError(b,"请输入供货结束时间",0):dateCompareNotEqual(c,d)?dateCompareNotEqual(a,d)?BaseError(b,""):BaseError(b,"您填写的时间必须在提货时间之后",0):BaseError(b,"结束时间必须在开始时间之后",0)}function checkQuoteAmount(){var $this=$("#quoteAmount"),result=$this.val(),$demandAmount=$("#demandCount").val();return""==result?BaseError($this,"请输入吨数",0):regluar.test(result)?Number(result)<50?BaseError($this,"吨数应大于50",0):Number(result)>=999999?BaseError($this,"吨数应小于999999",0):eval($demandAmount)<eval(result)?BaseError($this,"申供吨数应小于需求数量",0):BaseError($this,""):BaseError($this,"吨数为正整数",0)}function checkQuotePrice(){var a=$("#quotePrice"),b=a.val();return""==$.trim(b)?BaseError(a,"请填写价格",0):isNaN(b)?BaseError(a,"请填写正确的价格",0):reg.test(b)?Number(b)<50||Number(b)>1200?BaseError(a,"价格应为50-1200之间",0):BaseError(a,""):BaseError(a,"价格最多包含2位小数",0)}function checkStartPort(){var a=$("#startPort"),b=a.val();return""==$.trim(b)?BaseError(a,"请填写发运站/港",0):b.length>30?BaseError(a,"发运站/港长度不能超过30",0):BaseError(a,"")}function checkEndPort(){var a=$("#endPort"),b=a.val();return""==$.trim(b)?BaseError(a,"请填写交货站/港",0):b.length>30?BaseError(a,"交货站/港长度不能超过30",0):BaseError(a,"")}function checkOriginalPlace(){var a=$("#originalPlace"),b=a.val();return""==b?BaseError(a,"请输入产地",0):b.trim()?BaseError(a,""):BaseError(a,"产地输入不得为空",0)}function checkPlace(){var a=$("#deliveryplace"),b=$("#otherplace");return"-1"==a.val()?""==b.val()?BaseError(b,"请输入详细地址",0):b.val().length>20?BaseError(b,"详细地址最多为20位",0):BaseError(b):BaseError(b)}function checkOrg(){var a=$("#inspectionagency"),b=$("#otherorg");return"其它"==a.val()?""==b.val()?BaseError(b,"请输入详细检验机构",0):b.val().length>20?BaseError(b,"检验机构最多为20位",0):BaseError(b):BaseError(b)}function checkMode(){var a=$("#deliverymode").val();return"港口平仓"==a||"到岸舱底"==a?checkDeliveryDate():checkDeliveryDateStart()&checkDeliveryDateEnd()}function checkComment(){var a=$("#releasecomment"),b=a.val();return""!=b&&b.length>200?BaseError(a,"备注最多为200位",0):BaseError(a,"")}function checkComment2(){var a=$("#releaseremarks"),b=a.val();return""!=b&&b.length>200?BaseError(a,"备注最多为200位",0):BaseError(a,"")}function checkOriginPlace(){var a=currCoalTypeMapping[$("#pname").val()],b=a.el.find("[name=originplace]"),c=b.val();return""==c?BaseError(b,"请输入产地",0):c.length>50?BaseError(b,"产地最多为50位",0):c.trim()?BaseError(b,""):BaseError(b,"产地输入不得为空",0)}function checkDeliveryTime1(){var a=$("#deliverytime1"),b=($("#deliverytime2").val(),a.val()),c=a.attr("min");return""==b?BaseError(a,"请输入交货时间",0):days(c,b)<0?BaseError(a,"交货时间不能少于当前日","0"):BaseError(a,"")}function checkDeliveryTime2(){var a=$("#deliverytime2"),b=$("#deliverytime1").val(),c=a.val();return""==c?BaseError(a,"请输入交货时间",0):""!=b?days(b,c)<1?BaseError(a,"后面的日期应大于前面的日期",0):BaseError(a,""):(BaseError(a,""),void checkDeliveryTime1())}function checkShopSupplyAmount(){var a=$("#supplyquantity"),b=a.val();return""==b?($(".totalcost input").val("0"),BaseError(a,"请填写您要认购的吨数",0)):regluar.test(b)?Number(b)<50?($(".totalcost input").val("0"),BaseError(a,"认购吨数需大于50吨",0)):Number(b)>=999999?($(".totalcost input").val("0"),BaseError(a,"认购吨数应小于999999吨",0)):Number(b)>$(".restWeight").text().replace(/,/g,"").replace(/吨/g,"")?($(".totalcost input").val("0"),BaseError(a,"不可以大于剩余库存量",0)):BaseError(a,""):($(".totalcost input").val("0"),BaseError(a,"认购吨数应为正整数",0))}function checkShopDeliveryTime1(){var a=$("#deliverytime1"),b=($("#deliverytime2").val(),a.val()),c=a.attr("mindate");return""==b?BaseError(a,"请输入提货时间",0):$(".jhendtime").length&&new Date($("#deliverytime1").val()).getTime()-new Date($(".jhendtime").text()).getTime()>0?BaseError(a,"不能超过交货时间",0):new Date($("#deliverytime1").val()).getTime()<new Date($.trim($("#jhstarttime").text())).getTime()?BaseError(a,"不能小于交货时间",0):days(c,b)<0?BaseError(a,"提货时间不能少于当前日",0):BaseError(a,"")}function checkShopDeliveryTime2(){var a=$("#deliverytime2"),b=$("#deliverytime1").val(),c=a.val();return""==c?BaseError(a,"请输入提货时间",0):""!=b?days(b,c)<1?BaseError(a,"后面的日期应大于前面的日期",0):$(".jhendtime").length&&new Date($("#deliverytime2").val()).getTime()-new Date($(".jhendtime").text()).getTime()>0?BaseError(a,"不能超过交货时间",0):BaseError(a,""):(BaseError(a,""),void checkDeliveryTime1())}function checkPort(){var a=$("#portId"),b=$("#otherharbour");return"-1"==a.val()?""==b.val()?BaseError(b,"请输入详细地址",0):b.val().trim()?b.val().length>20?BaseError(b,"详细地址最多为20位",0):BaseError(b,""):BaseError(b,"请输入详细地址",0):BaseError(b,"")}function checkInspectOrg(){var a=$("#inspectorg"),b=$("#otherinspectorg");return"其它"==a.val()?""==b.val()?BaseError(b,"请输入详细检验机构",0):b.val().length>20?BaseError(b,"检验机构最多为20位",0):b.val().trim()?BaseError(b,""):BaseError(b,"请输入详细检验机构",0):BaseError(b,"")}function checkPrice(){var a=$("#ykjSelect").hasClass("selected");if(a){var b=$("#ykj");return cCheckPrice(b,b.val())}return checkJtj()&checkJtjAmount()}function checkJtjAmount(){var a=!0;return $("input[id^='jtjAmount']").each(function(b){var c=$(this);return a?"jtjAmount01"==c.attr("id")?!0:void(a=a&&cCheckAmount($("#jtjDunError"),c.val())):a}),a&&isNextJtjAmountMore("#jtjAmount",0,"#jtjMoreError")}function isNextJtjAmountMore(a,b,c){var d=b+1,e=$(a+b+"2"),f=$(a+d+"2");return 0==f.size()||0==e.size()?BaseError($("#jtjMoreError"),""):Number(f.val())>Number(e.val())?isNextJtjAmountMore(a,d,c):BaseError($(c),"后面的吨数应大于前面的吨数",0)}function checkJtj(){var a=!0;return $("input[id^='jtjPrice']").each(function(b){var c=$(this);return a?void(a=a&&cCheckJtPrice($("#jtjPriceError"),c.val())):a}),a}function cCheckAmount(a,b){return""==$.trim(b)?BaseError(a,"请填写吨数",0):digit.test(b)?(b=Number(b),50>b||b>999999?BaseError(a,"请填写50-999999的数值",0):BaseError(a,"")):BaseError(a,"请填写正确的吨数",0)}function cCheckPrice(a,b){return""==$.trim(b)?BaseError(a,"请填写价格",0):isNaN(b)?BaseError(a,"请填写正确的价格",0):reg.test(b)?(b=Number(b),1>b||b>1500?BaseError(a,"请填写1-1500之间的数",0):BaseError(a,"")):BaseError(a,"价格最多包含两位小数",0)}function cCheckJtPrice(a,b){return""==$.trim(b)?BaseJtError(a,"请填写价格",0):isNaN(b)?BaseJtError(a,"请填写正确的价格",0):reg.test(b)?Number(b)<1||Number(b)>1500?BaseJtError(a,"请填写1-1500之间的数",0):BaseJtError(a,""):BaseJtError(a,"价格最多包含两位小数",0)}function checkLinkman(){var a=$("#linktype").val(),b=!0,c=!0;if(1==a){var d=$("#linkmanname"),e=d.val(),f=$("#linkmanphone"),g=$.trim(f.val());b=""==$.trim(e)?BaseError(d,"请填写联系人",0):BaseError(d,""),e.length>20&&(b=BaseError(d,"最多可以输入20个字",0)),c=""==g?BaseError(f,"请填写手机号码",0):phoneRegex.test(g)?BaseError(f,""):BaseError(f,"请填写正确的手机号码",0)}return b&c}function days(a,b){var c=a,d=b,e=new Date(c.replace(/-,/g,"/")).getTime(),f=new Date(d.replace(/-,/g,"/")).getTime();return(f-e)/36e5/24}function dateCompare(a,b){console.log(a),console.log(b);var c=new Date(a.replace(/-,/g,"/")).getTime(),d=new Date(b.replace(/-,/g,"/")).getTime();return c>d?!1:!0}function dateCompareNotEqual(a,b){var c=new Date(a.replace(/-,/g,"/")).getTime(),d=new Date(b.replace(/-,/g,"/")).getTime();return c>=d?!1:!0}function dateCompareQuote(a,b){var c=new Date(a.replace(/-,/g,"/")).getTime(),d=new Date(b.replace(/-,/g,"/")).getTime();return c>d?!1:!0}function BaseError(a,b,c){return a.parent().parent().find($(".error_span")).remove(),0==c?(a.parent().parent().append("<span class='error_span'>"+b+"</span>"),void 0===errList?(errList=[],errList.push(a)):errList.push(a),console.log(a),!1):!0}function BaseError2(a,b,c){return a.parent().find($(".error_span")).remove(),0==c?(a.parent().append("<span class='error_span'>"+b+"</span>"),void 0===errList?(errList=[],errList.push(a)):errList.push(a),console.log(a),!1):!0}function BaseJtError(a,b,c){return a.text(""),a.removeClass("error_span2"),0==c?(a.text(b),a.addClass("error_span2"),void 0===errList?(errList=[],errList.push(a)):errList.push(a),console.log(a),!1):!0}function completeCompanyInfo(a){showNewModal(a.errMsg),"waitComplete"===a.field||"noPass"===a.field?$(".md-operate").attr("id","showRelease").text("去完善信息").on("tap",function(){location.href="/m/account/companyInfoLicence"}):$(".md-operate").on("click",function(){hideNewModal()})}function checkFC(){var a=currCoalTypeMapping[$("#pname").val()],b=[];a.el.find("[name=FC02]").length?($this=a.el.find("[name=FC02]"),b.push($this.val()),b.push(a.el.find("[name=FC]").val())):($this=a.el.find("[name=FC]"),b.push($this.val()));var c=null;return""==$.trim(b[0])&&"undefined"!=typeof b[1]&&""==$.trim(b[1])?BaseError($this,""):($.each(b,function(a,b){return!regluar.test(b)||Number(b)>100?(c=BaseError($this,"请填写0-100之间的整数[不包括0]",0),!1):void 0}),null!==c?c:BaseError($this,""))}function checkGV(){var a=currCoalTypeMapping[$("#pname").val()],b=a.el.find("[name=GV]"),c=b.val();return""!=c?regluar.test(c)?Number(c)>100?BaseError(b,"请填写0-100之间的整数[不包括0]",0):BaseError(b,""):BaseError(b,"请填写0-100之间的整数[不包括0]",0):a.required.indexOf("GV")>-1?BaseError(b,"请输入G值",0):BaseError(b,"")}function checkYV(){var a=currCoalTypeMapping[$("#pname").val()],b=a.el.find("[name=YV]"),c=b.val();return""!=c?regluar.test(c)?Number(c)>100?BaseError(b,"请填写0-100之间的整数[不包括0]",0):BaseError(b,""):BaseError(b,"请填写0-100之间的整数[不包括0]",0):a.required.indexOf("YV")>-1?BaseError(b,"请输入Y值",0):BaseError(b,"")}function checkCRC1(){var a=currCoalTypeMapping[$("#pname").val()],b=[],c=a.el.find("[name=CRC]"),d=a.el.find("[name=CRC]"),e=a.el.find("[name=CRC02]");if(e.length&&(b.push(d.val()),b.push(e.val())),a.required.indexOf("CRC")<=-1&&""==$.trim(b[0])&&""==$.trim(b[1]))return BaseError(c,"");if(""==$.trim(b[0])||""==$.trim(b[1]))return BaseError(c,"请输入焦渣特征",0);var f=null;return $.each(b,function(a,b){return!regluar.test(b)||Number(b)>50||Number(b)<1?(f=BaseError(c,"请输入1-50之间的整数",0),!1):void 0}),null!=f?f:BaseError(c,"")}function checkGV1(){var a=currCoalTypeMapping[$("#pname").val()],b=[];a.el.find("[name=GV02]").length?($this=a.el.find("[name=GV02]"),b.push($this.val()),b.push(a.el.find("[name=GV]").val())):($this=a.el.find("[name=GV]"),b.push($this.val()));var c=null;if(a.required.indexOf("GV")>-1){if(""==$.trim(b[0]))return BaseError($this,"请输入G值",0);if(b[1]&&""==$.trim(b[1]))return BaseError($this,"请输入G值",0)}else if(""==$.trim(b[0])&&"undefined"!=typeof b[1]&&""==$.trim(b[1]))return BaseError($this,"");return $.each(b,function(a,b){return!regluar.test(b)||Number(b)>100?(c=BaseError($this,"请填写0-100之间的整数[不包括0]",0),!1):void 0}),null!==c?c:BaseError($this,"")}function checkYV1(){var a=currCoalTypeMapping[$("#pname").val()],b=[];a.el.find("[name=YV02]").length?($this=a.el.find("[name=YV02]"),b.push($this.val()),b.push(a.el.find("[name=YV]").val())):($this=a.el.find("[name=YV]"),b.push($this.val()));var c=null;if(a.required.indexOf("YV")>-1){if(""==$.trim(b[0]))return BaseError($this,"请输入Y值",0);if(b[1]&&""==$.trim(b[1]))return BaseError($this,"请输入Y值",0)}else if(""==$.trim(b[0])&&"undefined"!=typeof b[1]&&""==$.trim(b[1]))return BaseError($this,"");return $.each(b,function(a,b){return!regluar.test(b)||Number(b)>100?(c=BaseError($this,"请填写0-100之间的整数[不包括0]",0),!1):void 0}),null!==c?c:BaseError($this,"")}function checkTax(){return notNullCheck($("#tax"),"请选择价格是否含税")}function checkPromotion(){return notNullCheck($("#promotion"),"请选择是否需要促销活动")}function checkFinance(){return notNullCheck($("#finance"),"请选择是否需要融资服务")}function checkLogistics(){return notNullCheck($("#logistics"),"请选择是否需要物流服务")}function notNullCheck(a,b){var c=$.trim(a.val());return""==c||0==c?BaseError2(a,b,0):BaseError2(a,"")}window.se={session:window.sessionStorage,set:function(a,b){return this.session.setItem(a,b),this},get:function(a){return this.session.getItem(a)},key:function(a){return this.session.key(a)},remove:function(a){return this.session.removeItem(a),this},clear:function(){window.sessionStorage.clear()},log:function(){for(var a in this.session){var b=this.session.getItem(a);console.log(a+" = "+b)}}};var showServerError=function(a,b){var c=$("#md-btn-2");c.off(),c.tap(function(){""!=$.trim(b)?location.href=b:setTimeout("history.go(0);",300)}),console.log(a+" -- ajax common error.."),showModal(a)};$(".md-close-modal").on("click",function(){hideNewModal()}),window.errList=[];var regluar=/^[1-9]+[0-9]*]*$/,reg=/^[0-9]+(.[0-9]{1,2})?$/,digit=/^[(\d+\.\d+)|(\d+)|(\.\d+)]+$/,digit2=/^[1-9]\d*$/,pattern=/^[0-9]+(.[0-9]{1})?$/,phoneRegex=/^0?(13[0-9]|17[0-9]|15[0-9]|18[0-9]|14[57])[0-9]{8}$/,currCoalTypeMapping={"动力煤":{el:$("#filter1"),required:"NCV"},"无烟煤":{el:$("#filter1"),required:"NCV"},"喷吹煤":{el:$("#filter2"),required:"ASH@TM@NCV"},"焦煤":{el:$("#filter3"),required:"ASH@GV@YV@CRC"}};return{se:se,showModal:showModal,hideModal:hideModal,showNewModal:showNewModal,hideNewModal:hideNewModal,checkIsLogin:checkIsLogin,showLoading:showLoading,hideLoading:hideLoading,showError:showError,hideError:hideError,errorHandler:errorHandler,showServerError:showServerError,showModalMsg:showModalMsg,offModal1Events:offModal1Events,closeEvent4AllModals:closeEvent4AllModals,hideAllModal:hideAllModal,refreshPage:refreshPage,isKeyinReferrer:isKeyinReferrer,isKeyinPath:isKeyinPath,checkNCV:checkNCV,checkFC:checkFC,checkGV:checkGV,checkGV1:checkGV1,checkYV1:checkYV1,checkYV:checkYV,checkSupplyAmount:checkSupplyAmount,checkShopSupplyAmount:checkShopSupplyAmount,checkDemandAmount:checkDemandAmount,checkRS:checkRS,checkADV:checkADV,checkTM:checkTM,checkIM:checkIM,checkADS:checkADS,checkRV:checkRV,checkAFT:checkAFT,checkASH:checkASH,checkHGI:checkHGI,checkDeliveryDate:checkDeliveryDate,checkDeliveryDateStart:checkDeliveryDateStart,checkDeliveryDateEnd:checkDeliveryDateEnd,checkPlace:checkPlace,checkOrg:checkOrg,checkMode:checkMode,checkQuoteEndDate:checkQuoteEndDate,checkOriginPlace:checkOriginPlace,checkComment:checkComment,checkComment2:checkComment2,checkDeliveryTime1:checkDeliveryTime1,checkDeliveryTime2:checkDeliveryTime2,checkShopDeliveryTime1:checkShopDeliveryTime1,checkShopDeliveryTime2:checkShopDeliveryTime2,checkLinkman:checkLinkman,checkPrice:checkPrice,checkJtj:checkJtj,checkInspectOrg:checkInspectOrg,checkPort:checkPort,completeCompanyInfo:completeCompanyInfo,checkQuoteTime1:checkQuoteTime1,checkQuoteTime2:checkQuoteTime2,checkQuoteAmount:checkQuoteAmount,checkQuotePrice:checkQuotePrice,checkOriginalPlace:checkOriginalPlace,checkStartPort:checkStartPort,checkEndPort:checkEndPort,checkBrandName:checkBrandName,checkCRC1:checkCRC1,checkPromotion:checkPromotion,checkFinance:checkFinance,checkLogistics:checkLogistics,checkTax:checkTax}});