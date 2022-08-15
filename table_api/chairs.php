<?php
define('HOSTNAME', 'localhost');
define('USERNAME', 'cpres873_Aban');
define('PASSWORD', 'KimiaAndMohammad');
define('DATABASE', 'cpres873_KNTU_Database');
require_once('../UserValidator.php');

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
mysqli_set_charset($connect, "utf8");
if ($connect) {
    $token = null;
    $headers = getallheaders();
    foreach ($headers as $key => $val) {
        if (strcmp($key, "Token") == 0)
            $token = $val;
    }

    $UserValidator = new UserValidator($token);
    if ($UserValidator->isValidUser()) {
        $deskID = $_GET['desk_id'];
        $query = "SELECT Desk.top_chair_id,Desk.start_chair_id,Desk.end_chair_id,Desk.bottom_chair_id FROM Desk,LexinTable,RelDeskLexinTable WHERE Desk.id = '$deskID' AND RelDeskLexinTable.lexin_table_id = LexinTable.id AND RelDeskLexinTable.desk_id = Desk.id AND LexinTable.is_valid='1'";
        $res = mysqli_query($connect, $query);
        if (mysqli_num_rows($res) == 1) {
            $row = mysqli_fetch_assoc($res);
            $startChair['id'] = $row['start_chair_id'];
            $startChair['deskID'] = $deskID;
            $endChair['id'] = $row['end_chair_id'];
            $endChair['deskID'] = $deskID;
            $topChair['id'] = $row['top_chair_id'];
            $topChair['deskID'] = $deskID;
            $bottomChair['id'] = $row['bottom_chair_id'];
            $bottomChair['deskID'] = $deskID;
            $row_array['topChair'] = $topChair;
            $row_array['bottomChair'] = $bottomChair;
            $row_array['startChair'] = $startChair;
            $row_array['endChair'] = $endChair;
            die(json_encode($row_array));
        }
    }
}
?>
