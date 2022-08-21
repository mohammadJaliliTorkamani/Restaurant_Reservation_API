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
    $foodID = $_GET['food_id'];
    $dateTime = (new gregorian2jalali)->gregorian_to_jalali() . " " . date('H:i:s');
    $makeItAccessbile = $_GET['submit_to_enable']; //if be true means make it accessible,if false means not_accessbile
    $token = $_GET['Token'];
    $userValidator = new UserValidator($token);
    if ($userValidator->isValidUser()) {
        $restaurantID = $userValidator->getRestaurantID();
        if ($makeItAccessbile == "true") { //want to enable such food 
            $searchSuchFoodQuery = "SELECT Food.id as id FROM Food WHERE Food.restaurant_id = '$restaurantID' AND Food.id = '$foodID' AND Food.deleted='0' AND Food.valid_to_cook='0'";
            $searchSuchFoodRes = mysqli_query($connect, $searchSuchFoodQuery);
            if (mysqli_num_rows($searchSuchFoodRes) > 0) {
                $fetchRes = mysqli_fetch_assoc($searchSuchFoodRes);
                $foodID = $fetchRes['id'];
                mysqli_query($connect, "UPDATE Food SET Food.valid_to_cook='1',Food.accessbility_modification_time='$dateTime' WHERE Food.id= '$foodID'");
                $response['resultCode'] = 200;
                $response['message'] = "موفق";
            } else {
                $response['resultCode'] = 100;
                $response['message'] = "غذای وارد شده موجود نیست یا معتبر است";
            }
        } else { //we wanna disable such food
            $searchSuchFoodQuery = "SELECT Food.id FROM Food WHERE Food.restaurant_id = '$restaurantID' AND  Food.id = '$foodID' AND Food.deleted='0' AND Food.valid_to_cook='1'";
            $searchSuchFoodRes = mysqli_query($connect, $searchSuchFoodQuery);
            if (mysqli_num_rows($searchSuchFoodRes) > 0) {
                $fetchRes = mysqli_fetch_assoc($searchSuchFoodRes);
                $foodID = $fetchRes['id'];
                mysqli_query($connect, "UPDATE Food SET valid_to_cook='0',accessbility_modification_time='$dateTime' WHERE id= '$foodID'");
                $response['resultCode'] = 200;
                $response['message'] = "موفق";
            } else {
                $response['resultCode'] = 100;
                $response['message'] = "غذای وارد شده موجود نیست یا غیرفعال است";
            }
        }

        die(json_encode($response));
    } else {
        die('Unauthorized !');
    }

    $headers = getallheaders();
    foreach ($headers as $key => $val) {
        if (strcmp($key, "token") == 0)
            $token = $val;
    }
}
