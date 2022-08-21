<?php
require_once('../UserValidator.php');
require_once('../PersianDate.php');
require_once('../MCrypt.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'lexeense_admin');
define('PASSWORD', 'admin@lexeen123_#');
define('DATABASE', 'lexeense_Main_DB');
date_default_timezone_set("Asia/Tehran");

/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
/*::                                                                         :*/
/*::  This routine calculates the distance between two points (given the     :*/
/*::  latitude/longitude of those points). It is being used to calculate     :*/
/*::  the distance between two locations using GeoDataSource(TM) Products    :*/
/*::                                                                         :*/
/*::  Definitions:                                                           :*/
/*::    South latitudes are negative, east longitudes are positive           :*/
/*::                                                                         :*/
/*::  Passed to function:                                                    :*/
/*::    lat1, lon1 = Latitude and Longitude of point 1 (in decimal degrees)  :*/
/*::    lat2, lon2 = Latitude and Longitude of point 2 (in decimal degrees)  :*/
/*::    unit = the unit you desire for results                               :*/
/*::           where: 'M' is statute miles (default)                         :*/
/*::                  'K' is kilometers                                      :*/
/*::                  'N' is nautical miles                                  :*/
/*::  Worldwide cities and other features databases with latitude longitude  :*/
/*::  are available at https://www.geodatasource.com                          :*/
/*::                                                                         :*/
/*::  For enquiries, please contact sales@geodatasource.com                  :*/
/*::                                                                         :*/
/*::  Official Web site: https://www.geodatasource.com                        :*/
/*::                                                                         :*/
/*::         GeoDataSource.com (C) All Rights Reserved 2018                  :*/
/*::                                                                         :*/
/*::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::*/
function distance($lat1, $lon1, $lat2, $lon2, $unit)
{
    if (($lat1 == $lat2) && ($lon1 == $lon2)) {
        return 0;
    } else {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        $unit = strtoupper($unit);

        if ($unit == "K") {
            return ($miles * 1.609344);
        } else if ($unit == "N") {
            return ($miles * 0.8684);
        } else {
            return $miles;
        }
    }
}

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
        $cypher = new MCrypt($sharedKey);
        $userID = $UserValidator->getUserID();
        $restaurantID = $cypher->getRestaurantID($code);
        $token_table = "Token";
        $return_arr = [];
        $data = json_decode(file_get_contents('php://input'), true);
        $latitude = $data['latitude'];
        $longitude = $data['longitude'];
        $restaurantLocationQuery = "SELECT Address.latitude,Address.longitude,Restaurant.service_radius_km as radius FROM Address,Restaurant WHERE Address.id=Restaurant.address_id";
        $restaurantLocationRes = mysqli_query($connect, $restaurantLocationQuery);
        $restaurantLocationFetchResult = mysqli_fetch_assoc($restaurantLocationRes);
        $restaurantLatitude = $restaurantLocationFetchResult['latitude'];
        $restaurantLongitude = $restaurantLocationFetchResult['longitude'];
        $restaurantServiceRadius = $restaurantLocationFetchResult['radius'];
        $distanceInKiloMeter = distance($latitude, $longitude, $restaurantLatitude, $restaurantLongitude, 'K');
        $legalToDelivery = (int)(1000 * $distanceInKiloMeter) <= (int)(1000 * $restaurantServiceRadius);
        if ($legalToDelivery) {
            $orderTime = strtotime("" . date("H:i"));
            $workingTimeQuery = "SELECT DeliveryTime.start_time,DeliveryTime.end_time FROM RelRestaurantDeliveryTime,DeliveryTime WHERE DeliveryTime.id = RelRestaurantDeliveryTime.delivery_time_id AND RelRestaurantDeliveryTime.restaurant_id = '$restaurantID'";
            $workingTimeRes = mysqli_query($connect, $workingTimeQuery);
            $legal = false;
            while ($workingTimeFetchRes = mysqli_fetch_assoc($workingTimeRes)) {
                $startTime = strtotime($workingTimeFetchRes['start_time']);
                $endTime = strtotime($workingTimeFetchRes['end_time']);
                if ($orderTime >= $startTime && $orderTime <= $endTime)
                    $legal = true;
            }
            if ($legal) {
                $bills = $data['specifiedBills'];
                foreach ($bills as $bill) {
                    $foodID = $bill['foodID'];
                    if ($foodID > 0) {
                        $toEatQuery = "SELECT food_to_eat FROM Food WHERE id = '$foodID'";
                        $toEatResult = mysqli_query($connect, $toEatQuery);
                        $toEatFetchResult = mysqli_fetch_assoc($toEatResult)['food_to_eat'];
                        if ($toEatFetchResult == 1)
                            $numberOfToEatFoods = $numberOfToEatFoods + 1;
                    }
                }

                if ($numberOfToEatFoods > 0) {
                    $totalPrice = 0;
                    $date = (new gregorian2jalali())->gregorian_to_jalali() . " " . date("H:i");
                    $date2 = (new gregorian2jalali())->gregorian_to_jalali() . " " . date("H:i:s");
                    $toDeliverTime = $date;
                    $explodedDate = explode("/", explode(" ", $date2)[0]);
                    $explodedTime = explode(":", explode(" ", $date2)[1]);
                    $qrCodeIssueTrackingNo = hash('md4', $userID . "_LXN_" . $explodedDate[0] . $explodedDate[1] . $explodedDate[2] . $explodedTime[0] . $explodedTime[1] . $explodedTime[2] . rand(1, 300000000));
                    //calculating totalPrice AND then, insert new lexinOrder record

                    $processedFoodIDS = [];
                    foreach ($bills as $bill) {
                        $foodID = $bill['foodID'];
                        $deskID = -1;
                        $countNumber = $bill['counter'];

                        if ($foodID > 0 && $deskID == -1) {
                            $priceQuery = "SELECT price FROM Food WHERE id = '$foodID'";
                            $priceQueryRes = mysqli_query($connect, $priceQuery);
                            $foodPrice = mysqli_fetch_assoc($priceQueryRes)['price'];
                            $totalPrice = $totalPrice + $foodPrice * $countNumber;
                        }
                    }
                    $discountID = -1;
                    $discountPercentage = -1;
                    if ($data["discountID"] != -1) {
                        $discountID = $data["discountID"];
                        $findDiscountIDQuery = "SELECT percentage FROM Discount WHERE id = '$discountID'";
                        $findDiscountIDQueryRes = mysqli_query($connect, $findDiscountIDQuery);
                        $discountPercentage = mysqli_fetch_assoc($findDiscountIDQueryRes)['percentage'];
                        $totalPrice = (1 - $discountPercentage / 100) * $totalPrice;
                    }

                    if ($discountID > 0)
                        mysqli_query($connect, "INSERT INTO RelNormalUserDiscount(normal_user_id,discount_id) VALUES('$userID','$discountID')");
                    $query = "INSERT INTO LexinOrder(restaurant_id,discount_id,total_price,qr_code_issue_tracking_no,type,orderer_normal_user_id,to_deliver_time) VALUES('$restaurantID','$discountID','$totalPrice','$qrCodeIssueTrackingNo','delivery','$userID','$toDeliverTime')";

                    $res = mysqli_query($connect, $query);
                    $insertedOrderID = mysqli_insert_id($connect);
                    //now it's time to insert food bills
                    foreach ($bills as $bill) {
                        $foodID = $bill['foodID'];
                        $deskID = -1;
                        $countNumber = $bill['counter'];

                        //no calculating total cost and creating query for each mode
                        if ($foodID > 0 && $deskID == -1) {
                            $priceQuery = "SELECT price FROM Food WHERE id = '$foodID'";
                            $priceQueryRes = mysqli_query($connect, $priceQuery);
                            $foodPrice = mysqli_fetch_assoc($priceQueryRes)['price'];
                            $specifiedTotalFoodPrice = $foodPrice * $countNumber;
                            if ($discountPercentage != -1)
                                $specifiedTotalFoodPrice = (1 - $discountPercentage / 100) * $specifiedTotalFoodPrice;
                            $query = "INSERT INTO FoodOrder(food_id,count_number,order_id,price) VALUES('$foodID','$countNumber','$insertedOrderID','$specifiedTotalFoodPrice')";
                        }
                        if ($query != NULL)
                            mysqli_query($connect, $query);
                    }
                    $updateCashOfProfile = "UPDATE NormalUser SET cash = cash - '$totalPrice' WHERE id = '$userID'";
                    mysqli_query($connect, $updateCashOfProfile);

                    if ($discountPercentage != -1) {
                        $discountUsageQuery = "UPDATE Discount SET used_times = used_times+1 WHERE ID = '$discountID'";
                        mysqli_query($connect, $discountUsageQuery);
                    }
                    $floor = $data['floor'];
                    $unit = $cypher->decrypt($data['unit']);
                    $blockNo = $cypher->decrypt($data['blockNo']);
                    $addressQuery = "INSERT INTO Address(block,floor,unit,latitude,longitude) VALUES('$blockNo','$floor','$unit','$latitude','$longitude')";
                    mysqli_query($connect, $addressQuery);
                    $addressID = mysqli_insert_id($connect);
                    $deliverQuery = "INSERT INTO Deliver(deliverer_id,order_id,destination_address_id,status) VALUES('1','$insertedOrderID','$addressID','JUST ORDERED')";
                    mysqli_query($connect, $deliverQuery);
                    $response['code'] = 101;
                    $response['message'] = $insertedOrderID;
                } else {
                    $response['code'] = 102;
                    $response['message'] = $cypher->encrypt("غذایی برای تحویل انتخاب نکرده اید");
                }
            } else {
                $response['code'] = 102;
                $response['message'] = $cypher->encrypt("رستوران در این ساعت قادر به تحویل غذا در محل نیست.");
            }
        } else {
            $response['code'] = 102;
            $response['message'] = $cypher->encrypt("رستوران مجاز به تحویل غذا در موقعیت انتخاب شده نیست.");
        }
        die(json_encode($response));
    }
}
