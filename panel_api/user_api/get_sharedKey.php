<?php
header('Access-Control-Allow-Origin: *'); //allow everybody  

define('HOSTNAME', 'localhost');
define('USERNAME', 'lexeense_admin');
define('PASSWORD', 'admin@lexeen123_#');
define('DATABASE', 'lexeense_Main_DB');

date_default_timezone_set("Asia/Tehran");
$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
if ($connect) {

    $token = $_GET['Token'];
    $query = "SELECT shared_key FROM Token WHERE value = '$token' AND deleted='0'";
    $res = mysqli_query($connect, $query);
    if (mysqli_num_rows($res) == 1) {
        $fResult = mysqli_fetch_assoc($res);
        $response['code'] = 200;
        $response['shared_key'] = $fResult['shared_key'];
    } else {
        $response['code'] = 100;
        $response['message'] = 'چنین کاربری وجود ندارد !';
    }
    die(json_encode($response));
}
