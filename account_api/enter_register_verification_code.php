<?php
require_once("../sms_service.php");
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
        if (strcmp($key, "pusheid") == 0)
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
            date_default_timezone_set("Asia/Tehran");
    
            // your sms.ir panel configuration
            $APIKey = "30cc1df5415d4e2361c82a02";
            $SecretKey = "KimiaMohammad_L95";
            $APIURL = "https://ws.sms.ir/";
            $templateID = "69085";
            $actCode = rand(10000, 99999);
            // message data
            $data = array(
                "ParameterArray" => array(
                    array(
                        "Parameter" => "VerificationCode",
                        "ParameterValue" => $actCode
                    )
                ),
                "Mobile" => $phone,
                "TemplateId" => $templateID
            );
    
            $SmsIR_UltraFastSend = new SmsIR_UltraFastSend($APIKey, $SecretKey, $APIURL);
            $UltraFastSend = $SmsIR_UltraFastSend->ultraFastSend($data);
            // var_dump($UltraFastSend);
        } catch (Exception $e) {
            // echo 'Error UltraFastSend : '.$e->getMessage();
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
