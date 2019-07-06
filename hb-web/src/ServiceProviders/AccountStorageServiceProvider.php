<?php
namespace ServiceProviders;

use Core\ServiceProviders\ServiceProvider;
use DAL\UsersDAL;

/**
 * A service which stores and retrieves authentication information about the
 * user (profileId, token, role)
 */
class AccountStorageServiceProvider extends ServiceProvider
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
     * Sets the currently logged in profile Id
     *
     * @param int $profileId
     * @return void
     */
    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;
        $session = $this->getSessionVariablesProvider();
        if (!is_null($session)) {
            $session->storeSessionValue("profileId", $profileId);
        }
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
        $session = $this->getSessionVariablesProvider();
        if (!is_null($session)) {
            $session->storeSessionValue("token", $token);
        }
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
     * Sets the role of the currently logged in user
     *
     * @param int $role
     * @return void
     */
    public function setRole($role)
    {
        $this->role = $role;
        $session = $this->getSessionVariablesProvider();
        if (!is_null($session)) {
            $session->storeSessionValue("role", $role);
        }   
    }

    /**
     * Loads all authentication parameters if they are stored in a persistent object
     *
     * @return void
     */
    public function loadCurrentUser()
    {
        $session = $this->getSessionVariablesProvider();
        if (!is_null($session)) {
            $this->profileId = $session->getSessionValue("profileId");
            $this->token = $session->getSessionValue("token");
            $this->role = $session->getSessionValue("role");
        }
    }

    /**
     * Forgets all the data about the currently logged in user
     *
     * @return void
     */
    public function clearSession()
    {
        $session = $this->getSessionVariablesProvider();
        if (!is_null($session)) {
            $session->clearAll();
        }
        $this->profileId = null;
        $this->token = null;
        $this->role = null;
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
