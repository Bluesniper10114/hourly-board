<?php
namespace DAL;

use PDO;
use \DAL\Entities\Monitor;

/**
 * Users DAL class
 **/
class MonitorsDALv1 extends \Core\Data
{

    /**
     * Get monitor id from IP address
     *
     * @param string $ipAddress The ip address
     * @return Monitor|null
     */
    public function getMonitorFromIP($ipAddress)
    {
        $sql = "SELECT top 1 id, IPAddress ipAddress
            FROM layout.monitor
            WHERE IPAddress = :ip";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':ip', $ipAddress);
        $stmt->execute();
        $monitor = $stmt->fetchObject('\DAL\Entities\Monitor');
        if ($monitor instanceof Monitor) {
            $monitor->typify();
            return $monitor;
        }
        return null;
    }

    /**
     * Get List Of Monitors
     *
     * @return Monitor[]
     **/
    public function getMonitors()
    {
        $sqlFilter = '';
        $sql = "select id, location, description, ipAddress, locationID, locationName, lineId, lineName FROM layout.vActiveMonitors
			where 1 = 1
                $sqlFilter 
            order by locationID";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $monitors = $stmt->fetchAll(PDO::FETCH_CLASS, 'DAL\Entities\Monitor');
        foreach ($monitors as $monitor) {
            $monitor->typify();
        }
        return $monitors;
    }


}