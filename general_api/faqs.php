<?php
define('HOSTNAME', 'localhost');
define('USERNAME', 'lexeense_admin');
define('PASSWORD', 'admin@lexeen123_#');
define('DATABASE', 'lexeense_Main_DB');

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');

if ($connect) {
    mysqli_set_charset($connect, "utf8");
    $headers = getallheaders();
    $response = [];

    $query = "SELECT question,answer FROM FAQ ORDER BY priority desc";
    $qRes = mysqli_query($connect, $query);
    while ($res = mysqli_fetch_assoc($qRes)) {
        $item['question'] = $res['question'];
        $item['answer'] = $res['answer'];
        array_push($response, $item);
    }
    die(json_encode($response));
}
