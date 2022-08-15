<?php
require_once('../UserValidator.php');
require_once('../MCrypt.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'cpres873_Aban');
define('PASSWORD', 'KimiaAndMohammad');
define('DATABASE', 'cpres873_KNTU_Database');

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
if ($connect) {
    mysqli_set_charset($connect, "utf8");
    $token = null;
    $sharedKey = null;
    $headers = getallheaders();
    foreach ($headers as $key => $val) {
        if (strcmp($key, "Token") == 0)
            $token = $val;
        else if (strcmp($key, "Encsharedkey") == 0)
            $sharedKey = $val;
    }
    $UserValidator = new UserValidator($token);
    if ($UserValidator->isValidUser()) {
        $cipher = new MCrypt($sharedKey);

        $restaurantName = $_GET['restaurant_name'];
        $query = "SELECT Restaurant.active,Gallery.one,Restaurant.id,Restaurant.name,Address.id as address_id,Address.country,Address.state,
Address.city,Address.street1,Address.street2,Address.alley1,Address.alley2,Address.block,Address.floor,Address.unit,Address.orientation,RestaurantType.name as type 
FROM Restaurant,RestaurantType,Address,Gallery WHERE Restaurant.type_id =RestaurantType.id AND Restaurant.address_id = Address.id
 AND Restaurant.pictures_id = Gallery.id AND Restaurant.name like '%$restaurantName%' order by Restaurant.priority";

        $restaurants = [];
        $result = mysqli_query($connect, $query);
        while ($res = mysqli_fetch_assoc($result)) {
            $item['id'] = $res['id'];
            $item['type'] = $cipher->encrypt($res['type']);
            $item['name'] = $cipher->encrypt($res['name']);
            $item['active'] = $res['active'] == 1 ? true : false;
            $item['encryptedCode'] = $cipher->encrypt($res['decrypted_qr_code']);
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
            array_push($pictures, $res['one']);
            $item['pictures'] = $pictures;
            array_push($restaurants, $item);
        }
        die(json_encode($restaurants));
    }
}
?>