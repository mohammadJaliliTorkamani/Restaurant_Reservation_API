<?php
require_once('../UserValidator.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'cpres873_Aban');
define('PASSWORD', 'KimiaAndMohammad');
define('DATABASE', 'cpres873_KNTU_Database');

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
mysqli_set_charset($connect, "utf8");
if ($connect) {
    $token = null;
    $headers = getallheaders();
    foreach ($headers as $key => $val) {
        if (strcmp($key, "Token") == 0)
            $token = $val;
    }
    $UserValidator = new UserValidator($token);
    if ($UserValidator->isValidUser()) {
        $userID = $UserValidator->getUserID();
        $query = "SELECT cash FROM NormalUser WHERE id = '$userID'";
        $res = mysqli_query($connect, $query);
        $fetchResult = mysqli_fetch_assoc($res);
        die($fetchResult['cash']);
    } else {
        http_response_code(774);
        die(NULL);
    }
}
?>