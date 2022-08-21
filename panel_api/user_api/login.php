<?php
header('Access-Control-Allow-Origin: *'); //allow everybody  

require_once("../../PersianDate.php");
define('HOSTNAME', 'localhost');
define('USERNAME', 'lexeense_admin');
define('PASSWORD', 'admin@lexeen123_#');
define('DATABASE', 'lexeense_Main_DB');

date_default_timezone_set("Asia/Tehran");
$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
if ($connect) {

    $username = $_POST['username'];
    $password = $_POST['password'];
    $uniqueID = $_POST['uniqueid'];

    if ($uniqueID != null) {
        $query = "SELECT password,id FROM StaffUser WHERE username = '$username' AND deleted='0'";
        $res = mysqli_query($connect, $query);
        if (mysqli_num_rows($res) == 1) {
            $fResult = mysqli_fetch_assoc($res);
            if ($fResult['password'] == $password) {
                $userID = $fResult['id'];
                $value = md5(uniqid("*lexinStaff#" . $uniqueID . "!lexinStaff@", true));;
                $date = date('H:i:s');
                $jalaliDateTime = (new gregorian2jalali)->gregorian_to_jalali() . " " . $date;
                //delete all the same tokens from this device
                mysqli_query($connect, "DELETE FROM Token WHERE pushe_ID = '$uniqueID'");
                $sharedKey = rand(10, 100);
                $createTokenQuery = "INSERT INTO Token(user_id,user_role,value,expire_time,last_login_time,pushe_ID,shared_key) VALUES('$userID','staff','$value','2297/10/20','$jalaliDateTime','$uniqueID','$sharedKey')";
                $createTokenRes = mysqli_query($connect, $createTokenQuery);
                $response['code'] = 200;
                $response['token'] = $value;
                $response['shared_key'] = $sharedKey;
            } else {
                $response['code'] = 100;
                $response['message'] = 'رمز خود را اشتباه وارد کرده اید !';
            }
        } else {
            $response['code'] = 100;
            $response['message'] = 'چنین کاربری وجود ندارد !';
        }
        die(json_encode($response));
    }
}
