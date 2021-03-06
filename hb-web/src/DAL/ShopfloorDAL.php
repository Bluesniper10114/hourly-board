<?php
namespace DAL;

use PDO;
use DAL\Entities\Hour;
use DAL\Entities\BillboardHeader;

/**
 * Users DAL class
 **/
class ShopfloorDAL extends \Core\Data
{
    /**
     * Gets Billboard in XML format
     *
     * 
     * @param int $monitorId id of monitor attempting the load
     * @return string|null
     **/
    public function getBillboardXml($monitorId)
    {
        $sql = "
            declare @xml xml;
            exec dbo.GetBillboard @MonitorID = $monitorId, @xml = @xml OUTPUT
            select @xml as xmlResult
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $xml = isset($result[0]["xmlResult"]) ? $result[0]["xmlResult"] : null;
        return $xml;
    }

    /**
     * Gets Billboard without XML
     *
     * @param int $monitorId id of monitor attempting the load
     * @return object[]|null
     **/
    public function getBillboardNonXml($monitorId)
    {
        $sql = "
            exec dbo.GetBillboardV1 @MonitorID = $monitorId
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * Gets billboard header information
     * 
     * @param int $monitorId Monitor id
     * @return BillboardHeader The header information
     */
    public function getBillboardHeaderV1($monitorId)
    {
        $sql = "
            declare @monitorId int = $monitorId
            declare @targetDailyID	int,
                @shiftLogID	int,
                @lineID	smallint,	
                @errorNumber	int = 16,
                @errorMessage	nvarchar(max),
                @procedureLogID	bigint


            -- get necessary informations for resulting xml dataset
            select top 1
                @targetDailyID = d.ID,
                @shiftLogID = slso.ShiftLogID,
                @lineID = m.LineID
            from layout.Monitor m
                cross join dbo.ShiftLog sl
                inner join dbo.ShiftLogSignOff slso on m.LineID = slso.LineID and sl.ID = slso.ShiftLogID
                left join [target].Daily d on m.LineID = d.LineID and slso.ShiftLogID = d.ShiftLogID
            where m.ID = @MonitorID
                and d.Billboard = 1
                and slso.SignedOffOperatorID is NULL
            order by sl.DataStart

            -- header info
            select
                CONVERT(nvarchar(10), sl.[Data], 20) 'date', sl.ShiftName 'shift',
                l.[Name] 'lineName', sl.LocationName 'locationName',
                0 'maxHourProduction',
                0 'deliveryTime',
                slso.ID 'shiftLogSignOffId'
            from layout.Line l
                cross join dbo.vShiftLog sl
                inner join dbo.ShiftLogSignOff slso on l.ID = slso.LineID and sl.ID = slso.ShiftLogID
            where l.ID = @lineID
                and sl.ID = @shiftLogID";
            
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $header = $stmt->fetchObject("BillboardHeader");
        if (isset($header)) {
            $header->typify();
        }
        return $header;
    }

    /**
     * Gets the hourly intervals to be displayed on the billboard
     * 
     * @param int $monitorId The monitor id
     * @return Hour[] An array of 8 hourly intevals
     */
    public function getBillboardDataV1($monitorId)
    {
        $sql = "
            set nocount on
            declare @monitorId int = $monitorId

            declare @targetDailyID	int,
                    @shiftLogID	int,
                    @lineID	smallint,	
                    @errorNumber	int = 16,
                    @errorMessage	nvarchar(max),
                    @procedureLogID	bigint

            declare @billboardLog table(
                    TargetHourlyID	int,
                    HourStart	datetime,
                    HourEnd	datetime,
                    [Hour]	tinyint,
                    [HourInterval]	nchar(11),
                    [Target]	smallint,
                    CumulativeTarget	smallint,
                    ActualAchieved	smallint,
                    CumulativeAchieved	smallint,
                    Defects	smallint,
                    Downtime	int,
                    Comment	nvarchar(100),
                    Escalated	nvarchar(50),
                    SignedOffOperatorID	int,
                    SignedOffOperatorBarcode nvarchar(50))

                -- get necessary informations for resulting xml dataset
                select top 1
                    @targetDailyID = d.ID,
                    @shiftLogID = slso.ShiftLogID,
                    @lineID = m.LineID
                from layout.Monitor m
                    cross join dbo.ShiftLog sl
                    inner join dbo.ShiftLogSignOff slso on m.LineID = slso.LineID and sl.ID = slso.ShiftLogID
                    left join [target].Daily d on m.LineID = d.LineID and slso.ShiftLogID = d.ShiftLogID
                where m.ID = @MonitorID
                    and d.Billboard = 1
                    and slso.SignedOffOperatorID is NULL
                order by sl.DataStart

                -- if target is missing there is no plan nor actuals values, all values are 0
                if @targetDailyID is NULL
                    exec [target].[AddTargetAutomatic] @LineID = @lineID, @ShiftLogID = @shiftLogID, @DailyID = @targetDailyID OUTPUT

                insert into @billboardLog(TargetHourlyID, HourStart, HourEnd, [Hour], [HourInterval], 
                    [Target], CumulativeTarget, ActualAchieved, CumulativeAchieved,
                    Defects, Downtime, Comment, Escalated, SignedOffOperatorID, SignedOffOperatorBarcode)
                select TargetHourlyID, HourStart, HourEnd, [Hour], [HourInterval], 
                    [Target], CumulativeTarget, ActualAchieved, CumulativeAchieved,
                    Defects, Downtime, Comment, Escalated, SignedOffOperatorID, SignedOffOperatorBarcode
                from vBillboardLog
                where TargetDailyID = @targetDailyID

                -- table info
                select	bl.TargetHourlyID 'id',
                    case when bl.SignedOffOperatorID is not NULL then 1 else 0 end 'closed',
                    case when fo.[Hour] is not NULL then 1 else 0 end 'firstOpen',
                    bl.HourInterval 'hourInterval', 
                    bl.[Target] 'target', 
                    bl.CumulativeTarget 'cumulativeTarget',
                    bl.ActualAchieved 'achieved', 
                    bl.CumulativeAchieved 'cumulativeAchieved', 
                    bl.Defects 'defects',
                    bl.Downtime 'downtime', 
                    bl.Comment 'comments', 
                    bl.Escalated 'escalations', 
                    bl.SignedOffOperatorBarcode 'signoff'
                from @billboardLog bl
                    left join (
                        select MIN([Hour]) [Hour]
                        from @billboardLog
                        where SignedOffOperatorID is NULL
                    ) fo on bl.[Hour] = fo.[Hour]
                order by bl.[Hour]
            ";
            
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $hours = $stmt->fetchAll(PDO::FETCH_CLASS, "DAL\Entities\Hour");
        if (!empty($hours)) {
            foreach ($hours as $hour) {
                $hour->typify();
            }
        }
        return $hours;
    }


    /**
     * Gets Historical Billboard report
     *
     * 
     * @param string $date selected date
     * @param string $line selected line
     * @param string $shiftType selected shiftType
     * @return array
     **/
    public function getHistoricalBillboard($date, $line, $shiftType)
    {
        $sql = "
            declare @errorMessage nvarchar(max), @xml xml;
            exec report.HistoricalShift @LineID = '$line',  @Date = '$date',  
            @ShiftType = '$shiftType', @xml = @xml OUTPUT, @ErrorMessage = @ErrorMessage OUTPUT
            select @xml as xml, @ErrorMessage as error
        ";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $exc) {
            $message = $exc->getMessage();
            return ['error' => $message, 'xml' => null];
        }


        return $result[0];
    }

    /**
     * Gets Downtime Minutes
     *
     * 
     * @param int $targetHourlyID id of hour attempting the load
     * @return string|null
     **/
    public function getDowntimeMinutes($targetHourlyID)
    {
        $sql = "
            declare @xml xml;
            exec dbo.GetDowntime @TargetHourlyID = $targetHourlyID, @xml = @xml OUTPUT
            select @xml as xmlResult
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $xml = isset($result[0]["xmlResult"]) ? $result[0]["xmlResult"] : null;
        return $xml;
    }


    /**
     * Save Downtime
     *
     * @param int $targetHourlyID
     * @param string $xml
     * @return string|null
     */
    public function saveDowntime($targetHourlyID, $xml)
    {
        $error = null;
        try {
            $sql = "
            DECLARE	@errorMessage nvarchar(max);
            exec dbo.SaveDowntime @TargetHourlyID = $targetHourlyID, @XML = '$xml', @errorMessage = @errorMessage OUTPUT
            select @errorMessage as error
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $sqlResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $error = $sqlResult[0]["error"];
        } catch (\PDOException $ex) {
            $error = $ex->getMessage();
        }
        return $error;
    }

    /**
     * Gets Downtime Dictionary
     *
     * 
     * @return array
     **/
    public function getDowntimeDictionary()
    {
        $sql = "select id, text from dbo.DowntimeDictionary where deleted=0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultList = [];
        foreach ($result as $res) {
            $resultList[$res['id']] = $res['text'];
        }
        return $resultList;
    }

    /**
     * Gets Comments List
     *
     * 
     * @return array
     **/
    public function getCommentsList()
    {
        $sql = "select id, text from dbo.CommentsDictionary where deleted=0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultList = [];
        foreach ($result as $res) {
            $resultList[$res['id']] = $res['text'];
        }
        return $resultList;
    }

    /**
     * Gets Escalated List
     *
     * 
     * @return array
     **/
    public function getEscalatedList()
    {
        $sql = "select id, text from dbo.EscalatedDictionary where deleted=0";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultList = [];
        foreach ($result as $res) {
            $resultList[$res['id']] = $res['text'];
        }
        return $resultList;
    }

    /**
     * Gets Lines for report
     *
     * @return array
     */
    public function getLinesList()
    {
        $sql = "select LineID, LineName from report.vLine order by Deleted, LineName";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultList = [];
        foreach ($result as $res) {
            $resultList[$res['LineID']] = $res['LineName'];
        }
        return $resultList;
    }

    /**
     * Gets Shift Type List
     *
     * @return array
     */
    public function getShiftTypeList()
    {
        $sql = "select ShiftType, ShiftTypeName from report.vShiftType order by ShiftType";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $resultList = [];
        foreach ($result as $res) {
            $resultList[$res['ShiftType']] = $res['ShiftTypeName'];
        }
        return $resultList;
    }
    /**
     * Save Escalated
     *
     * @param int $hourlyId
     * @param string $text
     * @return string|null
     */
    public function saveEscalated($hourlyId, $text)
    {
        $error = null;
        try {
            $sql = "
            DECLARE	@errorMessage nvarchar(max);
            exec dbo.BillboardSaveEscalated @TargetHourlyID = $hourlyId, @Escalated = '$text', @errorMessage = @errorMessage OUTPUT
            select @errorMessage as error
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $sqlResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $error = $sqlResult[0]["error"];
        } catch (\PDOException $ex) {
            $error = $ex->getMessage();
        }
        return $error;
    }



    /**
     * Save Comments
     *
     * @param int $hourlyId
     * @param string $text
     * @return string|null
     */
    public function saveComments($hourlyId, $text)
    {
        $error = null;
        try {
            $sql = "
                DECLARE	@errorMessage nvarchar(max);
                exec dbo.BillboardSaveComment @TargetHourlyID = $hourlyId, @Comment = '$text', @errorMessage = @errorMessage OUTPUT;
                select @errorMessage as error
                ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $sqlResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $error = $sqlResult[0]["error"];
        } catch (\PDOException $ex) {
            $error = $ex->getMessage();
        }
        return $error;
    }

    /**
     * Sign Off Hour
     *
     * @param int $hourlyId
     * @param string $operatorBarcode
     * @return string|null
     */
    public function signOffHour($hourlyId, $operatorBarcode)
    {
        $error = null;
        try {
            $sql = "
                DECLARE	@errorMessage nvarchar(max);
                exec dbo.BillboardHourSignOff @TargetHourlyID = $hourlyId, @OperatorBarcode = '$operatorBarcode', @errorMessage = @errorMessage OUTPUT;
                select @errorMessage as error
                ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $sqlResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $error = $sqlResult[0]["error"];
        } catch (\PDOException $ex) {
            $error = $ex->getMessage();
        }
        return $error;
    }

    /**
     * Sign Off Shift
     *
     * @param int $shiftLogSignOffID
     * @param string $operatorBarcode
     * @return string|null
     */
    public function signOffShift($shiftLogSignOffID, $operatorBarcode)
    {
        $error = null;
        try {
            $sql = "
                DECLARE	@errorMessage nvarchar(max);
                exec dbo.BillboardShiftSignOff @shiftLogSignOffID = $shiftLogSignOffID, 
                @OperatorBarcode = '$operatorBarcode', @errorMessage = @errorMessage OUTPUT;
                select @errorMessage as error
                ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $sqlResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $error = $sqlResult[0]["error"];
        } catch (\PDOException $ex) {
            $error = $ex->getMessage();
        }
        return $error;
    }
}