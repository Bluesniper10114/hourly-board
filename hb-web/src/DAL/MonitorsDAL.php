<?php
namespace DAL;

use PDO;
use DAL\Entities\Monitor;
/**
 * Users DAL class
 **/
class MonitorsDAL extends \Core\Data
{

    /**
     * get List Of Monitors
     *
     *  @return array
     **/
    public function getListOfMonitors()
    {
        $sqlFilter = '';
        $sql = "select id, location, description, ipAddress, locationID, locationName, lineId, lineName FROM layout.vActiveMonitors
			where 1 = 1
                $sqlFilter 
            order by locationID";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_CLASS, 'DAL\Entities\Monitor');
        return $result;
    }

    /**
     * get Monitor by id
     *
     * @param int $monitorId
     * @return Monitor
     **/
    public function getMonitor($monitorId = null)
    {
        $sql = "select id, location, description, ipAddress, locationID, locationName, lineId, lineName FROM layout.vActiveMonitors
			where id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'DAL\Entities\Monitor');
        $stmt->execute(['id' => $monitorId]);
        $result = $stmt->fetch();
        return $result;
    }

    /**
     * Get list of lines as array
     *
     * @return array
     */
    public function getLinesList()
    {
        $sql = "SELECT l.id, l.name FROM layout.Line l";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $return = [];
        foreach ($result as $row) {
            $return[$row['id']] = $row['name'];
        }
        return $return;
    }


    /**
     * Add monitor 
     *
     * @param Monitor $monitor The monitor object
     * @return string
     */
    public function addMonitor($monitor)
    {
        $sql = "declare @ErrorMessage nvarchar(max);
        exec layout.AddMonitor @UserID = $monitor->userId, @Location = '$monitor->location', 
        @Description = '$monitor->description', @IpAddress = '$monitor->ipAddress', 
        @LocationID = '$monitor->locationId', @LineID = $monitor->lineId, 
        @ErrorMessage = @ErrorMessage OUTPUT
        select @ErrorMessage as ErrorMessage
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $error = isset($result[0]["ErrorMessage"]) ? $result[0]["ErrorMessage"] : null;
        return $error;
    }


    /**
     * Edit monitor 
     *
     * @param Monitor $monitor The monitor object
     * @return string
     */
    public function editMonitor($monitor)
    {
        $sql = "declare @ErrorMessage nvarchar(max);
        exec layout.EditMonitor @MonitorID = $monitor->id, @UserID = $monitor->userId, 
        @Location = '$monitor->location', @Description = '$monitor->description', 
        @IpAddress = '$monitor->ipAddress', @LocationID = '$monitor->locationId', 
        @LineID = $monitor->lineId, @ErrorMessage = @ErrorMessage OUTPUT
        select @ErrorMessage as ErrorMessage
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $error = isset($result[0]["ErrorMessage"]) ? $result[0]["ErrorMessage"] : null;
        return $error;
    }


    /**
     * Delete monitor 
     *
     * @param int $userId The user profile id
     * @param int $monitorId The monitor id
     * @return string
     */
    public function deleteMonitor($userId, $monitorId)
    {
        $sql = "declare @ErrorMessage nvarchar(max);
        exec layout.DeleteMonitor @MonitorID = $monitorId, @UserID = $userId, @ErrorMessage = @ErrorMessage OUTPUT
        select @ErrorMessage as ErrorMessage
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $error = isset($result[0]["ErrorMessage"]) ? $result[0]["ErrorMessage"] : null;
        return $error;
    }

    /**
     * Get monitor id from IP address
     *
     * @param string $ipAddress The ip address
     * @return string|null
     */
    public function getMonitorIdFromIp($ipAddress)
    {
        $sql = "SELECT id
        FROM layout.monitor
        WHERE IPAddress = :ip";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['ip' => $ipAddress]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $id = isset($result[0]["id"]) ? $result[0]["id"] : null;
        return $id;
    }

}