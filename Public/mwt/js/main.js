

var bus = new Vue();

window.addEventListener("load",function(){
	
	new Vue({
		el : ".navbar-header",
		data : {
			systemTime : ""
		},
		mounted : function(){
			function showSystemTime(_this){
				var date = new Date();
				var serializeDate = [
					date.getFullYear(),
					date.getMonth() + 1,
					date.getDate(),
					date.getHours(),
					date.getMinutes(),
					date.getSeconds()
				];
				for(var i = 0; i < serializeDate.length; i++) {
					var item = serializeDate[i];
					if(item < 10){
						item = "0" + item;
					};
					item = i < 2 ? item + "/" : i == 2 ? item + " " : i < 5 ? item + ":" : item;
					serializeDate[i] = item;
				};
				_this.systemTime = "";
				for (var i = 0; i < serializeDate.length; i++) {
					_this.systemTime += serializeDate[i];
				};
				setTimeout(function(){
					showSystemTime(_this);
				},1000);
			};
			showSystemTime(this);

			window.addEventListener("resize",function(){
				this.innerHeight = window.innerHeight;
			}.bind(this));
		}
	});

	sideMenu = new Vue({
		el : ".side-menu",
		data : {
			showSideMenu : true,
			iconArr : ["hzcz","hdgl","zycl","gzzc","hzgs","qbcl","xlgl","qxgl","xxzx"],
			sideListData : [
				{
					label : "合作车主",
					children : [
						{
							label : "添加合作车主",
							name : "a",
							link : ""
						},
						{
							label : "待确认列表",
							name : "b",
							link : ""
						},
						{
							label : "合作中车主",
							name : "c",
							link : ""
						},
						{
							label : "待核算列表",
							name : "d",
							link : ""
						},
						{
							label : "已经核算列表",
							name : "e",
							link : ""
						}

					]	
				},
				{
					label : "货单管理",
					children : [
						{
							label : "待结单",
							name : "f",
							link : "waitOrderReceiving.html"
						},
						{
							label : "司机退单",
							name : "g",
							link : "driverChargeback.html"
						},
						{
							label : "正在进行",
							name : "h",
							link : "underway.html"
						},
						{
							label : "手动结算",
							name : "i",
							link : "manualClearing.html"
						},
						{
							label : "已完成",
							name : "j",
							link : "completed.html"
						},
						{
							label : "退单记录",
							name : "k",
							link : "chargebackRecord.html"
						},
						{
							label : "派单历史查询",
							name : "l",
							link : ""
						}

					]	
				},
				{
					label : "自有车辆",
					children : [
						{
							label : "添加自有车辆",
							name : "m",
							link : ""
						},
						{
							label : "待审车辆",
							name : "n",
							link : ""
						},
						{
							label : "自有车辆",
							name : "o",
							link : ""
						}

					]	
				},
				{
					label : "故障自查",
					children : [
						{
							label : "车辆异常",
							name : "p",
							link : ""
						}
					]	
				},
				{
					label : "合作公司",
					children : [
						{
							label : "添加合作公司",
							name : "r",
							link : ""
						},
						{
							label : "待确认公司",
							name : "s",
							link : ""
						},
						{
							label : "合作中公司",
							name : "t",
							link : ""
						},
						{
							label : "分派历史",
							name : "u",
							link : ""
						},
						{
							label : "承运历史",
							name : "v",
							link : ""
						}

					]	
				},
				{
					label : "全部车辆",
					children : [
						{
							label : "车辆地图",
							name : "w",
							link : ""
						},
						{
							label : "车辆列表",
							name : "x",
							link : ""
						},
						{
							label : "车辆组织架构图",
							name : "y",
							link : ""
						}
					]	
				},
				{
					label : "线路管理",
					children : [
						{
							label : "添加路线",
							name : "z",
							link : ""
						},
						{
							label : "路线列表",
							name : "aa",
							link : ""
						}

					]	
				},
				{
					label : "权限管理",
					children : [
						{
							label : "分公司列表",
							name : "ab",
							link : ""
						},
						{
							label : "员工列表",
							name : "ac",
							link : ""
						},
						{
							label : "组织架构图",
							name : "ad",
							link : ""
						}

					]	
				},
				{
					label : "消息中心",
					children : [
						{
							label : "消息推送",
							name : "ae",
							link : ""
						},
						{
							label : "消息列表",
							name : "af",
							link : ""
						},
						{
							label : "招聘信息",
							name : "ag",
							link : ""
						}
					]	
				},
			]
		},
		methods : {
			toggleSideMenu : function(){
				this.showSideMenu = !this.showSideMenu;
				bus.$emit("sideMenuFlag",this.showSideMenu);
			}
		}
	});


	
});

