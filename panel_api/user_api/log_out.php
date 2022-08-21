<?php
header('Access-Control-Allow-Origin: *'); //allow everybody
require_once('../UserValidator.php');
require_once('../../PersianDate.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'lexeense_admin');
define('PASSWORD', 'admin@lexeen123_#');
define('DATABASE', 'lexeense_Main_DB');

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
date_default_timezone_set('Asia/Tehran');
if ($connect) {
    $token = null;
    $headers = getallheaders();
    $token = $_POST['Token'];
    $response = [];
    $UserValidator = new UserValidator($token);
    if ($UserValidator->isValidUser()) {
        $res = mysqli_query($connect, "SELECT id FROM Token WHERE VALUE = '$token'");
        $tokenID = mysqli_fetch_assoc($res)['id'];
        $dateTime = (new gregorian2jalali)->gregorian_to_jalali() . " " . date('H:i:s');
        $query = "INSERT INTO LogOut (token_id,date_time) VALUES('$tokenID','$dateTime')";
        mysqli_query($connect, $query);
        $response['code'] = 200;
        mysqli_query($connect, "UPDATE Token SET deleted = '1' WHERE value = '$token'");
        die(json_encode($response));
    }
}
