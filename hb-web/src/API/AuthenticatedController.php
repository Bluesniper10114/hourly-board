<?php
namespace API;

use Common\Filters\AuthenticationFilter;
use Common\Filters\FeatureFilter;
use Core\Exceptions\HttpUnauthorizedAccessException;
use API\Filters\ApiAuthenticationFilter;

/**
*    Provides a mechanism to block any calls if the user calling this route is not authenticated
*/
abstract class AuthenticatedController extends \Common\Controller
{

    /**
     * Builds an authenticated controller by providing two filters:
     * 1. An authentication filter (checks token and profileId match)
     * 2. A feature filter (checks if the user role is allowed to use a feature)
     *
     * @inheritDoc
     */
    public function __construct($routeParams, $model = null)
    {
        parent::__construct($routeParams, $model);
        $authenticationFilter = new ApiAuthenticationFilter($this);
        $this->registerFilter($authenticationFilter);
    }

    /**
     * Gets the profileId of an authorized user (via token), or 
     * throws an HttpUnauthorizedAccessException if the token does not
     * belong to any user
     * @return int $profileId The profileId of the authorized user
     * @throws HttpUnauthorizedAccessException
     */
    protected function getProfileIdOrThrowException()
    {
        $profileId = $this->authenticationProvider->getProfileId();      

        if (is_null($profileId)) {
            throw new HttpUnauthorizedAccessException("Token does not belong to any user");
        };
        return $profileId;
    }
}