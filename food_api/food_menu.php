<?php
define('HOSTNAME', 'localhost');
define('USERNAME', 'lexeense_admin');
define('PASSWORD', 'admin@lexeen123_#');
define('DATABASE', 'lexeense_Main_DB');
require_once('../UserValidator.php');
require_once('../MCrypt.php');

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
mysqli_set_charset($connect, "utf8");
if ($connect) {

    $tokenValue = null;
    $code = null;
    $sharedKey = 10;
    null;
    $headers = getallheaders();

    foreach ($headers as $key => $val) {
        if (strcmp($key, "token") == 0)
            $tokenValue = $val;
        if (strcmp($key, "encsharedkey") == 0)
            $sharedKey = (int)$val;
        if (strcmp($key, "code") == 0)
            $code = $val;
    }

    $UserValidator = new UserValidator($tokenValue);
    if ($UserValidator->isValidUser()) {
        $cipher = new MCrypt($sharedKey);
        $restaurantID = $cipher->getRestaurantID($code);
        //prepare categories
        $query = "SELECT Gallery.one,Gallery.two,Category.id,Category.name,Category.color FROM Category,RelCategoryRestaurant,Gallery WHERE Gallery.id = Category.logos_id AND RelCategoryRestaurant.restaurant_id = '$restaurantID'  AND Category.id = RelCategoryRestaurant.category_id AND RelCategoryRestaurant.is_valid='1'";
        $res = mysqli_query($connect, $query);
        $final_return_arr = array();

        while ($category = mysqli_fetch_assoc($res)) {
            $categoryID = $category['id'];
            $final_category['id'] = $categoryID;
            $final_category['name'] = $cipher->encrypt($category['name']);
            $final_category['color'] = $cipher->encrypt($category['color']);
            $photos = [];
            array_push($photos, $cipher->encrypt($category['one']));
            array_push($photos, $cipher->encrypt($category['two']));
            $final_category['logos'] = $photos;

            //prepare food list
            $food_array = [];
            $query2 = "SELECT Food.id,Food.name,Food.calories,Food.cook_time_minutes,Food.description,Food.price,Gallery.one,Gallery.two FROM Food,RelFoodCategory,Gallery,RelCategoryRestaurant WHERE RelFoodCategory.food_id = Food.id AND  RelFoodCategory.category_id = '$categoryID' AND Gallery.id = Food.pictures_id AND Food.restaurant_id = '$restaurantID'  AND RelCategoryRestaurant.restaurant_id = Food.restaurant_id AND RelCategoryRestaurant.category_id = RelFoodCategory.category_id  AND Food.deleted='0' AND Food.valid_to_cook='1'";
            $res2 = mysqli_query($connect, $query2);
            while ($row = mysqli_fetch_assoc($res2)) {
                $photos = [];
                $row_array['id'] = $row['id'];
                $row_array['name'] = $cipher->encrypt($row['name']);
                $row_array['calories'] = $row['calories'];
                $row_array['cookTimeMinutes'] = $row['cook_time_minutes'];
                $row_array['description'] = $cipher->encrypt($row['description']);
                $row_array['price'] = $row['price'];
                array_push($photos, $cipher->encrypt($row['one']));
                $row_array['pictures'] = $photos;
                array_push($food_array, $row_array);
            }
            $return_arr['category'] = $final_category;
            $return_arr['foodList'] = $food_array;
            array_push($final_return_arr, $return_arr);
        }
        die(json_encode($final_return_arr));
    }
}
