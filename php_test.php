<?php

phpinfo();
die();
/**
 * Created by PhpStorm.
 * User: fastdream
 * Date: 2017/5/18
 * Time: 14:32
 */
$mysql_conf = array(
    'host'    => 'rm-2zepex5z822a4680g.mysql.rds.aliyuncs.com',
    'db'      => 'mwt',
    'db_user' => 'zjw',
    'db_pwd'  => 'XXoo1314',
);

$mysqli = @new mysqli($mysql_conf['host'], $mysql_conf['db_user'], $mysql_conf['db_pwd']);
if ($mysqli->connect_errno) {
    die("could not connect to the database:\n" . $mysqli->connect_error);//诊断连接错误
}
$mysqli->query("set names 'utf8';");//编码转化
$select_db = $mysqli->select_db($mysql_conf['db']);
if (!$select_db) {
    die("could not connect to the db:\n" .  $mysqli->error);
}
$sql = "select ST_X(local_gps) as x,ST_Y(local_gps) as y from coal_users where id=100";
$res = $mysqli->query($sql);
if (!$res) {
    die("sql error:\n" . $mysqli->error);
}
while ($row = $res->fetch_assoc()) {

echo json_encode($row);
}

$res->free();
$mysqli->close();
