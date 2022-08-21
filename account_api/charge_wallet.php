<?php
require_once('../UserValidator.php');
require_once('../MCrypt.php');
require_once('../PersianDate.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'cpres873_Aban');
define('PASSWORD', 'KimiaAndMohammad');
define('DATABASE', 'cpres873_KNTU_Database');

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
mysqli_set_charset($connect, "utf8");
date_default_timezone_set("Asia/Tehran");
if ($connect) {
    $token = null;
    $sharedKey = null;
    $headers = getallheaders();
    foreach ($headers as $key => $val) {
        if (strcmp($key, "Token") == 0) $token = $val;
        else if (strcmp($key, "Endsharedkey") == 0) $sharedKey = $val;
    }

    $UserValidator = new UserValidator($token);
    if ($UserValidator->isValidUser()) {
        $cypher = new MCrypt($sharedKey);
        $user_id = $UserValidator->getUserID();
        $amount = $_POST["amount"];
        $refID = $cypher->decrypt($_GET["ref_id"]);
        $updateCashQueryResult = mysqli_query($connect, "UPDATE NormalUser SET cash = cash+'$amount' WHERE id = '$user_id'");
        $newCashResult = mysqli_query($connect, "SELECT cash FROM NormalUser WHERE id = '$user_id'");
        $newCash = mysqli_fetch_assoc($newCashResult) ["cash"];
        $persianDate = new gregorian2jalali();
        $dateTime = $persianDate->gregorian_to_jalali() . " " . date("H:i:s");
        $query = "INSERT INTO CashCharge(user_id,user_role,amount,new_inventory,bank_issue_tracking_no,creation_time) VALUES ('$user_id','normal','$amount','$newCash','$refID','$dateTime')";
        mysqli_query($connect, $query);
        $response['code'] = 101;
        $response['message'] = $_POST["ref_id"];
        die(json_encode($response));
    }
}
?> 