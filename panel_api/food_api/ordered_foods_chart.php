<?php
header('Access-Control-Allow-Origin: *'); //allow everybody
date_default_timezone_set("Asia/Tehran");
require_once('../UserValidator.php');
require_once('../../PersianDate.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'cpres873_Aban');
define('PASSWORD', 'KimiaAndMohammad');
define('DATABASE', 'cpres873_AbanDatabase');
$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
if ($connect) {
    mysqli_set_charset($connect, "utf8");
    $mode = $_GET['mode'];
    $token = $_GET['Token'];
    $userValidator = new UserValidator($token);
    if ($userValidator->isValidUser()) {
        $startDate = date('Y/m/d');
        $endDate = date("Y/m/d", strtotime('-' + ($mode == 1 ? 1 : $mode == 2 ? 7 : $mode == 3 ? 31 : $mode == 4 ? 365 : 1) + 'days'));
        $startObj = new gregorian2jalali;
        $startObj->mydate = $startDate;
        $receivedStartTime = $startObj->gregorian_to_jalali() . " 00:00";
        $startObj->mydate = $endDate;
        $receivedEndTime = $startObj->gregorian_to_jalali() . " 23:59";
        $restaurantID = $userValidator->getRestaurantID();
        $query = "SELECT Food.id as food_id,Food.name,SUM(FoodOrder.count_number) as total_order_count FROM LexinOrder,FoodOrder,Food WHERE LexinOrder.restaurant_id = '$restaurantID' AND LexinOrder.id = FoodOrder.order_id AND FoodOrder.food_id = Food.id GROUP BY Food.id ORDER BY SUM(FoodOrder.count_number) DESC LIMIT 0,6";
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
?>