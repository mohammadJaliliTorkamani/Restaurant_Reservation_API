<?php
require_once('../UserValidator.php');
require_once('../MCrypt.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'lexeense_admin');
define('PASSWORD', 'admin@lexeen123_#');
define('DATABASE', 'lexeense_Main_DB');

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');

if ($connect) {
    mysqli_set_charset($connect, "utf8");
    $token = null;
    $sharedKey = null;
    $headers = getallheaders();
    foreach ($headers as $key => $val) {
        if (strcmp($key, "token") == 0)
            $token = $val;
        else if (strcmp($key, "encsharedkey") == 0)
            $sharedKey = $val;
    }

    $UserValidator = new UserValidator($token);
    if ($UserValidator->isValidUser()) {
        $cipher = new MCrypt($sharedKey);
        $class = $_GET['class'];
        $response = [];
        $query = "SELECT QrCode.qr_code,Gallery.one,Restaurant.active,Restaurant.class,Restaurant.id,Restaurant.name,Address.country as country,Address.state as state,Address.city as city,Address.street1 as street1,Address.street2 as street2,Address.alley1 as alley1,Address.alley2 as alley2,Address.block as block,Address.floor as floor,Address.unit as unit,Address.orientation orientation,RestaurantType.name as type FROM QrCode,Restaurant,RestaurantType,Address,Gallery WHERE Restaurant.type_id =RestaurantType.id AND Restaurant.address_id = Address.id AND Restaurant.pictures_id = Gallery.id AND Restaurant.id = QrCode.restaurant_id AND Restaurant.class = '$class' AND Restaurant.deleted='0' AND Restaurant.legitimated='1' order by Restaurant.active desc, Restaurant.priority desc";
        $qRes = mysqli_query($connect, $query);
        while ($res = mysqli_fetch_assoc($qRes)) {
            $item['id'] = $res['id'];
            $item['type'] = $cipher->encrypt($res['type']);
            $item['name'] = $cipher->encrypt($res['name']);
            $item['restaurantClass'] = $cipher->encrypt($res['class']);
            $item['active'] = $res['active'] == 1 ? true : false;
            $item['qrCode'] = $cipher->encrypt($res['qr_code']);

            $address['country'] = $cipher->encrypt($res['country']);
            $address['state'] = $cipher->encrypt($res['state']);
            $address['city'] = $cipher->encrypt($res['city']);
            $address['street1'] = $cipher->encrypt($res['street1']);
            $address['street2'] = $cipher->encrypt($res['street2']);
            $address['alley1'] = $cipher->encrypt($res['alley1']);
            $address['alley2'] = $cipher->encrypt($res['alley2']);
            $address['block'] = $cipher->encrypt($res['block']);
            $address['floor'] = $res['floor'];
            $address['unit'] = $cipher->encrypt($res['unit']);
            $address['orientation'] = $cipher->encrypt($res['orientation']);
            $item['address'] = $address;
            $pictures = [];
            array_push($pictures, $cipher->encrypt($res['one']));
            $item['pictures'] = $pictures;
            array_push($response, $item);
        }
        die(json_encode($response));
    }
}
