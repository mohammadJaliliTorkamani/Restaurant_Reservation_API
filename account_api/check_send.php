<?php
require_once('../SMSSender.php');
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
        if (strcmp($key, "pusheid") == 0)
            $pusheID = $val;
    }

    $phone = $_POST['phone'];
    $sSQL = 'SET CHARACTER SET utf8';
    mysqli_query($connect, $sSQL);
    $query = "SELECT * FROM NormalUser WHERE phone like '%$phone'";
    $res = mysqli_query($connect, $query);
    if (mysqli_num_rows($res) > 0) {
        $fResult = mysqli_fetch_assoc($res);
        $code = rand(10000, 99999);
        try {
            // your sms.ir panel configuration
            $APIKey = "4c389b80758bfba78bc4ac9d";
            $SecretKey = "Mohammad_Kimia_1376_1377";
            $APIURL = "https://ws.sms.ir/";
            // message data
            $data = array(
                "ParameterArray" => array(
                    array("Parameter" => "VerificationCode", "ParameterValue" => $code)
                ),
                "Mobile" => "0" . substr($phone, 4),
                "TemplateId" => "18002"
            );
            $SmsIR_UltraFastSend = new SmsSender($APIKey, $SecretKey, $APIURL);
            $UltraFastSend = $SmsIR_UltraFastSend->ultraFastSend($data);
        } catch (Exeption $e) {
            $response['code'] = 102;
            $response['message'] = 'خطای فنی !';
        }
        $dateTime = (new gregorian2jalali)->gregorian_to_jalali() . " " . date('H:i:s');
        $insertQuery = "INSERT INTO ActivationCode(phone,pusheID,activationCode,DateTime) VALUES('$phone','$pusheID','$code','$dateTime')";
        mysqli_query($connect, $insertQuery);
        $response['code'] = 101;
    } else {
        $response['code'] = 102;
        $response['message'] = 'کاربر موجود نیست !';
    }
} else {
    $response['code'] = 102;
    $response['message'] = 'خطای فنی !';
}

die(json_encode($response));
?>*/