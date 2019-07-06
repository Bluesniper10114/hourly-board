<?php

namespace Management\Monitors;

use DAL\MonitorsDAL;

/**
 * Model for Monitors
 */
class MonitorsModel extends \Common\Model
{
    /** @var array|null */
    public $monitors;

    /** @var array|null */
    public $messages;

    /** @var MonitorsDAL */
    public $dal;

    /**
     *Connects to the DAL
     */
    public function __construct()
    {
        parent::__construct();
        $this->dal = new MonitorsDAL();
    }

    /**
     * Delete monitor 
     *
     * @param int $userId The user profile id
     * @param int $monitorId The monitor id
     * @return boolean
     */
    public function deleteMonitor($userId, $monitorId)
    {
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("MonitorsList");
        $error = $this->dal->deleteMonitor($userId, $monitorId);
        if (!empty($error)) {
            echo $error;
            $this->application->onError($error);
            return false;
        }
        $this->application->onSuccess($translations["Messages"]["DeleteSuccess"]);
        return true;
    }

    /**
     * Get monitor id from IP address
     *
     * @param string $ipAddress The ip address
     * @return string|null
     */
    public function getMonitorIdFromIp($ipAddress)
    {
        $ipAddress = $this->dal->getMonitorIdFromIp($ipAddress);
        return $ipAddress;
    }

    /**
     * load model data
     *
     * @return void
     */
    public function load()
    {
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("MonitorsList");
        $this->title = $translations["Title"];
        $this->translationTitles = $translations["LabelsTitle"];
        $this->messages = $translations["Messages"];
        $this->monitors = $this->dal->getListOfMonitors();
    }

}

?>