
//pagination.js
$.fn.extend({
    "pagination" : function(fn){
        $.each(this,function(){
            var _this = $(this);
            var pages = _this.find("ul.pager-box li").not(".prev,.next");
            var prev = _this.find(".prev");
            var next = _this.find(".next");
            var to = _this.find("input");
            var total = Number(_this.attr("data-total")) || 5;
            var active = 1;
            var index = 1;

            prev.addClass("disabled");
            to.val(index);

            _this.click(function () {
                total = Number(_this.attr("data-total")) || 5;
            });

            prev.click(function(){
                next.removeClass("disabled");
                if(index > 1)index --;
                if(active > 1){
                    active --;
                    pages.removeClass("active").eq(active-1).addClass("active");
                }else if(index > 1){
                    pages.each(function(){
                        var a = $(this).find("a");
                        a.html(Number(a.html())-1);
                    });
                };
                if(index == 1){
                    prev.addClass("disabled");
                    pages.each(function(i){
                        $(this).find("a").html(i+1);
                    });
                };
                to.val(index);
                fn(_this,index);
            });

            next.click(function(){
                prev.removeClass("disabled");
                if(index < total)index ++;
                if(active < pages.length){
                    active ++;
                    pages.removeClass("active").eq(active-1).addClass("active");

                }else if(index < total){
                    pages.each(function(){
                        var a = $(this).find("a");
                        a.html(Number(a.html())+1);
                    });
                };
                if(index == total){
                    next.addClass("disabled");
                    pages.each(function(i){
                        $(this).find("a").html(total-4+i);
                    });
                };
                to.val(index);
                fn(_this,index);
            });

            pages.click(function(){
                pages.removeClass("active");
                active = $(this).addClass("active").index();
                index = $(this).find("a").html();
                if(index == 1){
                    prev.addClass("disabled");
                }else{
                    prev.removeClass("disabled")
                };
                if(index == pages.length){
                    next.addClass("disabled");
                }else{
                    next.removeClass("disabled")
                };
                to.val(index);
                fn(_this,index);
            });

            _this.find("input").keydown(function (ev) {
                var e = ev || window.event;
                var val = Number($(this).val());
                if(e.keyCode == 13 && val <= total && val > 0){
                    index = val;
                    pages.each(function (i) {
                        $(this).find("a").html(val+i);
                    }).removeClass("active").first().addClass("active");

                    if(index == 1){
                        prev.addClass("disabled");
                    }else{
                        prev.removeClass("disabled");
                    }
                    if(index == total){
                        next.addClass("disabled");
                    }else{
                        next.removeClass("disabled");
                    }

                    if(val <= total && val > total - 4){
                        pages.each(function (i) {
                            $(this).find("a").html(total-4+i);
                        }).removeClass("active").eq(val-total + 4).addClass("active");
                    }

                    fn(_this,index);
                }
            })
        });
        return this;
    }
});
