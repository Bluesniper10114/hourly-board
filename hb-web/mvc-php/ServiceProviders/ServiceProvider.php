<?php
namespace Core\ServiceProviders;

use Core\ServiceProviders\IServiceProvider;


abstract class ServiceProvider implements IServiceProvider
{
    /**
     * The application
     *
     * @var \Core\IApplication|null
     */
    public $application;

    /**
     * @inheritDoc
     */
    public function register($application)
    {
        $this->application = $application;
		$this->onRegister($application);
    }

    /**
     * Does the registration process on the provider side
     *
     * @param \Core\IApplication $application
     * @return void
     */
    protected function onRegister($application)
    {
        $className = get_class($this);
        $application->setServiceProvider($className, $this);
    }

    /**
     * @inheritDoc
     */
    public function unRegister($application)
    {
        $this->onUnRegister($application);
        $this->application = null;
    }

    /**
     * Does the deregistration process on the provider side
     *
     * @param \Core\IApplication $application
     * @return void
     */
    protected function onUnRegister($application)
    {
        $className = get_class($this);
        $application->setServiceProvider($className, null);
    }
    
    /**
     * Checks if the service is registered in the application
     * @return void
     * @throws \Exception If service is not registered
     */
    protected function validate()
    {
        if (is_null($this->application)) {
            throw new \Exception("Service " . get_class() . " is not registered");
        }
    }
}