<?php
namespace DAL;
use \PDO;
use DAL\Entities\PlanningDataset;
use DAL\Entities\PlanningDatasetXmlConverter;

/**
 * Gets information about machines, cells and lines, pplanning datasets
 */
class LayoutDALv1 extends \Core\Data
{

    /**
     * Get a simple list of available lines
     *
     * @throws Exception
     * @return array
     */
    public function getLinesList()
    {
        $list = [];
        $sql = "
        SELECT id, name as text FROM layout.line where Deleted = 0";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $list;
    }

    /**
     * Get list of tags for all available lines
     *
     * @throws Exception
     * @return array
     */
    public function getTagsList()
    {
        $list = [];
        $sql = "
        SELECT tag FROM layout.lineTag";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $list = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $list;
    }

    /**
     * Gets a full list of lines, including cells and machines in Xml format
     * @param int $profileId Profile id
     * @return string|null An Xml of lines
     */
    public function getLinesCellsAndMachines($profileId)
    {
        try {
            $sql = "
            declare @xml xml;
            declare @userId int = $profileId;
            
            exec [layout].[GetProductionLines] @userId = @userId, @xml = @xml output
            
            select @xml as linesXml";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();            
            $sqlResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (isset($sqlResult[0]["linesXml"])) {
                return $sqlResult[0]["linesXml"];
            }
            return null;
        } catch (\PDOException $ex) {
            return null;
        }
    }

    /**
     * Activates a planning dataset on billboard
     *
     * @param int $profileId
     * @param int $dailyTargetID
     * @return string|null
     */
    public function activateDatasetOnBillboard($profileId, $dailyTargetID)
    {
        $error = null;
        try {
            $sql = "
            DECLARE	@errorMessage nvarchar(max);
            exec target.SetOnBillboard @UserID = $profileId, @DailyTargetID = $dailyTargetID, @errorMessage = @errorMessage OUTPUT
            select @errorMessage as error
            ";
            //echo $sql;
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
     * @param int $profileId User making the request
     * @param string $filtersXml Filter for lines and dates in Xml format
     * @return PlanningDataset|null Planning dataset from Xml format
     */
    public function getPlanningDatasets($profileId, $filtersXml)
    {
        $error = null;
        try {
            $sql = "
            declare	@errorMessage nvarchar(max);
            declare @targetsXml xml;

            exec target.GetPlanningDatasetsV1 @UserID = :profileId, @filtersXml = :filtersXml, @targetsXml = @targetsXml OUTPUT, @errorMessage = @errorMessage OUTPUT
            select @errorMessage as error, @targetsXml as targetsXml
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(":profileId", $profileId, PDO::PARAM_INT);
            $stmt->bindValue(":filtersXml", $filtersXml, PDO::PARAM_STR);
            $stmt->execute();
            $sqlResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $error = $sqlResult[0]["error"];
        } catch (\PDOException $ex) {
            $error = $ex->getMessage();
        }
        if (!empty($error)) {
            throw new \Exception($error);
        }
        if (isset($sqlResult[0]["targetsXml"])) {
            $converter = new PlanningDatasetXmlConverter();
            $xml = $sqlResult[0]["targetsXml"];
            $result = $converter->fromXml($xml);
            return $result;
        }
        return null;    

    }
}
