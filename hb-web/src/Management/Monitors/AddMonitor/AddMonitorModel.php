<?php

namespace Management\Monitors\AddMonitor;

use DAL\MonitorsDAL;

/**
 * Model for Add Monitor
 */
class AddMonitorModel extends \Common\Model
{
    /** @var array|null */
    public $translationTitles;

    /** @var array|null */
    public $translationPlaceholders;

    /** @var array|null */
    public $messages;

    /** @var string|null */
    public $location;

    /** @var array|null */
    public $lines;

    /** @var \DAL\Entities\Monitor */
    public $monitor;

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
     * @param \DAL\Entities\Monitor|null $monitor
     * @return void
     */
    public function load($monitor)
    {
        if (is_null($monitor)) {
            $this->monitor = new \DAL\Entities\Monitor;
        } else {
            $this->monitor = $monitor;
        }
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("AddMonitor");
        $this->title = $translations["Title"];
        $this->translationTitles = $translations["LabelsTitle"];
        $this->translationPlaceholders = $translations["LabelsPlaceholders"];
        $this->messages = $translations["Messages"];
        $this->lines = $this->dal->getLinesList();
        $this->location = 'TM';

    }
    /**
     * Add monitor function
     *
     * @param \DAL\Entities\Monitor $monitor
     * @return boolean
     */
    public function addMonitor($monitor)
    {
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("AddMonitor");
        $error = $this->dal->addMonitor($monitor);
        if (!empty($error)) {
            $this->application->onError($error);
            return false;
        }
        $this->application->onSuccess($translations["Messages"]["Success"]);
        return true;
    }
}

?>