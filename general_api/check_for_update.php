<?php
require_once('../UserValidator.php');
require_once('../MCrypt.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'cpres873_Aban');
define('PASSWORD', 'KimiaAndMohammad');
define('DATABASE', 'cpres873_KNTU_Database');

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
mysqli_set_charset($connect, "utf8");

if ($connect) {
    $token = null;
    $sharedKey = null;
    $versionCode = -1;
    $headers = getallheaders();
    foreach ($headers as $key => $val) {
        if (strcmp($key, "Token") == 0)
            $token = $val;
        else if (strcmp($key, "Encsharedkey") == 0)
            $sharedKey = $val;
        else if (strcmp($key, "Versioncode") == 0)
            $versionCode = $val;
    }

    $UserValidator = new UserValidator($token);
    if ($UserValidator->isValidUser()) {
        $MCrypt = new MCrypt($sharedKey);
        $query = "SELECT MAX(version_code) AS max FROM LogCat";
        $qRes = mysqli_query($connect, $query);
        $res = mysqli_fetch_assoc($qRes);
        $maxVersion = $res['max'];

        if ($maxVersion > $versionCode) {
            $response['code'] = 102;
            $query = "SELECT feature FROM LogCat WHERE version_code = '$maxVersion'";
            $res = mysqli_query($connect, $query);
            $list = [];
            while ($fetchRes = mysqli_fetch_assoc($res)) {
                $var['versionCode'] = $maxVersion;
                $var['text'] = $fetchRes['feature'];
                array_push($list, $var);
            }
            $response['message'] = $MCrypt->encrypt(json_encode($list));
        } else {
            $response['code'] = 101;
            $colorQuery = "SELECT main_color FROM App";
            $colorRes = mysqli_query($connect, $colorQuery);
            $color = mysqli_fetch_assoc($colorRes)['main_color'];
            $response['message'] = $MCrypt->encrypt($color);
        }
        die(json_encode($response));
    }
}
?>