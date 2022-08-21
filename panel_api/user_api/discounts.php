<?php
header('Access-Control-Allow-Origin: *'); //allow everybody
date_default_timezone_set("Asia/Tehran");
require_once('../UserValidator.php');
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
        $restaurantID = $userValidator->getRestaurantID();
        $query = "SELECT Discount.id,Discount.code,Discount.percentage,Discount.used_times,Discount.minimum_acceptable_price,Discount.max_usage  FROM Discount WHERE Discount.restaurant_id = '$restaurantID' AND is_valid='1'";
        $res = mysqli_query($connect, $query);
        $array = [];
        while ($fetch = mysqli_fetch_assoc($res)) {
            $obj['id'] = (int)$fetch['id'];
            $obj['code'] = $fetch['code'];
            $obj['percentage'] = (int)$fetch['percentage'];
            $obj['used_counter'] = (int)$fetch['used_times'];
            $obj['capacity'] = (int)$fetch['max_usage'];
            $obj['min_acceptable'] = (float)$fetch['minimum_acceptable_price'];
            array_push($array, $obj);
        }
        die(json_encode($array));
    } else {
        die('Unauthorized !');
    }
}
