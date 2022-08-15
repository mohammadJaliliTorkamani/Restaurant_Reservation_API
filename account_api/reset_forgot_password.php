<?php
define('HOSTNAME', 'localhost');
define('USERNAME', 'cpres873_Aban');
define('PASSWORD', 'KimiaAndMohammad');
define('DATABASE', 'cpres873_KNTU_Database');
$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');

if ($connect) {
    mysqli_set_charset($connect, "utf8");
    $password = $_POST['password'];
    $phone = "0098" . $_POST['phone'];
    $query = "UPDATE NormalUser SET password = '$password' WHERE phone = '$phone'";
    mysqli_query($connect, $query);
    $response['code'] = 101;
    die(json_encode($response));
}
?>