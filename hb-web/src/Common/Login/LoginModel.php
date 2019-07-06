<?php
namespace Common\Login;
use DAL\UsersDAL;
/**
*     Login data for all login types
 */
abstract class LoginModel extends \Common\Model
{
    protected $username;
    protected $password;
    protected $token;
    protected $profileId;

    /** @var UsersDAL */
    public $dal;


    /**
     * Constructs the model
     */
    public function __construct()
    {
        parent::__construct();
        $this->dal = new UsersDAL();
    }

    /**
     * Sets the username used as username
     * @param string $username User badge username
     **/
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Sets the password
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Gets the token
     * @return string|null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Sets the token, as an alternative to username and password
     * It forgets any username and password
     * @param string|null $token A user token
     **/
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * Gets the profileId
     * @return int|null
     */
    public function getProfileId()
    {
        return $this->profileId;
    }

    /**
     * Sets the profileId
     *
     * @param int $profileId
     * @return void
     */
    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;
    }

    /**
     * Perform the actual login
     * @return bool True if successful
     */
    public abstract function login();

    /**
     * Checks whether username and password have been set
     *
     * @return boolean
     */
    public function canLogin()
    {
        return (!empty($this->username) && !empty($this->password));
    }

    /**
     * Checks if the given token corresponds to the profileId of an authenticated user
     * (it might have expired)
     *
     * $this->user must have a token and a profile id
     * @return bool Return true if the token belongs to the profile
    */
    public function isTokenValidForProfile()
    {
        if (empty($this->profileId)){
            return false;
        }
        if (empty($this->token)){
            return false;
        }
        $tokenValid = $this->dal->doesTokenBelongToProfile($this->token, $this->profileId);
        $this->dal->close();
        return $tokenValid;
    }

    /**
     * Logs out and clears stored login data
     * @return bool True if logged out
     * */
    public function logout()
    {
        $result = false;
        if (empty($this->token)){
            return false;
        }
        $result = $this->dal->logout($this->token);
        $this->dal->close();
        return $result;
    }

    /**
     * Checks if the given token corresponds to the profileId of an authenticated user
     * (it might have expired)
     *
     * $this->user must have a token and a profile id
     * @return bool Return true if the token belongs to the profile
    */
    public function tokenBelongsToUsername()
    {
        if (empty($this->token)){
            return false;
        }
        if (empty($this->username)){
            return false;
        }
        $tokenValid = $this->dal->tokenBelongsToUsername($this->token, $this->username);
        $this->dal->close();
        return $tokenValid;
    }

}