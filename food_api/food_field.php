<?php
require_once('../UserValidator.php');
require_once('../MCrypt.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'lexeense_admin');
define('PASSWORD', 'admin@lexeen123_#');
define('DATABASE', 'lexeense_Main_DB');

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
mysqli_set_charset($connect, "utf8");
if ($connect) {
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
        $cipher = new MCrypt($sharedKey);
        $food_id = $_GET['food_id'];
        $restaurantID = $cipher->getRestaurantID($code);
        $return_arr = [];

        $q1 = "SELECT PopularFood.id FROM PopularFood,Food WHERE Food.id = '$food_id' AND Food.id = PopularFood.food_id AND Food.restaurant_id = '$restaurantID' AND Food.deleted='0' AND Food.valid_to_cook='1'";
        $q2 = "SELECT Off.percentage FROM RelFoodOff,Food,Off WHERE Food.id = '$food_id' AND Food.id = RelFoodOff.food_id AND Food.restaurant_id = '$restaurantID' AND Off.id = RelFoodOff.off_id  AND Food.deleted='0' AND Food.valid_to_cook='1'";
        $res1 = mysqli_query($connect, $q1);
        if (mysqli_num_rows($res1) > 0) {
            array_push($return_arr, $cipher->encrypt("popular"));
            array_push($return_arr, $cipher->encrypt("null"));
        } else {
            $res2 = mysqli_query($connect, $q2);
            if (mysqli_num_rows($res2) > 0) {
                $offer = mysqli_fetch_assoc($res2);
                array_push($return_arr, $cipher->encrypt("offer"));
                array_push($return_arr, $offer['percentage']);
            } else {
                array_push($return_arr, $cipher->encrypt("null"));
                array_push($return_arr, $cipher->encrypt("null"));
            }
        }
        die(json_encode($return_arr));
    }
}
