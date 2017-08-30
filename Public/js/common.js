//common.js

$(function () {

    //系统时间
    var systemTime = null;
    var systemTimeBox = $(".system-time");
    function setSystemTime() {
        systemTime = new Date();
        var y = systemTime.getFullYear(),
            m = ("0" + (systemTime.getMonth() + 1)).substr(-2,2),
            d = ("0" + systemTime.getDate()).substr(-2,2),
            h = ("0" + systemTime.getHours()).substr(-2,2),
            i = ("0" + systemTime.getMinutes()).substr(-2,2),
            s = ("0" + systemTime.getSeconds()).substr(-2,2);

        systemTimeBox.html(y + "/" + m + "/" + d + "&nbsp;" + h + ":" + i + ":" + s);
        setTimeout(setSystemTime,1000);
    }
    setSystemTime();

    //侧栏菜单高度
    var sideBar = $(".side-bar");
    sideBar.height($(window).innerHeight() - 50);
    $(window).resize(function () {
        sideBar.height($(window).innerHeight() - 50);
    });

    //列表箭头切换
    $("#accordion .panel-heading").click(function (){
        $("#accordion .panel-heading").not(this).find(".glyphicon-triangle-bottom").removeClass("glyphicon-triangle-bottom").addClass("glyphicon-triangle-right");
        $(this).find(".glyphicon").toggleClass("glyphicon-triangle-bottom glyphicon-triangle-right");
    });

    //切换侧栏菜单
    var toggle_sidebar = true;
    $(".toggle-sidebar").click(function () {
        $(this).children().toggleClass("glyphicon-menu-left glyphicon-menu-right");
        if(toggle_sidebar){
            sideBar.css("left",-240);
            $(".content").css("margin-left",20);
            $(this).attr("title","显示菜单栏");
            toggle_sidebar = false;
        }else{
            sideBar.css("left",0);
            $(".content").css("margin-left",260);
            $(this).attr("title","隐藏菜单栏");
            toggle_sidebar = true;
        };
    });

    //分页功能
    // $(".mwt-pagination .dropdown-menu a").click(function () {
    //     $(this).parents(".dropdown").find(".btn").text($(this).text());
    // });

    function c_td(html,tag) {
        var tagName = tag || "td";
        var el = document.createElement(tagName);
        el.innerHTML = html || "";
        return el;
    }

    function getData(item,page){
        var table = item.parents("table");
        var url = table.attr("data-url");
        var info = eval(table.attr("data-info"));
        $.post(url,{current : page},function (res) {
            var data = JSON.parse(res);
            var pageSize = Math.ceil(data.count / 10);
            item.attr("data-total",pageSize);
            item.find(".total").html("共" + data.count + "条/ 共" + pageSize + "页");
            table.find("tbody").html("");
            for(var i = 0; i < data.list.length; i++){
                var list = data.list[i];
                var row = c_td("","tr");
                for(var j = 0; j < info.length; j++){
                    var td = c_td(list[info[j]]);
                    $(row).append(td);
                }
                table.find("tbody").append(row);
            }
        });
    }

    $(".mwt-pagination").each(function (i) {
        getData($(this),1);
        $(this).pagination(getData);
    });

});