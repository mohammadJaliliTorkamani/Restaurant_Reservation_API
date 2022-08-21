<?php
header('Access-Control-Allow-Origin: *'); //allow everybody
date_default_timezone_set("Asia/Tehran");
require_once('../UserValidator.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'cpres873_Aban');
define('PASSWORD', 'KimiaAndMohammad');
define('DATABASE', 'cpres873_AbanDatabase');
$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
//is food bills must not be used because of making unique order for tables and foods
if ($connect) {
    mysqli_set_charset($connect, "utf8");
    $token = $_GET['Token'];
    $userValidator = new UserValidator($token);
    if ($userValidator->isValidUser()) {
        $currentRestaurantID = $userValidator->getRestaurantID();
        //////
        $orders = [];
        $QRITN = $_GET['qrcode_value'];
        if ($QRITN != null) {
            $query = "SELECT LexinOrder.id as id,LexinOrder.restaurant_id as restaurant_id,total_price,status,to_deliver_time,type,NormalUser.name,NormalUser.last_name,qr_code_issue_tracking_no,discount_id FROM LexinOrder,NormalUser  WHERE NormalUser.id = orderer_normal_user_id AND qr_code_issue_tracking_no = '$QRITN' AND LexinOrder.restaurant_id = '$currentRestaurantID'";
        } else {
            $query = "SELECT LexinOrder.id as id,LexinOrder.restaurant_id as restaurant_id,total_price,status,to_deliver_time,type,NormalUser.name,NormalUser.last_name,qr_code_issue_tracking_no,discount_id FROM LexinOrder,NormalUser WHERE LexinOrder.restaurant_id = '$currentRestaurantID' AND NormalUser.id=orderer_normal_user_id";
        }

        $orderRes = mysqli_query($connect, $query);
        while ($order = mysqli_fetch_assoc($orderRes)) {
            $toReturn1['id'] = $order['id'];
            $toReturn1['discount_id'] = $order['discount_id'];
            $discountID = (int)$order['discount_id'];
            if ($discountID != -1) {
                $findDiscountCodeQuery = "SELECT Discount.code FROM Discount WHERE id = '$discountID'";
                $findDiscountCodeRes = mysqli_query($connect, $findDiscountCodeQuery);
                $code = mysqli_fetch_assoc($findDiscountCodeRes)['code'];
                $toReturn1['discount_code'] = $code;
            } else
                $toReturn1['discount_code'] = null;

            $toReturn1['orderer'] = $order['name'] . " " . $order['last_name'];
            $restaurantID = $order['restaurant_id'];
            $restaurantNameQuery = "SELECT name FROM Restaurant WHERE id = '$restaurantID'";
            $restaurantNameRes = mysqli_query($connect, $restaurantNameQuery);
            while ($restaurant = mysqli_fetch_assoc($restaurantNameRes)) {
                $toReturn1['restaurant'] = $restaurant['name'];
            }
            $toReturn1['date_and_time_start'] = $order['to_deliver_time'];
            $toReturn1['date_and_time_end'] = $order['to_deliver_time'];
            $toReturn1['qrCodeValue'] = $order['qr_code_issue_tracking_no'];
            $orderID = $toReturn1['id'];
            $bills = [];
            $foodOrdersQuery = "SELECT Food.name,FoodOrder.count_number,FoodOrder.price FROM FoodOrder,Food where order_id= '$orderID' AND FoodOrder.food_id = Food.id";
            $foodOrderRes = mysqli_query($connect, $foodOrdersQuery);
            $totalPrice = 0;
            while ($foodOrder = mysqli_fetch_assoc($foodOrderRes)) {
                $order1['foodName'] = $foodOrder['name'];
                $order1['counter'] = $foodOrder['count_number'];
                $order1['totalCost'] = $foodOrder['price'];
                $totalPrice = $totalPrice + $foodOrder['price'];
                array_push($bills, $order1);
            }
            //////////////////
            $tableOrdersQuery = "SELECT LexinTable.label,LexinTable.roof,LexinTable.price FROM LexinTableOrder,LexinTable WHERE LexinTableOrder.order_id =  '$orderID' AND LexinTable.id = LexinTableOrder.lexin_table_id";
            $tableOrderRes = mysqli_query($connect, $tableOrdersQuery);
            while ($tableOrder = mysqli_fetch_assoc($tableOrderRes)) {
                $order2['foodName'] = -1;
                $order2['lexinTableLabel'] = $tableOrder['label'];
                $order2['lexinTableRoof'] = $tableOrder['roof'];
                $order2['totalCost'] = $tableOrder['price'];
                $totalPrice = $totalPrice + $tableOrder['price'];
                array_push($bills, $order2);
            }
            $toReturn1['totalPrice'] = $totalPrice;
            $toReturn1['specifiedBills'] = $bills;
            if ($order['type'] == 'delivery') {
                $addressFinderQuery = "SELECT Address.block,Address.floor,Address.unit,Address.latitude,Address.longitude FROM Address,Deliver WHERE Deliver.destination_address_id = Address.id AND Deliver.order_id = '$orderID'";
            } else {
                $addressFinderQuery = "SELECT Address.block,Address.floor,Address.unit,Address.latitude,Address.longitude FROM Address,Restaurant WHERE Restaurant.address_id = Address.id AND Address.id AND  Restaurant.id = '$restaurantID'";
            }

            $addressFinderRes = mysqli_query($connect, $addressFinderQuery);
            $addressFinderFetchRes = mysqli_fetch_assoc($addressFinderRes);
            $toReturn1['blockNo'] = $addressFinderFetchRes['block'];
            $toReturn1['unit'] = $addressFinderFetchRes['unit'];
            $toReturn1['floor'] = $addressFinderFetchRes['floor'];
            $toReturn1['latitude'] = $addressFinderFetchRes['latitude'];
            $toReturn1['longitude'] = $addressFinderFetchRes['longitude'];


            $toReturn1['delivery'] = $order['type'] == 'serve' ? false : true;
            $toReturn1['status'] = $order['status'];
            array_push($orders, $toReturn1);
        }
        die(json_encode($orders));
    } else {
        die('Unauthorized !');
    }
}
?>