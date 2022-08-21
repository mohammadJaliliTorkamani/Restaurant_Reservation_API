<?php
header('Access-Control-Allow-Origin: *'); //allow everybody
date_default_timezone_set("Asia/Tehran");
require_once('../UserValidator.php');
require_once('../../PersianDate.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'cpres873_Aban');
define('PASSWORD', 'KimiaAndMohammad');
define('DATABASE', 'cpres873_AbanDatabase');
$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');

//is food bills must not be used because we wanna display only one item
if ($connect) {
    mysqli_set_charset($connect, "utf8");
    $token = $_GET['Token'];
    $userValidator = new UserValidator($token);
    if ($userValidator->isValidUser()) {
        $restaurantID = $userValidator->getRestaurantID();
        $orders = [];
        $receivedStartTime = $_GET['start_time'];
        $receivedEndTime = $_GET['end_time'];
        if ($receivedStartTime == null && $receivedEndTime == null) { //so we return today's meals
            $startDate = date('Y/m/d');
            $endDate = date("Y/m/d", strtotime('+168 hours'));
            $startObj = new gregorian2jalali;
            $startObj->mydate = $startDate;
            $receivedStartTime = $startObj->gregorian_to_jalali() . " 00:00";
            $startObj->mydate = $endDate;
            $receivedEndTime = $startObj->gregorian_to_jalali() . " 23:59";
        }

        $query = "SELECT LexinOrder.id as id,NormalUser.name as orderer_fname,NormalUser.last_name as orderer_lname,NormalUser.phone as orderer_phone,LexinOrder.restaurant_id as restaurant_id,total_price,status,to_deliver_time,type,orderer_normal_user_id,qr_code_issue_tracking_no,discount_id FROM LexinOrder,NormalUser WHERE to_deliver_time<= '$receivedEndTime' AND to_deliver_time >= '$receivedStartTime' AND  LexinOrder.restaurant_id = '$restaurantID' AND NormalUser.id=LexinOrder.orderer_normal_user_id ORDER BY LexinOrder.status desc,LexinOrder.to_deliver_time";
        $orderRes = mysqli_query($connect, $query);
        while ($order = mysqli_fetch_assoc($orderRes)) {
            $toReturn1['id'] = $order['id'];
            $toReturn1['discount_id'] = $order['discount_id'];
            $restaurantID = $order['restaurant_id'];
            $restaurantNameQuery = "SELECT name FROM Restaurant WHERE id = '$restaurantID'";
            $restaurantNameRes = mysqli_query($connect, $restaurantNameQuery);
            while ($restaurant = mysqli_fetch_assoc($restaurantNameRes)) {
                $toReturn1['restaurant'] = $restaurant['name'];
            }
            $toReturn1['orderer'] = $order['orderer_fname'] . " " . $order['orderer_lname'];
            $toReturn1['phone'] = $order['orderer_phone'];

            $toDeliverTime = explode(' ', $order['to_deliver_time']);
            
            $toReturn1['date_and_time_start'] = $toDeliverTime[1]."   ".$toDeliverTime[0];
            $toReturn1['date_and_time_end'] = $order['to_deliver_time'];
            $toReturn1['qrCodeValue'] = $order['qr_code_issue_tracking_no'];
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
            //////////////////
            $tableOrdersQuery = "SELECT LexinTableOrder.lexin_table_id,LexinTable.price FROM LexinTableOrder,LexinTable WHERE LexinTableOrder.lexin_table_id = LexinTable.id AND LexinTableOrder.order_id = '$orderID'";
            $tableOrderRes = mysqli_query($connect, $tableOrdersQuery);
            while ($tableOrder = mysqli_fetch_assoc($tableOrderRes)) {
                $order2['foodID'] = -1;
                $order2['lexinTableID'] = $tableOrder['lexin_table_id'];
                $order2['totalCost'] = $tableOrder['price'];
                $totalPrice = $totalPrice + $tableOrder['price'];
                array_push($bills, $order2);
            }
            
            $toReturn1['status'] = $order['status'];
            $toReturn1['totalPrice'] = $totalPrice;
            $toReturn1['specifiedBills'] = $bills;
            $toReturn1['delivery'] = $order['type'] == 'serve' ? false : true;
            array_push($orders, $toReturn1);
        }
        die(json_encode($orders));
        //////
    } else {
        die('Unauthorized !');
    }
}
?>