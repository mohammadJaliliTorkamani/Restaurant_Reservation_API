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
        if (strcmp($key, "Token") == 0)
            $token = $val;
        else if (strcmp($key, "Code") == 0)
            $code = $val;
        else if (strcmp($key, "Encsharedkey") == 0)
            $sharedKey = $val;
    }

    $UserValidator = new UserValidator($token);
    if ($UserValidator->isValidUser()) {
        $cipher = new MCrypt($sharedKey);
        $restaurantID = $cipher->getRestaurantID($code);
        $query = "SELECT Off.id as id,Off.percentage,RelFoodOff.food_id as food_id FROM RelFoodOff,Food,Off WHERE Food.restaurant_id='$restaurantID' AND Off.is_valid='1' AND Off.id=RelFoodOff.off_id AND Food.id=RelFoodOff.food_id AND Food.deleted='0' AND Food.valid_to_cook='1'";
        $res = mysqli_query($connect, $query);
        $return_arr = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $row_array['id'] = $row['id'];
            $row_array['foodID'] = $row['food_id'];
            $row_array['discountPercentage'] = $row['percentage'];
            array_push($return_arr, $row_array);
        }
        die(json_encode($return_arr));
    }
}
