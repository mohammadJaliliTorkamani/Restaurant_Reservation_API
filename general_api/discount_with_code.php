<?php
require_once('../UserValidator.php');
require_once('../MCrypt.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'lexeense_admin');
define('PASSWORD', 'admin@lexeen123_#');
define('DATABASE', 'lexeense_Main_DB');

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
if ($connect) {
    mysqli_set_charset($connect, "utf8");
    $token = null;
    $code = null;
    $sharedKey = null;
    $headers = getallheaders();
    foreach ($headers as $key => $val) {
        if (strcmp($key, "token") == 0)
            $token = $val;
        else if (strcmp($key, "code") == 0)
            $code = $val;
        else if (strcmp($key, "encsharedkey") == 0)
            $sharedKey = $val;
    }

    $UserValidator = new UserValidator($token);
    if ($UserValidator->isValidUser()) {
        $userID = $UserValidator->getUserID();
        $cipher = new MCrypt($sharedKey);
        $restaurantID = $cipher->getRestaurantID($code);
        $discountCode = $_GET["code"];
        $query = "SELECT id,code,percentage,minimum_acceptable_price FROM Discount WHERE is_valid='1' AND used_times< max_usage AND code = '$discountCode' AND restaurant_id = '$restaurantID'";
        $res = mysqli_query($connect, $query);
        if (mysqli_num_rows($res) != 1) //zero or more than one
            $discount['id'] = -2;
        else {
            $fetchRes = mysqli_fetch_assoc($res);
            $discountID = $fetchRes['id'];
            $discount['id'] = $discountID;
            $discount['code'] = $fetchRes['code'];
            $discount['percentage'] = $fetchRes['percentage'];
            $discount['minimumAcceptablePrice'] = $fetchRes['minimum_acceptable_price'];
            $query = "SELECT RelNormalUserDiscount.id FROM RelNormalUserDiscount WHERE RelNormalUserDiscount.discount_id='$discountID' AND RelNormalUserDiscount.normal_user_id = '$userID'";
            $queryRes = mysqli_query($connect, $query);
            if (mysqli_num_rows($queryRes) > 0)
                $discount['id'] = -1;
        }
        die(json_encode($discount));
    }
}
