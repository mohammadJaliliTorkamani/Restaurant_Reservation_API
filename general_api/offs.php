<?php
header('Access-Control-Allow-Origin: *'); //allow everybody
define('HOSTNAME', 'localhost');
define('USERNAME', 'cpres873_Aban');
define('PASSWORD', 'KimiaAndMohammad');
define('DATABASE', 'cpres873_KNTU_Database');

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');

if ($connect) {
    mysqli_set_charset($connect, "utf8");
    $headers = getallheaders();
    $response = [];

    $query = "SELECT id,percentage FROM Off WHERE is_valid = '1'";
    $qRes = mysqli_query($connect, $query);
    while ($res = mysqli_fetch_assoc($qRes)) {
        $item['percentage'] = $res['percentage'];
        $item['id'] = $res['id'];
        array_push($response, $item);
    }
    die(json_encode($response));
}
?>