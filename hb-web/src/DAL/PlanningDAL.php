<?php
namespace DAL;

use PDO;
/**
 * Users DAL class
 **/
class PlanningDAL extends \Core\Data
{
    /**
     * Gets Billboard
     *
     * @param int $profileId id of user attempting to load
     * @param string|null $tags search tags
     * @return array
     **/
    public function getByDayData($profileId, $tags)
    {
        $results = [];
        $tags = isset($tags) ? "N'$tags'" : "NULL";
        $sql = "
        DECLARE @weeksXML xml, @targetsXML xml;
            exec target.GetTargetByDay @UserID = $profileId,
            @DailyTargetID = NULL,
            @Tags = $tags,
            @weeksXML = @weeksXML OUTPUT,
            @targetsXML = @targetsXML OUTPUT;
            select @weeksXML as weeksXML, @targetsXML as targetsXML
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        if ($stmt->columnCount() === 0) {
            while ($stmt->columnCount() === 0 && $stmt->nextRowset()) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (isset($result[0])) {
                    $results = $result[0];
                }
            }
        } else {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $results = $results[0];
        }

        return $results;
    }


    /**
     * Save Daily Planning
     *
     * @param int|null $profileId
     * @param string $xml
     * @return string|null
     */
    public function saveByDay($profileId, $xml)
    {
        $error = null;
        try {
            $sql = "
            DECLARE	@errorMessage nvarchar(max);
            exec target.SaveTargetByDay @UserID = $profileId, @TargetsXML = '$xml', @errorMessage = @errorMessage OUTPUT
            select @errorMessage as error
            ";
           // echo $sql;
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            if ($stmt->columnCount() === 0) {
                while ($stmt->columnCount() === 0 && $stmt->nextRowset()) {
                    $sqlResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $error = $sqlResult[0]["error"];
                }
            } else {
                $sqlResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $error = $sqlResult[0]["error"];
            }
        } catch (\PDOException $ex) {
            $error = $ex->getMessage();
        }
        return $error;
    }

    /**
     * Loads loadByLineDate
     * @param int $profileId id of user
     * @param int $line id of line
     * @param string $date date
     * @return string
     */
    public function getPartNumberDataByLineDate($profileId, $line, $date)
    {
        $results = '';

        $sql = "DECLARE @targetsXML xml;
            exec target.GetTargetByPartNumber @UserID = $profileId, @DailyTargetID = NULL, 
            @LineID = $line, @Date = '$date', @targetsXML = @targetsXML OUTPUT;
            select @targetsXML as targetsXML
            ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        if ($stmt->columnCount() === 0) {
            while ($stmt->columnCount() === 0 && $stmt->nextRowset()) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (isset($result[0])) {
                    $results = $result[0];
                }
            }
        } else {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $results = $results[0]['targetsXML'];
        }

        return $results;
    }
    /**
     * Gets Daily Planning
     *
     * @param int $profileId id of user attempting to load
     * @param int $dailyTargetID
     * @return string
     **/
    public function getByPartNumberData($profileId, $dailyTargetID)
    {
        $results = '';

        $sql = "DECLARE @targetsXML xml;
            exec target.GetTargetByPartNumber @UserID = $profileId, @DailyTargetID = $dailyTargetID, 
            @LineID = NULL, @Date = NULL, @targetsXML = @targetsXML OUTPUT;
            select @targetsXML as targetsXML
            ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        if ($stmt->columnCount() === 0) {
            while ($stmt->columnCount() === 0 && $stmt->nextRowset()) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if (isset($result[0])) {
                    $results = $result[0];
                }
            }
        } else {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $results = $results[0]['targetsXML'];
        }

        return $results;
    }

    /**
     * Get Target By PartNumber
     *
     * @param int $partNumber
     * @throws Exception
     * @return array|null
     */
    public function getTargetByPartNumber($partNumber)
    {

        $error = null;
        $sqlResult = null;
        try {
            $sql = "
            SELECT partNumber, routing
            FROM layout.PartNumber where PartNumber= $partNumber ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            if ($stmt->columnCount() === 0) {
                while ($stmt->columnCount() === 0 && $stmt->nextRowset()) {
                    $sqlResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
            } else {
                $sqlResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (\PDOException $ex) {
            $error = $ex->getMessage();
        }
        if (!empty($error)) {
            throw new \Exception($error);
        }
        return !empty($sqlResult) && count($sqlResult) > 0 ? $sqlResult[0]['routing'] : null;
    }

    /**
     * Get list of lines
     *
     * @throws Exception
     * @return array
     */
    public function getLinesList()
    {

        $error = null;
        $sqlResult = null;
        $list = [];
        try {
            $sql = "
            SELECT id, name FROM layout.line where Deleted = 0";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $sqlResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($sqlResult as $result) {
                $list[$result['id'] . ""] = $result['name'];
            }
        } catch (\PDOException $ex) {
            $error = $ex->getMessage();
        }
        if (!empty($error)) {
            throw new \Exception($error);
        }
        return $list;
    }
    
    /**
     * Save Daily Planning
     *
     * @param int|null $profileId
     * @param string $xml
     * @return string|null
     */
    public function savePlanningByPartNumber($profileId, $xml)
    {
        $error = null;
        try {
            $sql = "
            DECLARE	@errorMessage nvarchar(max);
            exec target.SaveTargetByPartNumber @UserID = $profileId, @TargetsXML = '$xml', @errorMessage = @errorMessage OUTPUT
            select @errorMessage as error
            ";
           // echo $sql;
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            if ($stmt->columnCount() === 0) {
                while ($stmt->columnCount() === 0 && $stmt->nextRowset()) {
                    $sqlResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $error = $sqlResult[0]["error"];
                }
            } else {
                $sqlResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $error = $sqlResult[0]["error"];
            }
        } catch (\PDOException $ex) {
            $error = $ex->getMessage();
        }
        return $error;
    }

    /**
     * Get line name by id
     *
     * @param int|null $lineId
     * @return string|null
     */
    public function getLineName($lineId)
    {
        $list = $this->getLinesList();
        foreach ($list as $id => $name) {
            if (intval($id) === intval($lineId)) {
                return $name;
            }
        }
        return null;
    }

}