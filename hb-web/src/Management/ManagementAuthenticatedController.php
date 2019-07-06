<?php
namespace Management;

use Common\Filters\AuthenticationFilter;
use Common\Filters\FeatureFilter;

/**
*    Provides a mechanism to block any calls if the user calling this route is not authenticated
*/
abstract class ManagementAuthenticatedController extends \Management\ManagementController
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
        $authenticationFilter = new AuthenticationFilter($this);
        $authenticationFilter->redirectUrl = SITE_URL . "management/login";
        $this->registerFilter($authenticationFilter);

        $featureFilter = new FeatureFilter($this);
        $featureFilter->redirectUrl = SITE_URL . "management/login";
        $this->registerFilter($featureFilter);

    }
}