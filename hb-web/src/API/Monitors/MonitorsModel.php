<?php
namespace API\Monitors;

use DAL\MonitorsDALv1;
use Common\Helpers;
use DAL\Entities\Monitor;

/**
 * Model for breaks
 */
class MonitorsModel extends \Common\Model
{

    /** @var MonitorsDALv1 */
    public $dal;

    /**
     *Connects to the DAL and sets title and help
     */
    public function __construct()
    {
        parent::__construct();
        $this->dal = new MonitorsDALv1();
    }

    /**
     * Get monitor id from IP address
     *
     * @param string $ipAddress The ip address
     * @return Monitor|null
     */
    public function getMonitorFromIp($ipAddress)
    {
        return $this->dal->getMonitorFromIP($ipAddress);
    }

    /**
     * Gets a list of monitors.
     * 
     * @return Monitor[]
     */
    public function getMonitors()
    {
        return $this->dal->getMonitors();
    }
}