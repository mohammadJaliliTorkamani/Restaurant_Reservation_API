<?php
require_once('../PersianDate.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'cpres873_Aban');
define('PASSWORD', 'KimiaAndMohammad');
define('DATABASE', 'cpres873_KNTU_Database');
date_default_timezone_set("Asia/Tehran");

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');

if ($connect) {
    $username = $_GET['username'];
    $password = $_GET['password'];
    $headers = getallheaders();
    $pusheID = null;
    foreach ($headers as $key => $val)
        if (strcmp($key, "Pusheid") == 0)
            $pusheID = $val;

    if ($pusheID != null) {
        $query = "SELECT password,id FROM NormalUser WHERE phone = '$username' AND deleted='0'";
        $res = mysqli_query($connect, $query);
        if (mysqli_num_rows($res) == 1) {
            $fResult = mysqli_fetch_assoc($res);
            if ($fResult['password'] == $password) {
                $userID = $fResult['id'];
                $value = md5(uniqid("*lexin#" . $pusheID . "!lexin@", true));;
                $date = date('H:i:s');
                $jalaliDateTime = (new gregorian2jalali)->gregorian_to_jalali() . " " . $date;
                //delete all the same tokens from this device
                mysqli_query($connect, "DELETE FROM Token WHERE pushe_ID = '$pusheID'");
                $createTokenQuery = "INSERT INTO Token(user_id,user_role,value,expire_time,last_login_time,pushe_ID) VALUES('$userID','normal','$value','2297/10/20','$jalaliDateTime','$pusheID')";
                $createTokenRes = mysqli_query($connect, $createTokenQuery);
                $response['resultCode'] = 200;
                $response['token'] = $value;
            } else {
                $response['resultCode'] = 100;
                $response['message'] = 'رمز خود را اشتباه وارد کرده اید !';
            }

        } else {
            $response['resultCode'] = 100;
            $response['message'] = 'چنین کاربری وجود ندارد !';
        }
    }
    die(json_encode($response));
}
?>