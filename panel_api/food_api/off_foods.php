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
        $query = "SELECT  RelFoodOff.food_id as food_id,RelFoodOff.off_id as off_id,Off.percentage,Food.name FROM Off,Food,RelFoodOff WHERE Off.is_valid='1' AND RelFoodOff.off_id = Off.id AND RelFoodOff.food_id = Food.id AND Food.restaurant_id = '$restaurantID' AND Food.deleted='0' AND Food.valid_to_cook='1'";

        $res = mysqli_query($connect, $query);
        $array = [];
        while ($fetch = mysqli_fetch_assoc($res)) {
            $obj['food_id'] = (int)$fetch['food_id'];
            $obj['off_id'] = (int)$fetch['off_id'];
            $obj['name'] = $fetch['name'];
            $obj['percentage'] = (int)$fetch['percentage'];
            array_push($array, $obj);
        }
        die(json_encode($array));
    } else {
        die('Unauthorized !');
    }
}
?>