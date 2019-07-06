<?php
namespace Common\Filters;

use Core\Filters\ControllerFilter;


/**
*    Provides a mechanism to block any calls if the user calling this route is not authenticated
*/
class AuthenticationFilter extends ControllerFilter
{
    /**
     * @inheritDoc
     **/
    public function before()
    {
        if (!$this->userIsAuthenticated()) {
            return false;
        }
        return true;
    }

    /**
     * Checks if current user is authenticated 
     * @return bool True when user is authenticated
     */
    protected function userIsAuthenticated()
    {
        /** @var \ServiceProviders\AuthenticationServiceProvider */
        $authenticationService = $this->getApplication()->getServiceProvider("ServiceProviders\AuthenticationServiceProvider");
        if (!$authenticationService->isUserAuthenticated()) {
            $this->message = 'User is not authenticated or the session has expired';
            return false;
        }
        return true;
    }

}