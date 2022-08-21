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
        if (strcmp($key, "code") == 0)
            $code = $val;
        if (strcmp($key, "encsharedkey") == 0)
            $sharedKey = $val;
    }
    $UserValidator = new UserValidator($token);
    if ($UserValidator->isValidUser()) {
        $cipher = new MCrypt($sharedKey);
        $restaurantID = $cipher->getRestaurantID($code);
        $foodID = $_GET['food_id'];
        $photos = [];

        $query = "SELECT Food.id as id,Food.name as name,Food.calories as calorie,Food.cook_time_minutes,Food.description,Food.price,Gallery.one as one FROM Food,Gallery WHERE Food.id = '$foodID' AND Food.pictures_id = Gallery.id AND Food.restaurant_id = '$restaurantID' AND Food.deleted='0' AND Food.valid_to_cook='1'";
        $res = mysqli_query($connect, $query);
        $row = mysqli_fetch_assoc($res);
        $row_array['id'] = $row['id'];
        $row_array['name'] = $cipher->encrypt($row['name']);
        $row_array['calories'] = $row['calorie'];
        $row_array['cookTimeMinutes'] = $row['cook_time_minutes'];
        $row_array['description'] = $cipher->encrypt($row['description']);
        $row_array['price'] = $row['price'];
        array_push($photos, $cipher->encrypt($row['one']));
        $row_array['pictures'] = $photos;
        die(json_encode($row_array));
    }
}
