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
        $restaurantSelectionQuery = "SELECT Restaurant.name FROM Restaurant WHERE Restaurant.id = '$restaurantID'";
        $restaurantSelectionRes = mysqli_query($connect, $restaurantSelectionQuery);
        $restaurantSelectionFetchRes = mysqli_fetch_assoc($restaurantSelectionRes);
        $restaurant['name'] = $restaurantSelectionFetchRes['name'];
        die(json_encode($restaurant));
    } else {
        die('Unauthorized !');
    }
}
