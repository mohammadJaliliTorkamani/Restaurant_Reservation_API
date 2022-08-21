<?php
require_once('../PersianDate.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'lexeense_admin');
define('PASSWORD', 'admin@lexeen123_#');
define('DATABASE', 'lexeense_Main_DB');

date_default_timezone_set("Asia/Tehran");
$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');

if ($connect) {
    $pusheID = null;
    $headers = getallheaders();
    foreach ($headers as $key => $val) {
        if (strcmp($key, "Pusheid") == 0)
            $pusheID = $val;
    }

    mysqli_set_charset($connect, "utf8");
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $phoneNumber = $_POST['phone_number'];
    $male = $_POST['male'];
    $code = $_POST['code'];
    $gender = 1;
    if ($male == "true")
        $gender = 0;
    $password = $_POST['password'];

    $registerTime = (new gregorian2jalali)->gregorian_to_jalali() . " " . date('H:i:s');
    $equality = false;
    $checkCodeQuery = "SELECT activationCode as code FROM ActivationCode WHERE pusheID = '$pusheID'";
    $checkCodeQueryRes = mysqli_query($connect, $checkCodeQuery);
    if (mysqli_num_rows($checkCodeQueryRes) > 0) {
        $fResult = mysqli_fetch_assoc($checkCodeQueryRes);
        $equality = false;
        if ($code == $fResult['code']);
        $equality = true;
    } else {
        $response['resultCode'] = 1;
        $response['message'] = 'خطای فنی در سیستم';
        die(json_encode($response));
    }


    if ($equality) {
        $res = mysqli_query($connect, "SELECT normal_user_id FROM RelNormalUserPusheID WHERE pusheID = '$pusheID'");
        $normalUserFinderFetchRes = mysqli_fetch_assoc($res);
        $normalUserID = $normalUserFinderFetchRes['normal_user_id'];
        $query = "INSERT INTO NormalUser(name,last_name,phone,password,gender,register_time,pusheID) VALUES('$firstName','$lastName','$phoneNumber','$password','$gender','$registerTime','$pusheID')";
        mysqli_query($connect, $query);
        $deleteQuery = "DELETE FROM ActivationCode WHERE pusheID = '$pusheID' OR phone = '$phoneNumber'";
        mysqli_query($connect, $deleteQuery);
        $response['resultCode'] = 0;
    } else {
        $response['resultCode'] = 1;
        $response['message'] = 'کد وارد شده اشتباه می باشد';
    }
    die(json_encode($response));
}
