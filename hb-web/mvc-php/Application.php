<?php

namespace Core;

use PHPUnit\Framework\Constraint\Exception;
use Core\ServiceProviders;

abstract class Application implements IApplication
{
    /**
     * A dictionary of service providers
     *
     * @var array(\Core\ServiceProviders\IServiceProvider)
     */
    protected $serviceProviders = [];

    /**
     * @inheritDoc
     */
    public function getServiceProvider($key)
    {
        if (!isset($this->serviceProviders[$key]))
        {
            return null;
        }
        return $this->serviceProviders[$key];
    }

    /**
     * @inheritDoc
     */
    public function setServiceProvider($key, $value)
    {
        if (isset($this->serviceProviders[$key]))
        {
            throw new \Exception("There is already a service provider set for this key " . $key);
        }
        $this->serviceProviders[$key] = $value;
    }

    /**
     * Registers all the service providers
     * @return void
     */
    protected abstract function registerServiceProviders();

    /**
     * Initializes the service providers
     * @return void
     */
    protected abstract function initServiceProviders();
}