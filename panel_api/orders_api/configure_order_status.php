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
    date_default_timezone_set("Asia/Tehran");
    $status = $_GET['status'];
    $orderID = $_GET['order_id'];
    $dateTime = (new gregorian2jalali)->gregorian_to_jalali() . " " . date('H:i:s');
    $token = $_GET['Token'];
    $userValidator = new UserValidator($token);
    if ($userValidator->isValidUser()) {
        $restaurantID = $userValidator->getRestaurantID();
        if ($status == "done") { //want to make order to done status  
            $checkOrderExistenceQuery = "SELECT LexinOrder.id FROM LexinOrder WHERE LexinOrder.restaurant_id = '$restaurantID'AND LexinOrder.status= 'In Queue' AND LexinOrder.id = '$orderID'";
            $checkOrderExistenceRes = mysqli_query($connect, $checkOrderExistenceQuery);
            if (mysqli_num_rows($checkOrderExistenceRes) > 0) {
                $updateLexinOrderQuery = "UPDATE LexinOrder SET status = 'Done',status_fill_date_time = '$dateTime' WHERE id = '$orderID' ";
                $updateLexinOrderRes = mysqli_query($connect, $updateLexinOrderQuery);
                $updateLexinTableOrderQuery = "UPDATE LexinTableOrder SET completed = '1' WHERE order_id = '$orderID'";
                $updateLexinTableOrderRes = mysqli_query($connect, $updateLexinTableOrderQuery);
                $response['resultCode'] = 200;
                $response['message'] = "موفق";
            } else {
                $response['resultCode'] = 100;
                $response['message'] = "سفارش موجود نیست";
            }
        } else if ($status == "discarded") {//make order to discard status
            $checkOrderExistenceQuery = "SELECT LexinOrder.id FROM LexinOrder WHERE LexinOrder.restaurant_id = '$restaurantID' AND LexinOrder.status= 'In Queue' AND LexinOrder.id = '$orderID'";
            $checkOrderExistenceRes = mysqli_query($connect, $checkOrderExistenceQuery);
            if (mysqli_num_rows($checkOrderExistenceRes) > 0) {
                $updateLexinOrderQuery = "UPDATE LexinOrder SET status = 'Discarded',status_fill_date_time = '$dateTime' WHERE id = '$orderID' ";
                $updateLexinOrderRes = mysqli_query($connect, $updateLexinOrderQuery);
                $updateLexinTableOrderQuery = "UPDATE LexinTableOrder SET completed = '1' WHERE order_id = '$orderID'";
                $updateLexinTableOrderRes = mysqli_query($connect, $updateLexinTableOrderQuery);
                $response['resultCode'] = 200;
                $response['message'] = "موفق";
            } else {
                $response['resultCode'] = 100;
                $response['message'] = "سفارش موجود نیست";
            }
        }
        die(json_encode($response));
    } else {
        die('Unauthorized !');
    }
}
?>