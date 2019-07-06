<?php
namespace API\Filters;

use Core\Filters\ControllerFilter;
use Core\Exceptions\HttpUnauthorizedAccessException;    


/**
 * Provides a mechanism to block any calls if the user calling this route is not authenticated
 **/
class ApiAuthenticationFilter extends ControllerFilter
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
            throw new HttpUnauthorizedAccessException("Invalid token");
        }
        return true;
    }

}