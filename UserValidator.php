<?php

class UserValidator
{
    private $userToken;
    private $userID;

    public function __construct($token)
    {
        
        $this->userToken=$token;
    }

    public function isValidUser() {
        $connect = mysqli_connect(HOSTNAME,USERNAME,PASSWORD,DATABASE) or die('Unable to Connect');

        if($connect){
            if($this->userToken==null)
                return false;

            $query="SELECT NormalUser.id FROM NormalUser,Token WHERE NormalUser.id = Token.user_id AND Token.value = '$this->userToken' AND Token.deleted='0' AND NormalUser.deleted='0'";
            $res = mysqli_query($connect,$query);
            $counter=mysqli_num_rows($res);
            if($counter==0)
                return false;
            $fresult=mysqli_fetch_assoc($res);
            $this->userID=$fresult['id'];
            return true;
        }else
            return false;
    }
    public function getUserID()
    {
        return $this->userID;
    }
}