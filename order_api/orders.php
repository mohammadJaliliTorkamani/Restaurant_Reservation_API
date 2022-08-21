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
    $code = null;
    $pusheID = null;
    $sharedKey = null;
    $headers = getallheaders();
    foreach ($headers as $key => $val) {
        if (strcmp($key, "Token") == 0)
            $token = $val;
        else if (strcmp($key, "Code") == 0)
            $code = $val;
        else if (strcmp($key, "Pusheid") == 0)
            $pusheID = $val;
        else if (strcmp($key, "Encsharedkey") == 0)
            $sharedKey = $val;
    }

    $UserValidator = new UserValidator($token);
    if ($UserValidator->isValidUser()) {
        $orders = [];
        $cypher = new MCrypt($sharedKey);
        $userID = $UserValidator->getUserID();
        $query = "SELECT * FROM LexinOrder WHERE orderer_normal_user_id = '$userID' ";
        $orderRes = mysqli_query($connect, $query);
        while ($order = mysqli_fetch_assoc($orderRes)) {
            $theOrderID = $order['id'];
            $toReturn1['id']=$theOrderID;
            $addressFetcherQuery = "SELECT Address.latitude,Address.longitude FROM Address,Deliver WHERE Deliver.order_id = '$theOrderID' AND Deliver.destination_address_id = Address.id";
            $addressFetcherRes = mysqli_query($connect, $addressFetcherQuery);
            $addressFetcherFetchResult = mysqli_fetch_assoc($addressFetcherRes);
            $toReturn1['latitude'] = $addressFetcherFetchResult['latitude'];
            $toReturn1['longitude'] = $addressFetcherFetchResult['longitude'];
            $restaurantID = $order['restaurant_id'];
            $restaurantNameQuery = "SELECT name FROM Restaurant WHERE id = '$restaurantID'";
            $restaurantNameRes = mysqli_query($connect, $restaurantNameQuery);
            $restaurant = mysqli_fetch_assoc($restaurantNameRes);
            $toReturn1['restaurant'] = $cypher->encrypt($restaurant['name']);
            $toReturn1['date_and_time_start'] = $cypher->encrypt($order['to_deliver_time']);
            $toReturn1['date_and_time_end'] = $cypher->encrypt($order['to_deliver_time']);
            $toReturn1['qrCodeValue'] = $cypher->encrypt($order['qr_code_issue_tracking_no']);
            $toReturn2 = $toReturn1;
            $orderID = $toReturn1['id'];
            $bills = [];
            $foodOrdersQuery = "SELECT * FROM FoodOrder where order_id= '$orderID'";
            $foodOrderRes = mysqli_query($connect, $foodOrdersQuery);
            $totalPrice = 0;
            while ($foodOrder = mysqli_fetch_assoc($foodOrderRes)) {
                $order1['foodID'] = $foodOrder['food_id'];
                $order1['counter'] = $foodOrder['count_number'];
                $order1['totalCost'] = $foodOrder['price'];
                $totalPrice = $totalPrice + $foodOrder['price'];
                $order1['lexinTableID'] = -1;
                array_push($bills, $order1);
            }
            $tableOrdersQuery = "SELECT LexinTableOrder.lexin_table_id,LexinTable.price FROM LexinTableOrder,LexinTable WHERE LexinTableOrder.order_id= '$orderID' AND LexinTable.id = LexinTableOrder.lexin_table_id";
            $tableOrderRes = mysqli_query($connect, $tableOrdersQuery);
            while ($tableOrder = mysqli_fetch_assoc($tableOrderRes)) {
                $order2['foodID'] = -1;
                $order2['lexinTableID'] = $tableOrder['lexin_table_id'];
                $order2['totalCost'] = $tableOrder['price'];
                $totalPrice = $totalPrice + $tableOrder['price'];
                array_push($bills, $order2);
            }
            $toReturn2['totalPrice'] = $totalPrice;
            $toReturn2['delivery'] = $order['type'] == 'serve' ? false : true;
            $toReturn2['specifiedBills'] = $bills;
            array_push($orders, $toReturn2);
        }
        die(json_encode($orders));

    }
}
?>

