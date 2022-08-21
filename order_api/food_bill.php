<?php
require_once('../UserValidator.php');
require_once('../MCrypt.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'lexeense_admin');
define('PASSWORD', 'admin@lexeen123_#');
define('DATABASE', 'lexeense_Main_DB');

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
mysqli_set_charset($connect, "utf8");
if ($connect) {

    $token = null;
    $code = null;
    $pusheID = null;
    $headers = getallheaders();
    foreach ($headers as $key => $val) {
        if (strcmp($key, "Token") == 0)
            $token = $val;
        else if (strcmp($key, "Code") == 0)
            $code = $val;
        else if (strcmp($key, "Pusheid") == 0)
            $pusheID = $val;
    }
    $UserValidator = new UserValidator($token);
    if ($UserValidator->isValidUser()) {
        $return_arr = [];
        $return_array = [];
        $data = file_get_contents('php://input');
        $biils = json_decode($data, true);
        foreach ($biils as $item) {
            if ($item['foodID'] != -1) {
                $foodID = $item['foodID'];
                $query = "SELECT price FROM Food WHERE id = '$foodID'";
                $newFoodBill = [];
                $res = mysqli_query($connect, $query);
                while ($food = mysqli_fetch_assoc($res)) {
                    $newFoodBill['foodID'] = $item['foodID'];
                    $newFoodBill['lexinTableID'] = -1;
                    $newFoodBill['counter'] = $item['counter'];
                    $newFoodBill['totalCost'] = $item['counter'] * $food['price'];
                    array_push($return_array, $newFoodBill);
                }
            } else {
                $deskID = $item['lexinTableID'];
                $query = "SELECT LexinTable.price,Desk.top_chair_id as top,Desk.bottom_chair_id as bottom,Desk.start_chair_id as start,Desk.end_chair_id as end FROM LexinTable,Desk,RelDeskLexinTable WHERE Desk.id = '$deskID' AND LexinTable.id = RelDeskLexinTable.lexin_table_id AND RelDeskLexinTable.desk_id = Desk.id";
                $newTableBill = [];
                $res = mysqli_query($connect, $query);
                while ($table = mysqli_fetch_assoc($res)) {
                    $newTableBill['lexinTableID'] = $deskID;
                    $newTableBill['foodID'] = -1;
                    $newTableBill['totalCost'] = $table['price'];
                    $counter = 0;
                    if ($table['top'] != -1)
                        $counter++;
                    if ($table['bottom'] != -1)
                        $counter++;
                    if ($table['start'] != -1)
                        $counter++;
                    if ($table['end'] != -1)
                        $counter++;
                    $newTableBill['counter'] = $counter;
                    array_push($return_array, $newTableBill);
                }
            }
        }
        die(json_encode($return_array));
    }
}
