<?php
require_once("../UserValidator.php");
define('HOSTNAME', 'localhost');
define('USERNAME', 'lexeense_admin');
define('PASSWORD', 'admin@lexeen123_#');
define('DATABASE', 'lexeense_Main_DB');

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
if ($connect) {
    $token = null;
    $x = $_GET["client_private_key"];
    $headers = getallheaders();
    foreach ($headers as $key => $val) {
        if (strcmp($key, "token") == 0) {
            $token = $val;
        }
    }

    $UserValidator = new UserValidator($token);

    if ($UserValidator->isValidUser()) {
        $G = 6;
        $Prime = 13;
        $server_private_key = rand(2, 15);
        $y = pow($G, $server_private_key) % $Prime;
        $shared_key = pow($x, $server_private_key) % $Prime;
        mysqli_query($connect, "UPDATE Token SET shared_key = '$shared_key' WHERE value = '$token' AND deleted = '0'");
        echo ($y); //becasue of 'long' type, we need to echo without EOF
        die();
    }
}
