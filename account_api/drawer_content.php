<?php

require_once('../UserValidator.php');
require_once('../MCrypt.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'lexeense_admin');
define('PASSWORD', 'admin@lexeen123_#');
define('DATABASE', 'lexeense_Main_DB');
define("ENCRYPTION_KEY", "!@#$%^&*");

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');

if ($connect) {
    $token = null;
    $sharedKey = null;
    $headers = getallheaders();
    foreach ($headers as $key => $val) {
        if (strcmp($key, "Token") == 0)
            $token = $val;
        else if (strcmp($key, "Encsharedkey") == 0)
            $sharedKey = $val;
    }
    $cypher = new MCrypt($sharedKey);
    mysqli_query($connect, 'SET CHARACTER SET utf8');
    $userValidator = new UserValidator($token);
    if ($userValidator->isValidUser()) {
        $userID = $userValidator->getUserID();
        $query = "SELECT name,last_name,cash FROM NormalUser WHERE id = '$userID'";
        $res = mysqli_fetch_assoc(mysqli_query($connect, $query));
        try {
            $toReturn['name'] = $cypher->encrypt($res['name']);
            $toReturn['lastName'] = $cypher->encrypt($res['last_name']);
            $toReturn['cash'] = $res['cash'];
        } catch (Excecption $e) {
            file_put_contents("Encryption Error File", json_encode($e));
        }
        die(json_encode($toReturn));
    }
}
