<?php
define('HOSTNAME', 'localhost');
define('USERNAME', 'lexeense_admin');
define('PASSWORD', 'admin@lexeen123_#');
define('DATABASE', 'lexeense_Main_DB');

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');

if ($connect) {

    $pusheID = null;
    $headers = getallheaders();
    foreach ($headers as $key => $val) {
        if (strcmp($key, "pusheid") == 0)
            $pusheID = $val;
    }
    $code = $_POST['code'];
    $query = "SELECT activationCode as code FROM ActivationCode WHERE pusheID = '$pusheID' ORDER BY id desc";
    $res = mysqli_query($connect, $query);
    if (mysqli_num_rows($res) > 0) {
        $fResult = mysqli_fetch_assoc($res)['code'];
        if ($fResult == $code) {
            $response['code'] = 101;
            mysqli_query($connect, "DELETE FROM ActivationCode WHERE pusheID = '$pusheID'");
        } else {
            $response['code'] = 102;
            $response['message'] = 'کد وارد شده اشتباه می باشد';
        }
    } else {
        $response['code'] = 102;
        $response['message'] = 'کد فعالسازی وجود ندارد';
    }
    die(json_encode($response));
}
