<?php

class MCrypt
{
    private $iv;
    private $secretKey;
    private $sharedKey;
    private $restaurantID;


    public function __construct($sharedKey)
    {
        $this->sharedKey = $sharedKey;
        $hashedPassword = hash("sha256", $sharedKey);
        $this->secretKey = substr($hashedPassword, 0, 16);
        $this->iv = substr($hashedPassword, strlen($hashedPassword) - 16, strlen($hashedPassword));
    }

    public function encrypt($str)
    {
        if ($this->sharedKey == null)
            return $str;
        //$this->secretKey = $this->hex2bin($this->secretKey);

        $td = mcrypt_module_open('rijndael-128', '', 'cbc', $this->iv);

        mcrypt_generic_init($td, $this->secretKey, $this->iv);
        $encrypted = mcrypt_generic($td, $str);

        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return bin2hex($encrypted);
    }

    public function getRestaurantID($qrCode)
    {
        if ($this->restaurantID != null)
            return $this->restaurantID;

        $decryptedQrCode = $this->decrypt($qrCode);

        $connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');
        $query = "SELECT restaurant_id as id FROM QrCode WHERE qr_code = '$decryptedQrCode' AND valid = '1'";

        $res = mysqli_query($connect, $query);
        $fResult = mysqli_fetch_assoc($res);
        $this->restaurantID = $fResult['id'];
        $connect->close();
        return $this->restaurantID;
    }

    public function decrypt($code)
    {
        if ($this->sharedKey == null)
            return $code;
        //$this->secretKey = $this->hex2bin($this->secretKey);
        $code = $this->hex2bin($code);

        $td = mcrypt_module_open('rijndael-128', '', 'cbc', $this->iv);

        mcrypt_generic_init($td, $this->secretKey, $this->iv);
        $decrypted = mdecrypt_generic($td, $code);

        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);

        return utf8_encode(trim($decrypted));
    }

    private function hex2bin($hexdata)
    {
        $bindata = '';

        for ($i = 0; $i < strlen($hexdata); $i += 2) {
            $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
        }

        return $bindata;
    }

}

?>