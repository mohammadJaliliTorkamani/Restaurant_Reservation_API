<?php
require_once('../UserValidator.php');
require_once('../PersianDate.php');
require_once('../MCrypt.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'cpres873_Aban');
define('PASSWORD', 'KimiaAndMohammad');
define('DATABASE', 'cpres873_KNTU_Database');
date_default_timezone_set("Asia/Tehran");

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
        $orderTime = strtotime($_GET['order_time']);
        $openForServeCheckerQuery = "SELECT ready_for_serve as ready FROM Restaurant WHERE id = '$restaurantID'";
        $openForServeCheckerResult = mysqli_query($connect, $openForServeCheckerQuery);
        $readyForServe = mysqli_fetch_assoc($openForServeCheckerResult)['ready'];
        $response['manuallyDisabled']=!($readyForServe == 1);
        die(json_encode($response));
    }
}
?>