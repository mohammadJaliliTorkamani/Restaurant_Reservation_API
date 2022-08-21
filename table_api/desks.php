<?php
require_once('../UserValidator.php');
require_once('../MCrypt.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'cpres873_Aban');
define('PASSWORD', 'KimiaAndMohammad');
define('DATABASE', 'cpres873_KNTU_Database');

date_default_timezone_set("Asia/Tehran");

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
if ($connect) {
    mysqli_set_charset($connect, "utf8");
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
        $date = $_GET['selected_date'];
        $roof = $_GET['roof'];

        $cipher = new MCrypt($sharedKey);

        $restaurantID = $cipher->getRestaurantID($code);
        $dateObj = strtotime($date);
        $query = "SELECT Desk.id as id,Desk.top_chair_id,Desk.start_chair_id,Desk.end_chair_id,Desk.bottom_chair_id,Desk.row_index,Desk.column_index,LexinTable.price,LexinTable.roof,LexinTable.label,Desk.previous_desk_id,Desk.next_desk_id,RelDeskLexinTable.lexin_table_id  FROM Desk,LexinTable,RelDeskLexinTable  WHERE LexinTable.roof = '$roof' AND RelDeskLexinTable.lexin_table_id = LexinTable.id AND RelDeskLexinTable.desk_id = Desk.id AND LexinTable.restaurant_id = '$restaurantID' AND LexinTable.is_valid='1'";
        $res = mysqli_query($connect, $query);
        $return_arr = array();
        while ($row = mysqli_fetch_assoc($res)) {
            $row_array['id'] = $row['id'];
            $row_array['topChairID'] = $row['top_chair_id'];
            $row_array['bottomChairID'] = $row['bottom_chair_id'];
            $row_array['startChairID'] = $row['start_chair_id'];
            $row_array['endChairID'] = $row ['end_chair_id'];
            $row_array['row_index'] = $row['row_index'];
            $row_array['column_index'] = $row['column_index'];
            $row_array['previousDeskID'] = $row['previous_desk_id'];
            $row_array['nextDeskID'] = $row['next_desk_id'];
            $row_array['lexinTableID'] = $row['lexin_table_id'];
            $row_array['price'] = $row['price'];
            $row_array['roof'] = $row['roof'];
            $row_array['label'] = $cipher->encrypt($row['label']);
            $row_array['reserved'] = false; //put not reserved as default
            $deskID = $row['id'];
            $checkReservedDeskQuery = "SELECT start_time,end_time FROM RelDeskLexinTable,LexinTable,LexinTableOrder WHERE LexinTableOrder.lexin_table_id = LexinTable.id AND RelDeskLexinTable.lexin_table_id = LexinTable.id AND RelDeskLexinTable.desk_id = '$deskID' AND completed = '0' AND LexinTable.is_valid='1'";

            $checkReservedDeskRes = mysqli_query($connect, $checkReservedDeskQuery);
            while ($checkReservedDeskFetchRes = mysqli_fetch_assoc($checkReservedDeskRes)) {
                $deskStartTime = $checkReservedDeskFetchRes['start_time'];
                $deskEndTime = $checkReservedDeskFetchRes['end_time'];
                $deskStartTimeObj = strtotime($deskStartTime);
                $deskEndTimeObj = strtotime($deskEndTime);
                if ($dateObj >= $deskStartTimeObj && $dateObj <= $deskEndTimeObj)
                    $row_array['reserved'] = true;
            }
            array_push($return_arr, $row_array);
        }
        die(json_encode($return_arr));
    }
}
?>

