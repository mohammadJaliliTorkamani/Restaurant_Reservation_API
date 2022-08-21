<?php
header('Access-Control-Allow-Origin: *'); //allow everybody
date_default_timezone_set("Asia/Tehran");
require_once('../UserValidator.php');
require_once('../../PersianDate.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'cpres873_Aban');
define('PASSWORD', 'KimiaAndMohammad');
define('DATABASE', 'cpres873_AbanDatabase');
$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');

if ($connect) {
    mysqli_set_charset($connect, "utf8");
    $lexinTableID = $_GET['lexin_table_id'];
    $dateTime = (new gregorian2jalali)->gregorian_to_jalali() . " " . date('H:i:s');
    $makeItAccessbile = $_GET['submit_to_enable'];//if be true means make it accessible,if false means not_accessbile

    $token = $_GET['Token'];
    $userValidator = new UserValidator($token);
    if ($userValidator->isValidUser()) {
        $restaurantID = $userValidator->getRestaurantID();
        if ($makeItAccessbile == "true") { //want to enable such food
            $searchLexinTableQuery = "SELECT LexinTable.id FROM LexinTable WHERE LexinTable.restaurant_id = '$restaurantID' AND is_valid = '0' AND LexinTable.id = '$lexinTableID'";
            $searchLexinTableRes = mysqli_query($connect, $searchLexinTableQuery);
            if (mysqli_num_rows($searchLexinTableRes) > 0) {
                $fetchRes = mysqli_fetch_assoc($searchLexinTableRes);
                $lexinTableID = $fetchRes['id'];
                mysqli_query($connect, "UPDATE LexinTable SET is_valid='1' WHERE id='$lexinTableID'");
                $response['resultCode'] = 200;
                $response['message'] = "موفق";
            } else {
                $response['resultCode'] = 100;
                $response['message'] = "چنین جایگاهی وجود ندارد";
            }
        } else {//we wanna disable such food
            $searchLexinTableQuery = "SELECT LexinTable.id FROM LexinTable WHERE LexinTable.restaurant_id = '$restaurantID' AND is_valid = '1' AND LexinTable.id = '$lexinTableID'";
            $searchLexinTableRes = mysqli_query($connect, $searchLexinTableQuery);
            if (mysqli_num_rows($searchLexinTableRes) > 0) {
                $fetchRes = mysqli_fetch_assoc($searchLexinTableRes);
                $lexinTableID = $fetchRes['id'];
                mysqli_query($connect, "UPDATE LexinTable SET is_valid='0' WHERE id='$lexinTableID'");
                $response['resultCode'] = 200;
                $response['message'] = "موفق";
            } else {
                $response['resultCode'] = 100;
                $response['message'] = "چنین جایگاهی وجود ندارد";
            }
        }
        die(json_encode($response));
    } else {
        die('Unauthorized !');
    }
}
?>