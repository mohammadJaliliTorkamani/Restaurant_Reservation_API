<?php
require_once('../UserValidator.php');
require_once('../MCrypt.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'lexeense_admin');
define('PASSWORD', 'admin@lexeen123_#');
define('DATABASE', 'lexeense_Main_DB');

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
mysqli_set_charset($connect, "utf8");

if ($connect) {
    $token = null;
    $sharedKey = null;
    $headers = getallheaders();
    foreach ($headers as $key => $val) {
        if (strcmp($key, "Token") == 0)
            $token = $val;
        else if (strcmp($key, "Encsharedkey") == 0)
            $sharedKey = $val;
    }
    $UserValidator = new UserValidator($token);
    if ($UserValidator->isValidUser()) {
        $cipher = new MCrypt($sharedKey);
        $res = [];
        $item['id'] = 1;
        $item['name'] = $cipher->encrypt("A");
        array_push($res, $item);
        $item['id'] = 2;
        $item['name'] = $cipher->encrypt("B");
        array_push($res, $item);
        $item['id'] = 3;
        $item['name'] = $cipher->encrypt("C");
        array_push($res, $item);
        die(json_encode($res));
    }
}
