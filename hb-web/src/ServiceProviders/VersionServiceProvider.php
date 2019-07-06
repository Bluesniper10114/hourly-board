<?php

namespace ServiceProviders;
use \Core\ServiceProviders\ServiceProvider;

/**
 * Contains information about versions of components used throughout the system
 **/
class VersionServiceProvider extends ServiceProvider
{
    /**
     * Returns a formatted message with the version of the database that is currently used
     * <Version> (<BuildNumber>)
     * Example: 1.0.6 (29)
     * @return string
     **/
    public function getDatabaseVersion()
    {
        $settingsData = new \DAL\SettingsDAL;
        $resultVersion = $settingsData->getDatabaseVersionInfo();

        $formattedResult = $resultVersion["Version"] . " (" . $resultVersion["id"] . ")";
        $settingsData->close();
        return $formattedResult;
    }

    /**
     * Get web software version line
     * @return string
     **/
    public function getSoftwareVersionLine()
    {
        return getSetting('AppVersionLine');
    }

    /**
     * Get copyright line
     * @return string
     **/
    public function getCopyrightLine()
    {
        return getSetting('CopyrightLine');
    }
}