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
    $offID = $_GET['off_id'];
    $submitToAdd = $_GET['submit_to_add']; //if be true means add,if false means delete

    $token = $_GET['Token'];
    $userValidator = new UserValidator($token);
    if ($userValidator->isValidUser()) {
        $restaurantID = $userValidator->getRestaurantID();
        if ($submitToAdd == "true") { //want to add such off relation between food and off
            $selectOffFoodQuery = "SELECT * FROM RelFoodOff WHERE food_id = '$foodID' AND off_id = '$offID'";

            $selectOffFoodRes = mysqli_query($connect, $selectOffFoodQuery);
            if (mysqli_num_rows($selectOffFoodRes) == 0) {
                $checkFoodExistenceQuery = "SELECT * FROM Food WHERE Food.id = '$foodID' AND Food.restaurant_id = '$restaurantID'";
                $checkFoodExistenceRes = mysqli_query($connect, $checkFoodExistenceQuery);
                if (mysqli_num_rows($checkFoodExistenceRes) > 0) {
                    $checkOffQuery = "SELECT * FROM Off WHERE id = '$offID'";
                    $checkOffRes = mysqli_query($connect, $checkOffQuery);
                    if (mysqli_num_rows($checkOffRes) > 0) {
                        mysqli_query($connect, "INSERT INTO RelFoodOff(food_id,off_id) VALUES('$foodID','$offID')");
                        $response['resultCode'] = 200;
                        $response['message'] = "موفق";
                    } else {
                        $response['resultCode'] = 100;
                        $response['message'] = "تخفیف وارد شده موجود نیست";
                    }
                } else {
                    $response['resultCode'] = 100;
                    $response['message'] = "غذای وارد شده موجود نیست.";
                }
            } else {
                $response['resultCode'] = 100;
                $response['message'] = "این تخفیف برای این غذا از قبل ثبت شده است.";
            }
        } else { //we wanna delete a relation
            $selectOffFoodQuery = "SELECT * FROM RelFoodOff WHERE food_id = '$foodID' AND off_id = '$offID'";
            $selectOffFoodRes = mysqli_query($connect, $selectOffFoodQuery);
            if (mysqli_num_rows($selectOffFoodRes) > 0) {
                $checkFoodExistenceQuery = "SELECT * FROM Food WHERE Food.id = '$foodID' AND Food.restaurant_id = '$restaurantID'";
                $checkFoodExistenceRes = mysqli_query($connect, $checkFoodExistenceQuery);
                if (mysqli_num_rows($checkFoodExistenceRes) > 0) {
                    $checkOffQuery = "SELECT * FROM Off WHERE id = '$offID'";
                    $checkOffRes = mysqli_query($connect, $checkOffQuery);
                    if (mysqli_num_rows($checkOffRes) > 0) {
                        mysqli_query($connect, "DELETE FROM RelFoodOff WHERE food_id = '$foodID' AND off_id = '$offID'");
                        $response['resultCode'] = 200;
                        $response['message'] = "موفق";
                    } else {
                        $response['resultCode'] = 100;
                        $response['message'] = "تخفیف وارد شده موجود نیست";
                    }
                } else {
                    $response['resultCode'] = 100;
                    $response['message'] = "غذای وارد شده موجود نیست.";
                }
            } else {
                $response['resultCode'] = 100;
                $response['message'] = "هیچ رابطه ای بین غذا و تخفیف وارد شده موجود نیست.";
            }
        }
        die(json_encode($response));
    } else {
        die('Unauthorized !');
    }
}
