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
        $restaurantID = $cipher->getRestaurantID($code);
        $query = "SELECT DISTINCT roof FROM LexinTable WHERE LexinTable.restaurant_id = '$restaurantID'";
        $res = mysqli_query($connect, $query);
        $return_arr = [];
        while ($row = mysqli_fetch_assoc($res))
            array_push($return_arr, $row['roof']);
        die(json_encode($return_arr));
    }
}
