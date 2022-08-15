<?php
define('HOSTNAME', 'localhost');
define('USERNAME', 'cpres873_Aban');
define('PASSWORD', 'KimiaAndMohammad');
define('DATABASE', 'cpres873_KNTU_Database');

class UserValidator
{
    private $token;
    private $userID;
    private $sharedKey;
    private $restaurantID;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function isValidUser()
    {
        $connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');

        if ($connect) {
            if ($this->token == null)
                return false;

            $query = "SELECT StaffUser.id,Token.shared_key FROM StaffUser,Token WHERE StaffUser.id = Token.user_id AND Token.value = '$this->token' AND Token.deleted='0' AND StaffUser.deleted='0'";
            $res = mysqli_query($connect, $query);
            $counter = mysqli_num_rows($res);
            if ($counter == 0)
                return false;
            $fresult = mysqli_fetch_assoc($res);
            $this->userID = $fresult['id'];
            $this->sharedKey = $fresult['shared_key'];
            return true;
        } else
            return false;
    }

    public function getSharedKey()
    {
        return $this->sharedKey;
    }

    public function getUserID()
    {
        return $this->userID;
    }

    public function getRestaurantID()
    {
        $connect = mysqli_connect(HOSTNAME, USERNAME, PASSWORD, DATABASE) or die('Unable to Connect');

        if ($connect) {
            if ($this->token == null)
                return null;

            $query = "SELECT Restaurant.id FROM StaffUser,Token,Restaurant WHERE StaffUser.id = Token.user_id AND Token.value = '$this->token' AND Restaurant.id = StaffUser.id AND Token.deleted='0' AND StaffUser.deleted='0'";
            $res = mysqli_query($connect, $query);
            $counter = mysqli_num_rows($res);
            if ($counter == 0)
                return false;
            $fresult = mysqli_fetch_assoc($res);
            $this->restaurantID = $fresult['id'];
        }
        $connect->close();
        return $this->restaurantID;
    }
}