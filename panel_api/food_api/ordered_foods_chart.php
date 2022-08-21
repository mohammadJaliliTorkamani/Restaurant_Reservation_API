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
    $mode = $_GET['mode'];
    $token = $_GET['Token'];
    $userValidator = new UserValidator($token);
    if ($userValidator->isValidUser()) {
        $startDate = date('Y/m/d');
        if ($mode == 1)
            $ans = "1";
        else if ($mode == 2)
            $ans = "7";
        else if ($mode == 3)
            $ans = "31";
        else if ($mode == 4)
            $ans = "365";
        else
            $ans = "2";

        $command = ('-' . $ans . ' days');
        $startDate = date("Y/m/d", strtotime($command));
        $startObj = new gregorian2jalali;
        $startObj->mydate = $startDate;
        $receivedStartTime = $startObj->gregorian_to_jalali() . " 00:00";
        $endObj = new gregorian2jalali;
        $receivedEndTime = $endObj->gregorian_to_jalali() . " " . date("H:i");

        $restaurantID = $userValidator->getRestaurantID();
        $query = "SELECT Food.id as food_id,Food.name,SUM(FoodOrder.count_number) as total_order_count FROM LexinOrder,FoodOrder,Food WHERE LexinOrder.restaurant_id = '$restaurantID' AND LexinOrder.id = FoodOrder.order_id AND FoodOrder.food_id = Food.id AND to_deliver_time between '$receivedStartTime' AND '$receivedEndTime' AND status = 'Done' GROUP BY Food.id ORDER BY SUM(FoodOrder.count_number) DESC LIMIT 0,6";
        //die($query);
        $percentageCalculatorQuery = "SELECT food_ID,name,total_order_count FROM (" . $query . ") as T2 ";
        $res = mysqli_query($connect, $percentageCalculatorQuery);
        $totalSumCounter = 0;
        while ($fetch = mysqli_fetch_assoc($res)) {
            $totalSumCounter = $totalSumCounter + $fetch['total_order_count'];
        }
        $array = [];
        $res = mysqli_query($connect, $percentageCalculatorQuery);
        while ($fetch = mysqli_fetch_assoc($res)) {
            $obj['food_ID'] = $fetch['food_ID'];
            $obj['name'] = $fetch['name'];
            $obj['total_order_count'] = (int)$fetch['total_order_count'];
            $obj['percentage'] = $fetch['total_order_count'] / $totalSumCounter * 100;
            array_push($array, $obj);
        }
        shuffle($array);
        die(json_encode($array));
    } else {
        die('Unauthorized !');
    }
}
