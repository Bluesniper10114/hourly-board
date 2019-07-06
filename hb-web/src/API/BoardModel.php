<?php
namespace API;

use ServiceProviders\VersionServiceProvider;
use DAL\MonitorsDAL;
use DAL\MonitorsDALv1;
use Common\Helpers;
use API\Monitor;

/**
 * Model for breaks
 */
class BoardModel extends \Common\Model
{

    /**
     *Connects to the DAL and sets title and help
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Gets the footer of the public shopfloor part of the system
     * 
     * @return array
     */
    public function getFooter()
    {
        /** @var VersionServiceProvider|null */
        $versionService = $this->application->getServiceProvider("ServiceProviders\VersionServiceProvider");

        if (is_null($versionService)) {
            $errorMessage = [
                "errorMessage" => "No data available"
            ];
            return $errorMessage;
        }

        $data = [
            "softwareVersionLine" => $versionService->getSoftwareVersionLine(),
            "copyrightLine" => $versionService->getCopyrightLine(),
            "datababaseVersion" => $versionService->getDatabaseVersion()
        ];
        return $data;
    }

}