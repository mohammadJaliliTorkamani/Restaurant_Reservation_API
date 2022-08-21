<?php
require_once('../UserValidator.php');
require_once('../MCrypt.php');
define('HOSTNAME', 'localhost');
define('USERNAME', 'cpres873_Aban');
define('PASSWORD', 'KimiaAndMohammad');
define('DATABASE', 'cpres873_KNTU_Database');

$connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
mysqli_set_charset($connect, "utf8");
if ($connect) {

    $token = null;
    $sharedKey = null;
    $code = null;
    $headers = getallheaders();
    foreach ($headers as $key => $val) {
        if (strcmp($key, "Token") == 0)
            $token = $val;
        else if (strcmp($key, "Encsharedkey") == 0)
            $sharedKey = $val;
    }

    $qrCode = $_GET['qrCode'];
    $UserValidator = new UserValidator($token);
    if ($UserValidator->isValidUser()) {
        $cipher = new MCrypt($sharedKey);
        $query = "SELECT Gallery.one,Restaurant.id,Restaurant.name,Address.id as address_id,Address.latitude,Address.longitude,Address.country,Address.state,Address.city,Address.street1,Address.street2,Address.alley1,Address.alley2,Address.block,Address.floor,Address.unit,Address.orientation,RestaurantType.name as type,Restaurant.phone FROM Restaurant,RestaurantType,Address,Gallery,QrCode WHERE Restaurant.id = QrCode.restaurant_id AND QrCode.qr_code = '$qrCode' AND Restaurant.type_id =RestaurantType.id AND Restaurant.address_id = Address.id AND Restaurant.pictures_id = Gallery.id";
        $qRes = mysqli_query($connect, $query);
        if(mysqli_num_rows($qRes)>0){
            $res = mysqli_fetch_assoc($qRes);
            $item['id'] = $res['id'];
            $item['type'] = $cipher->encrypt($res['type']);
            $item['name'] = $cipher->encrypt($res['name']);
            $item['phone'] = $cipher->encrypt($res['phone']);
            $item['encryptedCode'] = $cipher->encrypt($qrCode);
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
            $address['latitude'] = $res['latitude'];
            $address['longitude'] = $res['longitude'];
            $address['orientation'] = $cipher->encrypt($res['orientation']);
            
            $item['address'] = $address;
            $pictures = [];
            array_push($pictures, $res['one']);
            $item['pictures'] = $pictures;
        }
        die(json_encode($item));
    }
}
?>