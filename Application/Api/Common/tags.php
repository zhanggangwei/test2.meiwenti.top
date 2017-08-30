<?php
/*****************************************************获取上个月的今天************************************************/
function last_month_today($time) {
    $time=$time?$time:strtotime(date('Y-m-d'));
    $last_month_time = mktime(date("G", $time), date("i", $time),
        date("s", $time), date("n", $time), - 1, date("Y", $time));
    return date(date("Y-m", $last_month_time) . "-d H:i:s", $time);
}
?>