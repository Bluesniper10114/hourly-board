<?php
namespace Core;
interface IApplication
{
    /**
     * Gets the dictionary of service providers
     * @param string $key The key of the service provider
     * @return \Core\ServiceProviders\IServiceProvider|null
     */
    function getServiceProvider($key);

    /**
     * Sets a service provider
     *
     * @param string $key
     * @param \Core\ServiceProviders\IServiceProvider|null $value
     * @return void
     */
    function setServiceProvider($key, $value);
}