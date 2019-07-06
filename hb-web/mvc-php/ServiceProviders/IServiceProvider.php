<?php
namespace Core\ServiceProviders;

interface IServiceProvider
{
    /**
     * Register the service with the application
     *
     * @param \Core\IApplication $application
     * @return void
     */
    function register($application);

    /**
     * Unregister the service from the app
     *
     * @param \Core\IApplication $application
     * @return void
     */
    function unRegister($application);
}