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
        $query = "SELECT Category.id,Category.name FROM Category,RelCategoryRestaurant WHERE RelCategoryRestaurant.restaurant_id = '$restaurantID' AND Category.id = RelCategoryRestaurant.category_id AND RelCategoryRestaurant.is_valid= '1'";
        $res = mysqli_query($connect, $query);
        $array = [];
        while ($fetch = mysqli_fetch_assoc($res)) {
            $obj['id'] = (int)$fetch['id'];
            $obj['name'] = $fetch['name'];
            array_push($array, $obj);
        }
        die(json_encode($array));
    } else {
        die('Unauthorized !');
    }
}
?>