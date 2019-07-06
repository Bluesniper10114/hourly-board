<?php
namespace DAL;

use PDO;
use DAL\Entities\Week;
use DAL\Entities\PlanningByDay;

/**
 * Planning DAL class
 **/
class PlanningDALv1 extends \Core\Data
{
    /**
     * Get Weeks header for daily planning
     *
     * @return Week[]
     **/
    public function getWeeks()
    {
        $sql = "
            set nocount on
            declare @DailyTargetID	int;		-- if is not NULL, @targetXML will return targets for corresponding line (no matter @Tags value)
            declare @weeksXML		XML;		-- xml data set for week/day screen aria

            set datefirst 1 --set first day of the week to Monday

            declare @lastMonday			datetime,
                    @firstOpenShiftID	int;
                    
            -- setting constant parameters
            set @lastMonday = DATEADD(day, -7, [global].NextMonday([global].[GetDate]()))
            select @firstOpenShiftID = MIN(ID) from dbo.vShiftLog where DataStart > [global].[GetDate]()


            declare @days table(ShiftLogID int, Data datetime)

            insert into @days(ShiftLogID, [Data])
            select ID, [Data]
            from dbo.ShiftLog
            where DATEDIFF(day, @lastMonday, [Data]) between 0 and 13
            order by [Data]

            select 
                DATENAME(WEEK, Data) [id], 
                convert(char(10), MIN(Data), 120) [start], 
                convert(char(10), MAX(Data), 120) [end]
            from @days
                group by DATENAME(WEEK, Data)
                order by [id]
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_CLASS, "DAL\Entities\Week");
        return $results;
    }

    /**
     * Gets Billboard
     *
     * @param int $profileId id of user attempting to load
     * @param string|null $tags search tags
     * @return string|null Xml containing the plan, or null if there is no plan
     **/
    public function getByDayPlan($profileId, $tags)
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
            select @weeksXML as weeksXml, @targetsXML as targetsXml
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
        if (isset($results["targetsXml"])) {
            return $results["targetsXml"];
        };
        return null;
    }

    /**
     * Save Daily Planning
     *
     * @param int|null $profileId
     * @param string $xml
     * @return string|null The error message
     */
    public function saveByDayPlan($profileId, $xml)
    {
        $error = null;
        try {
            $sql = "
            DECLARE	@errorMessage nvarchar(max);
            exec target.SaveTargetByDay @UserID = $profileId, @TargetsXML = '$xml', @errorMessage = @errorMessage OUTPUT
            select @errorMessage as error
            ";
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
     * Get routing for a partNumber 
     *
     * @param string $partNumber
     * @return int|null Number of minutes
     */
    public function getRouting($partNumber)
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
     * Loads loadByLineDate
     * @param int $profileId id of user
     * @param int $line id of line
     * @param string $date date
     * @return string|null
     */
    public function getPartNumberPlan($profileId, $line, $date)
    {
        $results = null;

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
     * Save Part Number planning
     *
     * @param int|null $profileId
     * @param string $xml
     * @return string|null The error message
     */
    public function savePartNumberPlan($profileId, $xml)
    {
        $error = null;
        try {
            $sql = "
            DECLARE	@errorMessage nvarchar(max);
            exec target.SaveTargetByPartNumber @UserID = $profileId, @TargetsXML = '$xml', @errorMessage = @errorMessage OUTPUT
            select @errorMessage as error
            ";

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
}