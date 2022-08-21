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
    $code = $_GET['code'];
    $submitToAdd = $_GET['submit_to_add']; //if be true means add,if false means delete
    $percentage = $_GET['percentage'];
    $maxUsage = $_GET['max_usage'];
    $minAcceptable = $_GET['min_acceptable'];
    $dateTime = (new gregorian2jalali)->gregorian_to_jalali() . " " . date('H:i:s');

    $token = $_GET['Token'];
    $userValidator = new UserValidator($token);
    if ($userValidator->isValidUser()) {
        $restaurantID = $userValidator->getRestaurantID();
        if ($submitToAdd == "true") { //want to add such discount code
            $selectDiscountQuery = "SELECT * FROM Discount WHERE code = '$code' AND restaurant_id = '$restaurantID' AND is_valid='1'";
            $selectDiscounRes = mysqli_query($connect, $selectDiscountQuery);
            if (mysqli_num_rows($selectDiscounRes) == 0) {
                mysqli_query($connect, "INSERT INTO Discount(code,percentage,max_usage,minimum_acceptable_price,restaurant_id,is_valid,creation_time) VALUES('$code','$percentage','$maxUsage','$minAcceptable','$restaurantID','1','$dateTime')");
                $response['resultCode'] = 200;
                $response['message'] = "موفق";
            } else {
                $response['resultCode'] = 100;
                $response['message'] = "کد تخفیف وارد شده موجود است";
            }
        } else { //we wanna delete such discount code
            $selectDiscountQuery = "SELECT * FROM Discount WHERE code = '$code' AND restaurant_id = '$restaurantID' AND is_valid='1'";
            $selectDiscounRes = mysqli_query($connect, $selectDiscountQuery);
            if (mysqli_num_rows($selectDiscounRes) > 0) {
                mysqli_query($connect, "UPDATE Discount SET is_valid='0',delete_time='$dateTime' WHERE code='$code' AND restaurant_id = '$restaurantID'");
                $response['resultCode'] = 200;
                $response['message'] = "موفق";
            } else {
                $response['resultCode'] = 100;
                $response['message'] = "کد تخفیف وارد شده موجود نیست";
            }
        }
        die(json_encode($response));
    } else {
        die('Unauthorized !');
    }
}
