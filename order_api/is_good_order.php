<?php
require_once('../UserValidator.php');
require_once('../MCrypt.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'cpres873_Aban');
define('PASSWORD', 'KimiaAndMohammad');
define('DATABASE', 'cpres873_KNTU_Database');

function getActiveForServeHours($connect, $restaurantID)
{
    $workingTimeQuery = "SELECT ServingTime.start_time,ServingTime.end_time FROM RelRestaurantServingTime,ServingTime WHERE ServingTime.id = RelRestaurantServingTime.serving_time_id AND RelRestaurantServingTime.restaurant_id = '$restaurantID'";
    $workingHourSentence = "ساعت سرویس دهی امروز رستوران ";
    $workingTimeRes = mysqli_query($connect, $workingTimeQuery);
    $rowsLastIndex = mysqli_num_rows($workingTimeRes) - 1;
    $counterIndex = 0;
    $workingHourSentenceIntervals = "";
    while ($workingTimeFetchRes = mysqli_fetch_assoc($workingTimeRes)) {
        $counterIndex = $counterIndex + 1;
        $startTime = $workingTimeFetchRes['start_time'];
        $endTime = $workingTimeFetchRes['end_time'];
        $workingHourSentenceIntervals = $workingHourSentenceIntervals . "از " . $startTime . " تا " . $endTime . ($counterIndex < $rowsLastIndex ? " ، " : "");
    }
    $workingHourSentence = $workingHourSentence . $workingHourSentenceIntervals . " می باشد.";
    return $workingHourSentence;
}

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
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
        $numberOfToEatFoods = 0;
        $cipher = new MCrypt($sharedKey);
        $restaurantID = $cipher->getRestaurantID($code);
        $data = json_decode(file_get_contents('php://input'), true);
        $number = $data['n'];
        $orderTime = strtotime($data['orderTime']);
        $totalSum = 0;
        $totalChairNumber = 0;
        $bills = $data['bills'];
        foreach ($bills as $bill) {
            $foodID = $bill["foodID"];
            if ($foodID != -1) {
                $toEatQuery = "SELECT food_to_eat FROM Food WHERE id = '$foodID'";
                $toEatResult = mysqli_query($connect, $toEatQuery);
                $toEatFetchResult = mysqli_fetch_assoc($toEatResult)['food_to_eat'];
                if ($toEatFetchResult == 1)
                    $numberOfToEatFoods = $numberOfToEatFoods + 1;

                $query = "SELECT Food.price FROM Food WHERE Food.id = '$foodID'";
                $result = mysqli_query($connect, $query);
                $price = mysqli_fetch_assoc($result)["price"];
                $totalSum = $totalSum + $price * $bill["counter"];
            } else {
                $lexinTableID = $bill['lexinTableID'];
                $desksExtractorQuery = "SELECT RelDeskLexinTable.desk_id FROM Desk,RelDeskLexinTable WHERE Desk.id = RelDeskLexinTable.desk_id AND RelDeskLexinTable.lexin_table_id = '$lexinTableID'";
                $desksExtractorResult = mysqli_query($connect, $desksExtractorQuery);
                while ($desksExtractorFetchResult = mysqli_fetch_assoc($desksExtractorResult)) {
                    $deskID = $desksExtractorFetchResult['desk_id'];
                    $chairExtractorQuery = "SELECT Desk.top_chair_id as top,Desk.bottom_chair_id as  bottom,Desk.start_chair_id as start,Desk.end_chair_id as end FROM Desk WHERE Desk.id = '$deskID'";
                    $chairExtractorResult = mysqli_query($connect, $chairExtractorQuery);
                    $chairExtractorFetchResult = mysqli_fetch_assoc($chairExtractorResult);
                    $totalChairNumber = $totalChairNumber + ($chairExtractorFetchResult['top'] != -1 ? 1 : 0);
                    $totalChairNumber = $totalChairNumber + ($chairExtractorFetchResult['bottom'] != -1 ? 1 : 0);
                    $totalChairNumber = $totalChairNumber + ($chairExtractorFetchResult['start'] != -1 ? 1 : 0);
                    $totalChairNumber = $totalChairNumber + ($chairExtractorFetchResult['end'] != -1 ? 1 : 0);
                }
            }
        }

//        $minFoodQuery = "SELECT MIN(price) as minimum FROM Food WHERE Food.food_to_eat = '1' AND  Food.restaurant_id = '$restaurantID' AND deleted = '0' AND valid_to_cook = '1'";
//        $minFoodRes = mysqli_query($connect, $minFoodQuery);
//        $mimimumPrice = mysqli_fetch_assoc($minFoodRes)['minimum'];
        $ans = ($totalChairNumber - 3 <= $number) && ($number <= $totalChairNumber);
        $response['code'] = 102;
        if ($ans) {
            //check serving of at least one food_to_eat
            if ($numberOfToEatFoods > 0) {
                //check serving time
                $workingTimeQuery = "SELECT ServingTime.start_time,ServingTime.end_time FROM RelRestaurantServingTime,ServingTime WHERE ServingTime.id = RelRestaurantServingTime.serving_time_id AND RelRestaurantServingTime.restaurant_id = '$restaurantID'";
                $workingTimeRes = mysqli_query($connect, $workingTimeQuery);

                while ($workingTimeFetchRes = mysqli_fetch_assoc($workingTimeRes)) {
                    $startTime = strtotime($workingTimeFetchRes['start_time']);
                    $endTime = strtotime($workingTimeFetchRes['end_time']);
                    if ($orderTime >= $startTime && $orderTime <= $endTime) {
                        $legal = true;
                        break;
                    }
                }
                if ($legal) {
                    $response['code'] = 101;
                } else {
                    $response['code'] = 102;
                    $response['message'] = $cipher->encrypt("رستوران در این ساعت قادر به ارایه خدمات نیست." . getActiveForServeHours($connect, $restaurantID));
                }
            } else {
                $response['code'] = 102;
                $response['message'] = $cipher->encrypt("غذایی برای سرو انتخاب نکرده اید");
            }
        } else {
            $response['code'] = 102;
            $response['message'] = $cipher->encrypt("عدم تناسب تعداد نفرات با میز های انتخاب شده");
        }
        die(json_encode($response));

    }
}
?>