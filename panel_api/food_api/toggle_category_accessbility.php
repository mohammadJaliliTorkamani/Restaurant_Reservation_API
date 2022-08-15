<?php
define('HOSTNAME', 'localhost');
define('USERNAME', 'cpres873_Aban');
define('PASSWORD', 'KimiaAndMohammad');
define('DATABASE', 'cpres873_AbanDatabase');
$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
if ($connect) {
    mysqli_set_charset($connect, "utf8");
    date_default_timezone_set("Asia/Tehran");
    $categoryID = $_GET['category_id'];
    $restaurantID = $_GET['restaurant_id'];
    $enable = $_GET['submit_to_enable'];//if be true means enalbe(or if not exist,add),if false means disable
    $token = null;
    $headers = getallheaders();
    foreach ($headers as $key => $val) {
        if (strcmp($key, "Token") == 0)
            $token = $val;
    }
    $authorizeQuery = "SELECT * FROM Token WHERE value = '$token'";
    $authorizeRes = mysqli_query($connect, $authorizeQuery);
    if (mysqli_num_rows($authorizeRes) > 0) {
        if ($enable == "true") { //want to add or enable relation
            $checkRelationExistenceQuery = "SELECT * FROM RelCategoryRestaurant WHERE restaurant_id = '$restaurantID' AND category_id = '$categoryID'";
            $checkRelationExistenceRes = mysqli_query($connect, $checkRelationExistenceQuery);
            if (mysqli_num_rows($checkRelationExistenceRes) > 0) {
                $checkValidityQuery = "SELECT * FROM RelCategoryRestaurant WHERE restaurant_id = '$restaurantID' AND category_id = '$categoryID' AND is_valid='1'";
                $checkValidityRes = mysqli_query($connect, $checkValidityQuery);
                if (mysqli_num_rows($checkValidityRes) > 0) {//it's done before !
                    $response['resultCode'] = 100;
                    $response['message'] = "از قبل موجود است";
                } else {//enable it
                    mysqli_query($connect, "UPDATE RelCategoryRestaurant SET is_valid='1' WHERE restaurant_id = '$restaurantID' AND category_id = '$categoryID'");
                    $response['resultCode'] = 200;
                    $response['message'] = "موفق";
                }
            } else { //add  this relation
                mysqli_query($connect, "INSERT INTO RelCategoryRestaurant(restaurant_id,category_id,is_valid) VALUES('$restaurantID','$categoryID','1')");
                $response['resultCode'] = 200;
                $response['message'] = "موفق";
            }
        } else {//disable such relation
            $checkRelationExistenceQuery = "SELECT * FROM RelCategoryRestaurant WHERE restaurant_id = '$restaurantID' AND category_id = '$categoryID'";
            $checkRelationExistenceRes = mysqli_query($connect, $checkRelationExistenceQuery);
            if (mysqli_num_rows($checkRelationExistenceRes) > 0) {
                $checkInvalidityQuery = "SELECT * FROM RelCategoryRestaurant WHERE restaurant_id = '$restaurantID' AND category_id = '$categoryID' AND is_valid='0'";
                $checkInvalidityRes = mysqli_query($connect, $checkInvalidityQuery);
                if (mysqli_num_rows($checkInvalidityRes) > 0) {//it's done before !
                    $response['resultCode'] = 100;
                    $response['message'] = "از قبل موجود است";
                } else {//disable it
                    mysqli_query($connect, "UPDATE RelCategoryRestaurant SET is_valid='0' WHERE restaurant_id = '$restaurantID' AND category_id = '$categoryID'");
                    $response['resultCode'] = 200;
                    $response['message'] = "موفق";
                }
            } else {
                $response['resultCode'] = 100;
                $response['message'] = "موجود نیست";
            }
        }
        die(json_encode($response));
    } else {
        die('Unauthorized !');
    }
}
?>