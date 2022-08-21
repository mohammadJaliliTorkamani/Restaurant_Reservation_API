<?php
require_once('../UserValidator.php');
require_once('../MCrypt.php');
require_once('../PersianDate.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'lexeense_admin');
define('PASSWORD', 'admin@lexeen123_#');
define('DATABASE', 'lexeense_Main_DB');

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
mysqli_set_charset($connect, "utf8");
if ($connect) {
    $token = null;
    $code = null;
    $sharedKey = null;
    $headers = getallheaders();
    foreach ($headers as $key => $val) {
        if (strcmp($key, "Token") == 0)
            $token = $val;
        else if (strcmp($key, "Code") == 0)
            $code = $val;
        else if (strcmp($key, "Encsharedkey") == 0)
            $sharedKey = $val;
    }

    $UserValidator = new UserValidator($token);
    if ($UserValidator->isValidUser()) {
        $return_arr = [];
        $totalPrice = 0;
        $userID = $UserValidator->getUserID();
        $cypher = new MCrypt($sharedKey);
        $restaurantID = $cypher->getRestaurantID($code);

        $data = json_decode(file_get_contents('php://input'), true);
        date_default_timezone_set("Asia/Tehran");

        $date = (new gregorian2jalali)->gregorian_to_jalali() . " " . date("H:i");
        $date2 = (new gregorian2jalali)->gregorian_to_jalali() . " " . date("H:i:s");

        $receivedGregorianDateTime = $cypher->decrypt($data['date_and_time_start']);
        $receivedGregorianDate = explode(" ", $receivedGregorianDateTime)[0];
        $receivedGregorianTime = explode(" ", $receivedGregorianDateTime)[1];

        $shamsiConverterObj1 = new gregorian2jalali;
        $shamsiConverterObj1->mydate = $receivedGregorianDate;
        $receivedShamsiDate = $shamsiConverterObj1->gregorian_to_jalali();
        $receivedShamsiDateTime = $receivedShamsiDate . " " . $receivedGregorianTime;
        $endTimeGregorian = date("Y/m/d H:i", strtotime('+1 hours', strtotime($receivedGregorianDateTime)));
        $endGregorianDate = explode(" ", $endTimeGregorian)[0];
        $endGregorianTime = explode(" ", $endTimeGregorian)[1];
        $shamsiConverterObj1->mydate = $endGregorianDate;
        $endShamsiDate = $shamsiConverterObj1->gregorian_to_jalali();
        $endShamsiDateTime = $endShamsiDate . " " . $endGregorianTime;
        $explodedDate = explode("/", explode(" ", $date)[0]);
        $explodedTime = explode(":", explode(" ", $date2)[1]);
        $qrCodeIssueTrackingNo = hash('md4', $userID . "_LXN_" . $explodedDate[0] . $explodedDate[1] . $explodedDate[2] . $explodedTime[0] . $explodedTime[1] . rand(1, 400000000));
        //calculating totalPrice AND then, insert new lexinOrder record
        $bills = $data['specifiedBills'];
        foreach ($bills as $bill) {
            $foodID = $bill['foodID'];
            $lexinTableID = $bill['lexinTableID'];
            $countNumber = $bill['counter'];
            if ($foodID > 0 && $lexinTableID == -1) {
                $priceQuery = "SELECT price FROM Food WHERE id = '$foodID'";
                $priceQueryRes = mysqli_query($connect, $priceQuery);
                $foodPrice = mysqli_fetch_assoc($priceQueryRes)['price'];
                $totalPrice = $totalPrice + $foodPrice * $countNumber;
            } else {
                $lexinTableFindingQuery = "SELECT price from LexinTable WHERE id='$lexinTableID'";
                $lexinTableFindingRes = mysqli_query($connect, $lexinTableFindingQuery);
                $lexinTablePrice = mysqli_fetch_assoc($lexinTableFindingRes)['price'];
                $totalPrice = $totalPrice + $lexinTablePrice;
            }
        }
        $discountID = -1;
        $discountPercentage = -1;
        if ($data["discountID"] > 0) {
            $discountID = $data["discountID"];
            $findDiscountIDQuery = "SELECT percentage FROM Discount WHERE id = '$discountID'";
            $findDiscountIDQueryRes = mysqli_query($connect, $findDiscountIDQuery);
            $discountPercentage = mysqli_fetch_assoc($findDiscountIDQueryRes)['percentage'];
            $totalPrice = (1 - $discountPercentage / 100) * $totalPrice;
        }

        if ($discountID != -1)
            mysqli_query($connect, "INSERT INTO RelNormalUserDiscount(normal_user_id,discount_id,time_stamp) VALUES('$userID','$discountID','$date')");

        $query = "INSERT INTO LexinOrder(restaurant_id,discount_id,total_price,qr_code_issue_tracking_no,type,orderer_normal_user_id,to_deliver_time) VALUES('$restaurantID','$discountID','$totalPrice','$qrCodeIssueTrackingNo','serve','$userID','$receivedShamsiDateTime')";
        mysqli_query($connect, $query);
        $insertedOrderID = mysqli_insert_id($connect);
        //now it's time to insert food or lexinTable bills
        foreach ($bills as $bill) {
            $foodID = $bill['foodID'];
            $lexinTableID = $bill['lexinTableID'];
            $countNumber = $bill['counter'];

            //no calculating total cost and creating query for each mode
            if ($foodID > 0 && $lexinTableID == -1) {
                $priceQuery = "SELECT price FROM Food WHERE id = '$foodID'";
                $priceQueryRes = mysqli_query($connect, $priceQuery);
                $foodPrice = mysqli_fetch_assoc($priceQueryRes)['price'];
                $specifiedTotalFoodPrice = $foodPrice * $countNumber;
                if ($discountPercentage != -1)
                    $specifiedTotalFoodPrice = (1 - $discountPercentage / 100) * $specifiedTotalFoodPrice;
                $query = "INSERT INTO FoodOrder(food_id,count_number,order_id,price) VALUES('$foodID','$countNumber','$insertedOrderID','$specifiedTotalFoodPrice')";
            } else {
                $query = "INSERT INTO LexinTableOrder(lexin_table_id,order_id,start_time,end_time) VALUES('$lexinTableID','$insertedOrderID','$receivedShamsiDateTime','$endShamsiDateTime')";
            }
            mysqli_query($connect, $query);
        }
        $updateCashOfProfile = "UPDATE NormalUser SET cash = cash - '$totalPrice' WHERE id = '$userID'";
        mysqli_query($connect, $updateCashOfProfile);

        if ($discountPercentage != -1) {
            $discountUsageQuery = "UPDATE Discount SET used_times = used_times+1 WHERE ID = '$discountID'";
            mysqli_query($connect, $discountUsageQuery);
        }
        $response['code'] = 101;
        $response['message'] = $insertedOrderID;
        die(json_encode($response));
    }
}
