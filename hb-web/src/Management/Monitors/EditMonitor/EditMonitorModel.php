<?php

namespace Management\Monitors\EditMonitor;

use DAL\MonitorsDAL;

/**
 * Model for Edit Monitor
 */
class EditMonitorModel extends \Common\Model
{
    /** @var array|null */
    public $translationTitles;

    /** @var array|null */
    public $translationPlaceholders;

    /** @var array|null */
    public $messages;

    /** @var string|null */
    public $location;

    /** @var \DAL\Entities\Monitor */
    public $monitor;


    /** @var array|null */
    public $lines;

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
     * Load model
     *
     * @param int $monitorId
     * @return void
    */
    public function load($monitorId)
    {
        $this->monitor = $this->dal->getMonitor($monitorId);
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("EditMonitor");
        $this->title = $translations["Title"];
        $this->translationTitles = $translations["LabelsTitle"];
        $this->translationPlaceholders = $translations["LabelsPlaceholders"];
        $this->messages = $translations["Messages"];
        $this->lines = $this->dal->getLinesList();
        $this->location = 'TM';
    }
    /**
     * Edit monitor function
     *
     * @param \DAL\Entities\Monitor $monitor
     * @return boolean
     */
    public function editMonitor($monitor)
    {
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("EditMonitor");
        $error = $this->dal->editMonitor($monitor);
        if (!empty($error)) {
            $this->application->onError($error);
            return false;
        }
        $this->application->onSuccess($translations["Messages"]["Success"]);
        return true;
    }
}

?>