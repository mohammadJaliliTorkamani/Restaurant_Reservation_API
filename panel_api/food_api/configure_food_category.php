<?php
header('Access-Control-Allow-Origin: *'); //allow everybody
date_default_timezone_set("Asia/Tehran");
require_once('../UserValidator.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'lexeense_admin');
define('PASSWORD', 'admin@lexeen123_#');
define('DATABASE', 'lexeense_adminDatabase');
$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
if ($connect) {
    $foodID = $_GET['food_id'];
    $categoryID = $_GET['category_id'];
    $submitToAdd = $_GET['submit_to_add']; //if be true means add,if false means delete
    mysqli_set_charset($connect, "utf8");
    $token = $_GET['Token'];
    $userValidator = new UserValidator($token);
    if ($userValidator->isValidUser()) {
        $restaurantID = $userValidator->getRestaurantID();
        if ($submitToAdd == "true") { //want to add such relation between food and category
            $selectCategoryFoodQuery = "SELECT id FROM RelFoodCategory WHERE food_id = '$foodID' AND category_id = '$categoryID'";
            $selectCategoryFoodRes = mysqli_query($connect, $selectCategoryFoodQuery);
            if (mysqli_num_rows($selectCategoryFoodRes) == 0) {
                $checkFoodExistenceQuery = "SELECT id FROM Food WHERE Food.id = '$foodID' AND Food.restaurant_id = '$restaurantID'";
                $checkFoodExistenceRes = mysqli_query($connect, $checkFoodExistenceQuery);
                if (mysqli_num_rows($checkFoodExistenceRes) > 0) {
                    $checkCategoryQuery = "SELECT id FROM Category WHERE id = '$categoryID'";
                    $checkCategoryRes = mysqli_query($connect, $checkCategoryQuery);
                    if (mysqli_num_rows($checkCategoryRes) > 0) {
                        mysqli_query($connect, "INSERT INTO RelFoodCategory(food_id,category_id) VALUES('$foodID','$categoryID')");
                        $response['resultCode'] = 200;
                        $response['message'] = "موفق";
                    } else {
                        $response['resultCode'] = 100;
                        $response['message'] = "دسته بندی وارد شده موجود نیست";
                    }
                } else {
                    $response['resultCode'] = 100;
                    $response['message'] = "غذای وارد شده موجود نیست.";
                }
            } else {
                $response['resultCode'] = 100;
                $response['message'] = "چنین رابطه ای قبلا ثبت شده است";
            }
        } else { //we wanna delete a relation
            $selectCategoryFoodQuery = "SELECT id FROM RelFoodCategory WHERE food_id = '$foodID' AND category_id = '$categoryID'";
            $selectCategoryFoodRes = mysqli_query($connect, $selectCategoryFoodQuery);
            if (mysqli_num_rows($selectCategoryFoodRes) > 0) {
                $checkFoodExistenceQuery = "SELECT id FROM Food WHERE Food.id = '$foodID' AND Food.restaurant_id = '$restaurantID'";
                $checkFoodExistenceRes = mysqli_query($connect, $checkFoodExistenceQuery);
                if (mysqli_num_rows($checkFoodExistenceRes) > 0) {
                    $checkCategoryQuery = "SELECT id FROM Category WHERE id = '$categoryID'";
                    $checkCategoryRes = mysqli_query($connect, $checkCategoryQuery);
                    if (mysqli_num_rows($checkCategoryRes) > 0) {
                        mysqli_query($connect, "DELETE FROM RelFoodCategory WHERE food_id = '$foodID' AND category_id = '$categoryID'");
                        $response['resultCode'] = 200;
                        $response['message'] = "موفق";
                    } else {
                        $response['resultCode'] = 100;
                        $response['message'] = "دسته بندی وارد شده موجود نیست";
                    }
                } else {
                    $response['resultCode'] = 100;
                    $response['message'] = "غذای وارد شده موجود نیست.";
                }
            } else {
                $response['resultCode'] = 100;
                $response['message'] = "هیچ رابطه ای بین غذا و دسته بندی وارد شده موجود نیست.";
            }
        }
        die(json_encode($response));
    } else {
        die('Unauthorized !');
    }
}
