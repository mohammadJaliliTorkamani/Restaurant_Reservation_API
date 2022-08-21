<?php
header('Access-Control-Allow-Origin: *'); //allow everybody
date_default_timezone_set("Asia/Tehran");
require_once('../UserValidator.php');
require_once('../../PersianDate.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'lexeense_admin');
define('PASSWORD', 'admin@lexeen123_#');
define('DATABASE', 'lexeense_adminDatabase');
$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');

if ($connect) {

    mysqli_set_charset($connect, "utf8");

    $name = $_POST['name'];
    $foodID = $_POST['id'];
    $calories = $_POST['calories'];
    $cookTime = $_POST['cook_time_minute'];
    $description = $_POST['description'];
    $foodToEat = $_POST['food_to_eat'];
    $price = $_POST['price'];
    $dateTime = (new gregorian2jalali)->gregorian_to_jalali() . " " . date('H:i:s');

    $submitToAdd = $_POST['submit_to_add']; //if be true means add,if false means delete

    $token = $_POST['Token'];
    $userValidator = new UserValidator($token);
    if ($userValidator->isValidUser()) {
        $restaurantID = $userValidator->getRestaurantID();

        if ($submitToAdd == "true") { //want to add such food
            $upload_image = $dateTime . "_" . $token . "_" . $_FILES["foodImage"]["name"];

            $upload_image = str_ireplace(array(':', '/', '*', '!', '-', '+'), '_', $upload_image);
            $upload_image = preg_replace('/\s+/', '_', $upload_image);
            $upload_image = preg_replace('/\.(?=.*\.)/', '_', $upload_image); //replace all dots with _ , except the last dot

            $absoluteAddress = "https://lexeen.ir/kntu_project/assets/foods/" . $upload_image;
            move_uploaded_file($_FILES["foodImage"]["tmp_name"], "../../../assets/foods/" . $upload_image);
            $insertImageQuery = "INSERT INTO Gallery(one) VALUES ('$absoluteAddress')";
            mysqli_query($connect, $insertImageQuery);
            $insertedImageID = mysqli_insert_id($connect);

            $checkFoodExistenceQuery = "SELECT id FROM Food WHERE restaurant_id='$restaurantID' AND name ='$name' AND deleted='0'";
            $checkFoodExistenceRes = mysqli_query($connect, $checkFoodExistenceQuery);
            if (mysqli_num_rows($checkFoodExistenceRes) > 0) {
                $response['resultCode'] = 100;
                $response['message'] = "غذای وارد شده تکراری است";
            } else {
                mysqli_query($connect, "INSERT INTO Food(name,restaurant_id,calories,cook_time_minutes,food_to_eat,description,price,pictures_id,valid_to_cook,deleted,creation_time,accessbility_modification_time) VALUES('$name','$restaurantID','$calories','$cookTime','$foodToEat','$description','$price','$insertedImageID','1','0','$dateTime','$dateTime')");
                $response['resultCode'] = 200;
                $response['message'] = "موفق";
                $response['insertedFoodID'] = mysqli_insert_id($connect);
            }
        } else { //we wanna delete such food
            $checkFoodExistenceQuery = "SELECT * FROM Food WHERE restaurant_id='$restaurantID' AND id ='$foodID' AND deleted='0'";
            $checkFoodExistenceRes = mysqli_query($connect, $checkFoodExistenceQuery);
            if (mysqli_num_rows($checkFoodExistenceRes) == 0) {
                $response['resultCode'] = 100;
                $response['message'] = "غذای وارد شده موجود نیست";
            } else {
                mysqli_query($connect, "UPDATE Food SET deleted='1',delete_time = '$dateTime',valid_to_cook='0' WHERE id ='$foodID' AND restaurant_id = '$restaurantID'");
                $response['resultCode'] = 200;
                $response['message'] = "موفق";
            }
        }
        die(json_encode($response));
    } else {
        die('Unauthorized !');
    }
}
