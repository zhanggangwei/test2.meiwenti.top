
function createArr(start,end) {
    var arr = [];
    for(var i = start; i < end; i++){
        arr.push(Math.floor(Math.random() * end) + 1);
    }
    return arr;
}


$(function () {

    var train_option = {
        title : {
            text : "车次统计"
        },
        xAxis : {
            name : "日期",
            data : (function () {
                var arr = [];
                for(var i = 1; i <= 31; i++){
                    arr.push(i);
                };
                return arr;
            })()
        },
        yAxis : {
            name : "车次"
        },
        tooltip : {
            trigger : "axis",
            axisPointer : {
                type : "shadow"
            }
        },
        dataZoom: [{
            type: 'inside',
            start: 0,
            end: 100
        }],
        series : [
            {
                name : "AAA",
                type : "bar",
                data : createArr(1,24),
                label : {
                    emphasis : {
                        show : true,
                        position : "outside",
                        formatter : "{c}车"
                    }
                },
                barWidth : "40%"
            }
        ]
    };

    train_option.subjoin = {
            day : {
                xAxisData : [train_option.xAxis.data[22]],
                seriesData : [train_option.series[0].data[22]],
                itemWidth : 50
            },
            week : {
                xAxisData : createArr(23-7,23),
                seriesData : train_option.series[0].data.filter(function (item,i) {
                    return i > 22 - 7;
                }),
                itemWidth : 30
            },
            month : {
                xAxisData : train_option.xAxis.data,
                seriesData : train_option.series[0].data,
                itemWidth : 5
            }
    };



    var tunnage_option = {
        title : {
            text : "吨数统计"
        },
        xAxis : {
            data : (function () {
                var arr = [];
                for(var i = 1; i <= 31; i++){
                    arr.push(i);
                };
                return arr;
            })(),
            name : "日期"
        },
        yAxis : {
            name : "吨数"
        },
        tooltip : {
            trigger : "axis",
            axisPointer : {
                type : "shadow"
            }
        },
        dataZoom: [{
            type: 'inside',
            start: 0,
            end: 100
        }],
        series : [
            {
                name : "BBB",
                type : "bar",
                data : createArr(1,24),
                label : {
                    emphasis : {
                        show : true,
                        position : "outside",
                        formatter : "{c}吨"
                    }
                },
                barWidth : "40%"
            }
        ]
    };



    var result_option = {
        title : {
            text : "招标量统计"
        },

        tooltip : {},
        series : [
            {
                name : "招标量",
                type : "pie",
                label : {
                    normal: {
                        position : "inner",
                        formatter : "{b}：{c}吨({d}%)"
                        // formatter: '{a|{a}}{abg|}\n{hr|}\n  {b|{b}：}{c}  {per|{d}%}  ',
                        // backgroundColor: '#eee',
                        // borderColor: '#aaa',
                        // borderWidth: 1,
                        // borderRadius: 4,
                        // rich: {
                        //     a: {
                        //         color: '#999',
                        //         lineHeight: 22,
                        //         align: 'center'
                        //     },
                        //     hr: {
                        //         borderColor: '#aaa',
                        //         width: '100%',
                        //         borderWidth: 0.5,
                        //         height: 0
                        //     },
                        //     b: {
                        //         fontSize: 16,
                        //         lineHeight: 33
                        //     },
                        //     per: {
                        //         color: '#eee',
                        //         backgroundColor: '#334455',
                        //         padding: [2, 4],
                        //         borderRadius: 2
                        //     }
                        // }
                    }
                },
                data : [
                    {value : 500, name : "本月已拉吨数"},
                    {value : 200, name : "待安排吨数"}
                ]
            }
        ]
    };




    var train_number = echarts.init($(".chart-bar").get(0));
    train_number.setOption(train_option);

    var tunnage = echarts.init($(".tunnage-bar").get(0));
    tunnage.setOption(tunnage_option);

    var result = echarts.init($(".result-pie").get(0));
    result.setOption(result_option);

    $(window).resize(function () {
        train_number.resize();
        tunnage.resize();
        result.resize();
    });

});