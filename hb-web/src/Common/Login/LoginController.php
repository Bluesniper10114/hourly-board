<?php
namespace Common\Login;
use Core\Persistence\PersistentObject;
/**
 * Manages the login for the app or the management tool
 **/
abstract class LoginController extends \Common\Controller
{
    /** @var LoginModel The model handling business logic for this controller */
    protected $model;

    /**
     * Does a basic check if username and password are set
     *
     * Override to expand the condition
     * @return bool Returns true if the login button should be enabled
     **/
    protected function isLoginEnabled()
    {
        return $this->model->canLogin();
    }

    /**
     * Implements the login action
     * @return bool Returns true if logged in
     **/
    public abstract function loginProcessAction();

    /**
     * Logout
     *
     * Performs the logout on the model. Clears the session details
     *
     * @return void
     **/
    protected function logout()
    {
        $this->model->logout();

        /** @var \ServiceProviders\AccountStorageServiceProvider $accountStorageServiceProvider */
        $accountStorageServiceProvider = $this->application->getServiceProvider("ServiceProviders\AccountStorageServiceProvider");        
        $accountStorageServiceProvider->clearSession();
    }

    /**
     *
     * Saves the user details after login
     * @param int $profileId
     * @param string $token
     * @param int $role
     *
     * @return void
     */
    protected function save($profileId, $token, $role)
    {
        /** @var \ServiceProviders\AccountStorageServiceProvider $accountStorageServiceProvider */
        $accountStorageServiceProvider = $this->application->getServiceProvider("ServiceProviders\AccountStorageServiceProvider");        
        $accountStorageServiceProvider->setProfileId($profileId);
        $accountStorageServiceProvider->setToken($token);
        $accountStorageServiceProvider->setRole($role);
    }
}