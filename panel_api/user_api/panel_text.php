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
        $array = [];
        $larmQuery = "SELECT PanelMessage.id,PanelMessage.restaurant_id,PanelMessage.message, PanelMessage.is_valid, PanelMessage.date_time  FROM PanelMessage WHERE PanelMessage.is_valid='1' AND restaurant_id = '$restaurantID'";
        $alarmRes = mysqli_query($connect, $larmQuery);
        if (mysqli_num_rows($alarmRes) > 0) {
            while ($alarmFetchRes = mysqli_fetch_assoc($alarmRes)) {
                $alarm['id'] = $alarmFetchRes['id'];
                $alarm['restaurant_id'] = $alarmFetchRes['restaurant_id'];
                $alarm['message'] = $alarmFetchRes['message'];
                $alarm['date_time'] = $alarmFetchRes['date_time'];
                array_push($array, $alarm);
            }
        }
        die(json_encode($array));
    } else {
        die('Unauthorized !');
    }
}
