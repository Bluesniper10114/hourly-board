<?php
namespace ServiceProviders;

use Core\ServiceProviders\ServiceProvider;
use DAL\UsersDAL;

/**
 * A service which transforms a token into a $profileId and $role
 */
class AuthenticationServiceProvider extends ServiceProvider
{
    /**
     * Logged in profile id
     *
     * @var int|null
     */
    protected $profileId;

    /**
     * Token for logged in user
     *
     * @var string|null
     */
    protected $token;

    /**
     * Role of the logged in user
     *
     * @var int|null
     */
    protected $role;

    /**
     * Gets the currently logged in profile id
     *
     * @return int|null
     */
    public function getProfileId()
    {
        return $this->profileId;
    }

    /**
     * Gets the token for the currently logged in user
     *
     * @return string|null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Sets the token for the currently logged in user
     *
     * @param string $token
     * @return void
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * Gets the role of the currently logged in user
     *
     * @return int|null
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Loads all user information based on token
     *
     * @return void
     */
    public function loadFromToken()
    {
        $dal = new UsersDAL();
        if (empty($this->token)) {
            return;
        }
        $data = $dal->getProfileFromToken($this->token);
        if (empty($data)) {
            return;
        }
        $this->profileId = $data["ProfileId"];
        $this->role = $data["Role"];
    }

    /**
     * Checks if the current profileId and the token are still valid
     *
     * @return boolean True if authenticated
     */
    public function isUserAuthenticated()
    {
        $dal = new UsersDAL();
        if (empty($this->token)) {
            return false;
        }
        $result = $dal->isTokenValid($this->token);
        $dal->close();
        return $result;
    }

    /**
     * Reads the Http Header information and extracts the token based on the authorization method
     * @return string|null Token
     */
    public function extractTokenFromHttpHeader()
    {
        $token = getHttpHeaderInfo("Authorization");
        $this->token = $token;
        if (!is_null($token)) {
            $this->token = str_replace("Bearer ", "", $token);
        }
        return $this->token;
    }

    /**
     * Gets the global session variables provider
     *
     * @return \ServiceProviders\SessionVariablesProvider|null
     * @throws \Exception When storage provider does not exist or this service is deregistered
     */
    private function getSessionVariablesProvider()
    {
        if (is_null($this->application)) {
            throw new \Exception("Internal error: Service not registered");
        }
        /** @var \ServiceProviders\SessionVariablesProvider|null */
        $provider = $this->application->getServiceProvider("ServiceProviders\SessionVariablesProvider");
        return $provider;
    }

}
