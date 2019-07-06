<?php
namespace Common\Filters;

use Core\Filters\ControllerFilter;


/**
*    Provides a mechanism to block any calls if the user calling this route is not authenticated
*/
class FeatureFilter extends ControllerFilter
{
    /**
     * @inheritDoc
     **/
    public function before()
    {
        $feature = $this->controller->getFeatureBeingExecuted();
        if (empty($feature)) {
            return false;
        }
        if (!$this->featureIsPermitted($feature)) {
            return false;
        }
        return true;
    }

    /**
     * Checks if current user has permissions for the feature currently being called
     * @param string $feature Feature to be checked
     * @return bool True when feature is permitted
     */
    protected function featureIsPermitted($feature)
    {
        if (empty($feature)) {
            return false;
        }

        /** @var \ServiceProviders\AuthenticationServiceProvider */
        $authenticationService = $this->getApplication()->getServiceProvider("ServiceProviders\AuthenticationServiceProvider");
        $role = $authenticationService->getRole();

        if (is_null($role)) {
            return false;
        }
        /** @var \ServiceProviders\FeatureServiceProvider */
        $featureService = $this->getApplication()->getServiceProvider("ServiceProviders\FeatureServiceProvider");
        if (!$featureService->anyFeaturePermitted([$feature], $role)) {
            $this->message = "The user does not have permission set: $feature";
            return false;
        }
        return true;
    }

    /**
     * @inheritDoc
     *
     * @return void
     */
    public function onFail()
    {
        //$this->controller->onError($this->message);
        echo $this->message;
        echo "Should redirect to $this->redirectUrl";
        //parent::onFail();
    }
}