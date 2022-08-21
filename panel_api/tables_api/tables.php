<?php
header('Access-Control-Allow-Origin: *'); //allow everybody
date_default_timezone_set("Asia/Tehran");
require_once('../UserValidator.php');
require_once('../../PersianDate.php');

define('HOSTNAME', 'localhost');
define('USERNAME', 'lexeense_admin');
define('PASSWORD', 'admin@lexeen123_#');
define('DATABASE', 'lexeense_adminDatabase');

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
if ($connect) {
    mysqli_set_charset($connect, "utf8");
    $token = $_GET['Token'];
    $userValidator = new UserValidator($token);
    if ($userValidator->isValidUser()) {
        $dateTime = (new gregorian2jalali)->gregorian_to_jalali() . " " . date("H:i:s");
        $restaurantID = $userValidator->getRestaurantID();
        $response = [];
        $lexinTableExtractorQuery = "SELECT is_valid as availability,label,LexinTable.id FROM LexinTable WHERE LexinTable.restaurant_id = '$restaurantID'";

        $lexinTableExtractorRes = mysqli_query($connect, $lexinTableExtractorQuery);
        while ($lexinTableExtractorFetch = mysqli_fetch_assoc($lexinTableExtractorRes)) {
            $obj['id'] = (int)$lexinTableExtractorFetch['id'];
            $id = $obj['id'];
            $obj['name'] = $lexinTableExtractorFetch['label'];
            $obj['availability'] = $lexinTableExtractorFetch['availability'] == 1;
            $subQuery = "SELECT start_time,end_time FROM LexinTableOrder WHERE lexin_table_id = '$id' AND '$dateTime'>=start_time AND '$dateTime'<=end_time AND completed = '0'";
            $subRes = mysqli_query($connect, $subQuery);
            $obj['reserved'] = mysqli_num_rows($subRes) > 0;
            array_push($response, $obj);
        }
        die(json_encode($response));
    } else {
        die('Unauthorized !');
    }
}
