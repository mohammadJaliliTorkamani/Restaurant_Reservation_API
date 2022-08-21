<?php
require_once('../UserValidator.php');
require_once('../MCrypt.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'cpres873_Aban');
define('PASSWORD', 'KimiaAndMohammad');
define('DATABASE', 'cpres873_KNTU_Database');
date_default_timezone_set("Asia/Tehran");

function getActiveForDeliveryHours($connect, $restaurantID)
{
    $workingTimeQuery = "SELECT DeliveryTime.start_time,DeliveryTime.end_time FROM RelRestaurantDeliveryTime,DeliveryTime WHERE DeliveryTime.id = RelRestaurantDeliveryTime.delivery_time_id AND RelRestaurantDeliveryTime.restaurant_id = '$restaurantID'";
    $workingTimeRes = mysqli_query($connect, $workingTimeQuery);
    $rowsLastIndex = mysqli_num_rows($workingTimeRes) - 1;
    $counterIndex = 0;
    $workingHourSentenceIntervals = "";
    while ($workingTimeFetchRes = mysqli_fetch_assoc($workingTimeRes)) {
        $counterIndex = $counterIndex + 1;
        $startTime = $workingTimeFetchRes['start_time'];
        $endTime = $workingTimeFetchRes['end_time'];
        $workingHourSentenceIntervals = $workingHourSentenceIntervals . "از " . $startTime . " تا " . $endTime . ($counterIndex < $rowsLastIndex ? " " : " , ");
    }
    $workingHourSentence = $workingHourSentenceIntervals;
    return $workingHourSentence;
}

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
if ($connect) {
    mysqli_set_charset($connect, "utf8");
    $token = null;
    $code = null;
    $sharedKey = null;
    $headers = getallheaders();
    foreach ($headers as $key => $val) {
        if (strcmp($key, "Token") == 0)
            $token = $val;
        else if (strcmp($key, "Code") == 0)
            $code = $val;
        else if (strcmp($key, "Encsharedkey") == 0)
            $sharedKey = $val;
    }

    $UserValidator = new UserValidator($token);
    if ($UserValidator->isValidUser()) {
        $cypher = new MCrypt($sharedKey);
        $restaurantID = $cypher->getRestaurantID($code);
        $orderTime = strtotime(date("H:i"));
        $openForDeliveryCheckerQuery = "SELECT ready_for_delivery as ready FROM Restaurant WHERE id = '$restaurantID'";
        $openForDeliveryCheckerResult = mysqli_query($connect, $openForDeliveryCheckerQuery);
        $readyForDelivery = mysqli_fetch_assoc($openForDeliveryCheckerResult)['ready'];
        if ($readyForDelivery == 1) {
            //check delivery time
            $workingTimeQuery = "SELECT DeliveryTime.start_time,DeliveryTime.end_time FROM RelRestaurantDeliveryTime,DeliveryTime WHERE DeliveryTime.id = RelRestaurantDeliveryTime.delivery_time_id AND RelRestaurantDeliveryTime.restaurant_id = '$restaurantID'";
            $workingTimeRes = mysqli_query($connect, $workingTimeQuery);

            while ($workingTimeFetchRes = mysqli_fetch_assoc($workingTimeRes)) {
                $startTime = strtotime($workingTimeFetchRes['start_time']);
                $endTime = strtotime($workingTimeFetchRes['end_time']);
                if ($orderTime >= $startTime && $orderTime <= $endTime) {
                    $legal = true;
                    break;
                }
            }
            if ($legal) {
                $response['active'] = true;
            } else {
                $response['active'] = false;
                $response['todaysServiceTime'] = $cypher->encrypt(getActiveForDeliveryHours($connect, $restaurantID));
            }
        } else {
            $response['active'] = false;
            $response['manuallyDisabled'] = true;
        }
        die(json_encode($response));
    }
}
?>