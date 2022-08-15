<?php

class SMSSender_AddContacts
{
    public function __construct($APIKey, $SecretKey)
    {
        $this->APIKey = $APIKey;
        $this->SecretKey = $SecretKey;
    }

    public function AddContacts($ContactsData)
    {

        $token = $this->GetToken($this->APIKey, $this->SecretKey);
        if ($token != false) {
            $url = $this->AddContactsUrl();
            $AddContacts = $this->execute($ContactsData, $url, $token);
            $object = json_decode($AddContacts);
            if (is_object($object)) {
                $result = $object;
            } else {
                $result = 'Error Getting Object.';
            }

        } else {
            $result = 'Error Getting Token Key.';
        }
        return $result;
    }

    private function GetToken()
    {
        $postData = array(
            'UserApiKey' => $this->APIKey,
            'SecretKey' => $this->SecretKey
        );
        $postString = json_encode($postData);
        $ch = curl_init($this->getApiTokenUrl());
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
        ));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, count($postString));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);

        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result);

        if (is_object($response)) {
            if ($response->IsSuccessful == true) {
                @$resp = $response->TokenKey;
            } else {
                $resp = false;
            }
        }

        return $resp;
    }

    protected function getApiTokenUrl()
    {
        return "https://api.sms.ir/users/v1/Token/GetToken";
    }

    protected function AddContactsUrl()
    {
        return "https://api.sms.ir/users/v1/Contacts/AddContacts";
    }

    private function execute($postData, $url, $token)
    {

        $postString = json_encode($postData);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'x-sms-ir-secure-token: ' . $token
        ));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, count($postString));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);

        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}

?>