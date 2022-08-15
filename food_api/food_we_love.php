<?php
require_once('../UserValidator.php');
require_once('../MCrypt.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'cpres873_Aban');
define('PASSWORD', 'KimiaAndMohammad');
define('DATABASE', 'cpres873_KNTU_Database');

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
mysqli_set_charset($connect, "utf8");
if ($connect) {
    $token = null;
    $qrCode = null;
    $sharedKey = null;
    $headers = getallheaders();
    foreach ($headers as $key => $val) {
        if (strcmp($key, "Token") == 0)
            $token = $val;
        else if (strcmp($key, "Code") == 0)
            $qrCode = $val;
        else if (strcmp($key, "Encsharedkey") == 0)
            $sharedKey = $val;
    }
    $UserValidator = new UserValidator($token);
    if ($UserValidator->isValidUser()) {
        $cipher = new MCrypt($sharedKey);
        $restaurantID = $cipher->getRestaurantID($qrCode);
        $query = "SELECT PopularFood.id as id,PopularFood.food_id,PopularFood.priority FROM PopularFood,Food WHERE PopularFood.food_id = Food.id  AND Food.restaurant_id = '$restaurantID' AND Food.deleted='0' AND Food.valid_to_cook='1'";

        $res = mysqli_query($connect, $query);
        $return_arr = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $row_array['id'] = $row['id'];
            $row_array['foodID'] = $row['food_id'];
            $row_array['priority'] = $row['priority'];
            array_push($return_arr, $row_array);
        }
        die(json_encode($return_arr));
    }
}
?>