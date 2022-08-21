<?php
header('Access-Control-Allow-Origin: *'); //allow everybody
date_default_timezone_set("Asia/Tehran");
require_once('../UserValidator.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'lexeense_admin');
define('PASSWORD', 'admin@lexeen123_#');
$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect2');

if ($connect) {
    mysqli_set_charset($connect, "utf8");
    $token = $_GET['Token'];
    $userValidator = new UserValidator($token);
    if ($userValidator->isValidUser()) {
        $restaurantID = $userValidator->getRestaurantID();
        $query = "SELECT Food.id,Food.name,Food.calories,Food.price,Food.cook_time_minutes,Food.description,Food.valid_to_cook FROM Food WHERE Food.restaurant_id = '$restaurantID' AND Food.deleted='0'";
        $res = mysqli_query($connect, $query);
        $array = [];
        while ($fetch = mysqli_fetch_assoc($res)) {
            $obj['id'] = (int)$fetch['id'];
            $obj['name'] = $fetch['name'];
            $obj['cook_time'] = (int)$fetch['cook_time_minutes'];
            $obj['price'] = (float)$fetch['price'];
            $obj['description'] = $fetch['description'];
            $obj['availability'] = $fetch['valid_to_cook'] == 1;
            $obj['calories'] = $fetch['calories'];
            array_push($array, $obj);
        }
        die(json_encode($array));
    } else {
        die('Unauthorized !');
    }
}
