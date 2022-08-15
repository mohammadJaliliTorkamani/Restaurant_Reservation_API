<?php
header('Access-Control-Allow-Origin: *'); //allow everybody
date_default_timezone_set("Asia/Tehran");
require_once('../UserValidator.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'cpres873_Aban');
define('PASSWORD', 'KimiaAndMohammad');
define('DATABASE', 'cpres873_AbanDatabase');
$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
if ($connect) {
    mysqli_set_charset($connect, "utf8");
    $token = $_GET['Token'];
    $userValidator = new UserValidator($token);
    if ($userValidator->isValidUser()) {
        $restaurantID = $userValidator->getRestaurantID();
        $query = "SELECT Food.id as id,Food.food_to_eat,Food.name,Food.calories, Food.price,Food.cook_time_minutes,Food.description,Food.valid_to_cook as availability FROM Food WHERE Food.restaurant_id = '$restaurantID' AND Food.deleted='0' AND Food.valid_to_cook='1'";
        $res = mysqli_query($connect, $query);
        $array = [];
        while ($fetch = mysqli_fetch_assoc($res)) {
            $obj['id'] = (int)$fetch['id'];
            $obj['name'] = $fetch['name'];
            $obj['food_to_eat'] = $fetch['food_to_eat'];
            $obj['cook_time'] = (int)$fetch['cook_time_minutes'];
            $obj['price'] = (float)$fetch['price'];
            $obj['description'] = $fetch['description'];
            $obj['availability'] = $fetch['availability'] == 1;
            $obj['calories'] = $fetch['calories'];
            array_push($array, $obj);
        }
        die(json_encode($array));
    } else {
        die('Unauthorized !');
    }
}
?>