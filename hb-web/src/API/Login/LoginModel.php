<?php
namespace API\Login;

class LoginModel extends \Common\Login\LoginModel
{
    /** @var string[] $errorMessages */
    public $errorMessages = [];

    /**
     * @inheritDoc
     */
    public function login()
    {
        $this->token = null;
        if (empty($this->username)) {
            $this->errorMessages[] = 'Username cannot be empty';
            return false;
        }
        if (empty($this->password)) {
            $this->errorMessages[] = 'Password cannot be empty';
            return false;
        }
        $result = $this->dal->login($this->username, $this->password);
        if (empty($result)) {
            $this->errorMessages[] = "Internal error occured (empty result set)";
            return false;
        }
        $token = $result['token'];
        if (empty($token)) {
            $this->errorMessages[] = "Invalid login credentials";            
            $errorCode = isset($result['result']) ? intval($result['result']) : -50400;
            $this->errorMessages[] = "Error code ($errorCode)";            
            return false;
        }
        $this->setToken($token);
        $this->setPassword("");
        $this->setUsername("");
        return true;
    }
}