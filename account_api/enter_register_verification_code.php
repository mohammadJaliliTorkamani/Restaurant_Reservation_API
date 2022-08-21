<?php
require_once("../SMSSender.php");
require_once("../PersianDate.php");
define('HOSTNAME', 'localhost');
define('USERNAME', 'lexeense_admin');
define('PASSWORD', 'admin@lexeen123_#');
define('DATABASE', 'lexeense_Main_DB');

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
date_default_timezone_set("Asia/Tehran");

if ($connect) {

    $pusheID = null;
    $headers = getallheaders();
    foreach ($headers as $key => $val) {
        if (strcmp($key, "Pusheid") == 0)
            $pusheID = $val;
    }
    $sSQL = 'SET CHARACTER SET utf8';
    mysqli_query($connect, $sSQL);
    $phone = $_POST['phone'];
    //check for duplicated user
    $query = "SELECT id FROM NormalUser WHERE phone = '$phone' ";
    $res = mysqli_query($connect, $query);
    if (mysqli_num_rows($res) > 0) {
        $response['code'] = 102;
        $response['message'] = 'کاربر در لکسین دارای حساب کاربری است!';
    } else {
        try {
            $actCode = rand(10000, 99999);
            // your sms.ir panel configuration
            $APIKey = "4c389b80758bfba78bc4ac9d";
            $SecretKey = "Mohammad_Kimia_1376_1377";
            $APIURL = "https://ws.sms.ir/";
            // message data
            $data = array(
                "ParameterArray" => array(
                    array("Parameter" => "VerificationCode", "ParameterValue" => $actCode)
                ),
                "Mobile" => "0" . substr($phone, 4),
                "TemplateId" => "18002"
            );
            $SmsIR_UltraFastSend = new SmsSender($APIKey, $SecretKey, $APIURL);
            $UltraFastSend = $SmsIR_UltraFastSend->ultraFastSend($data);
        } catch (Exeption $e) {
            echo 'Error UltraFastSend : ' . $e->getMessage();
        }
        mysqli_query($connect, "DELETE FROM ActivationCode WHERE pusheID = '$pusheID'");
        $dateTime = (new gregorian2jalali)->gregorian_to_jalali() . " " . date('H:i:s');
        $insertQuery = "INSERT INTO ActivationCode(phone,pusheID,activationCode,DateTime) VALUES('$phone','$pusheID','$actCode','$dateTime')";
        mysqli_query($connect, $insertQuery);
        $response['code'] = 101;
    }
} else {
    $response['code'] = 102;
    $response['message'] = 'خطای فنی !';
}

die(json_encode($response));
