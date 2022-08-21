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
        if($mode == 1)
            $ans="1";
        else if($mode==2)
            $ans="7";
        else if($mode==3)
            $ans="31";
        else if($mode==4)
            $ans="365";
        else
            $ans="2";
            
        $command=('-' . $ans. ' days');
        $startDate = date("Y/m/d", strtotime($command));
        $startObj = new gregorian2jalali;
        $startObj->mydate = $startDate;
        $receivedStartTime = $startObj->gregorian_to_jalali() . " 00:00";
        $endObj = new gregorian2jalali;
        $receivedEndTime = $endObj->gregorian_to_jalali() ." ". date("H:i");
        
        $restaurantID = $userValidator->getRestaurantID();
        $query = "SELECT LexinTable.id, LexinTable.label as label FROM LexinOrder,LexinTableOrder,LexinTable WHERE LexinOrder.restaurant_id = '$restaurantID' AND LexinOrder.id = LexinTableOrder.order_id AND LexinTableOrder.lexin_table_id = LexinTable.id AND to_deliver_time between '$receivedStartTime' AND '$receivedEndTime' AND status = 'Done' GROUP BY LexinTable.label DESC LIMIT 0,6";
        
        //die($query);
        $percentageCalculatorQuery = "SELECT id,label,COUNT(*) as total_reserve_count  FROM (" . $query . ") as T2 Group By label";
        $res = mysqli_query($connect, $percentageCalculatorQuery);
        $totalSumCounter = 0;
        while ($fetch = mysqli_fetch_assoc($res)) {
            $totalSumCounter = $totalSumCounter + $fetch['total_reserve_count'];
        }
        $array = [];
        $res = mysqli_query($connect, $percentageCalculatorQuery);
        while ($fetch = mysqli_fetch_assoc($res)) {
            $obj['id'] = $fetch['id'];
            $obj['label'] = $fetch['label'];
            $obj['total_reserve_count'] = (int)$fetch['total_reserve_count'];
            $obj['percentage'] = $fetch['total_reserve_count'] / $totalSumCounter * 100;
            array_push($array, $obj);
        }
        shuffle($array);
        die(json_encode($array));
    } else {
        die('Unauthorized !');
    }
}
?>