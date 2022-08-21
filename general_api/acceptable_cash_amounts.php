<?php
require_once('../UserValidator.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'lexeense_admin');
define('PASSWORD', 'admin@lexeen123_#');
define('DATABASE', 'lexeense_Main_DB');

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
        $return_arr = [];
        array_push($return_arr, 10000);
        array_push($return_arr, 50000);
        array_push($return_arr, 100000);
        array_push($return_arr, 200000);
        array_push($return_arr, 500000);
        array_push($return_arr, 1000000);
        die(json_encode($return_arr));
    }
}
