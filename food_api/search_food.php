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
        if (strcmp($key, "Code") == 0)
            $code = $val;
        if (strcmp($key, "Encsharedkey") == 0)
            $sharedKey = $val;
    }

    $UserValidator = new UserValidator($token);
    if ($UserValidator->isValidUser()) {
        $MCrypt = new MCrypt($sharedKey);
        $restaurantID = $MCrypt->getRestaurantID($code);
        $foodName = $_GET['food_name'];
        $query = "SELECT Category.id as cat_id,Food.id as id,Food.name as name,Food.calories,Food.price,Food.cook_time_minutes FROM Food,Category,RelFoodCategory WHERE Food.name LIKE '%" . $foodName . "%' AND Food.restaurant_id = '$restaurantID' AND RelFoodCategory.category_id=Category.id AND Food.id=RelFoodCategory.food_id AND Food.deleted='0' AND Food.valid_to_cook = '1'";
        $return_arr = [];
        $res = mysqli_query($connect, $query);
        while ($row = mysqli_fetch_assoc($res)) {
            $catID = $row['cat_id'];
            $row_array['id'] = $row['id'];
            $row_array['name'] = $MCrypt->encrypt($row['name']);
            $row_array['calories'] = $row['calories'];
            $row_array['cookTimeMinutes'] = $row['cook_time_minutes'];
            $row_array['price'] = $row['price'];

            $photos = array();
            $logoQuery = "SELECT Gallery.one,Gallery.two FROM Gallery,Category WHERE Category.id='$catID' AND Gallery.id=Category.logos_id";
            $logoRes = mysqli_query($connect, $logoQuery);
            while ($logoRow = mysqli_fetch_assoc($logoRes)) {
                array_push($photos, $MCrypt->encrypt($logoRow['logo_one'])); //white
                array_push($photos, $MCrypt->encrypt($logoRow['logo_two'])); //black
            }
            $row_array['pictures'] = $photos;
            array_push($return_arr, $row_array);
        }
        die(json_encode($return_arr));
    }
}
