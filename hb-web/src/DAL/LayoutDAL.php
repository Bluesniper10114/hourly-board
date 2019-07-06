<?php
namespace DAL;
use \PDO;
/**
 * Gets information about machines, cells and lines, pplanning datasets
 */
class LayoutDAL extends \Core\Data
{
    /**
     * Gets a list of all machines in the plant
     *
     * @param int $profileId The user making the request
     * @return string
     */
    public function getMachines($profileId)
    {
        $sql =
        "declare @xml xml;
        exec layout.GetProductionLines @userID = $profileId, @xml = @xml OUTPUT
        select @xml as xmlResult
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $xml = isset($result[0]["xmlResult"]) ? $result[0]["xmlResult"] : null;
        return $xml;
    }

    /**
     * Gets planning dataset
     *
     * @param int $profileId The user making the request
     * @return string
     */
    public function getPlanningDataset($profileId)
    {
        $sql =
        "declare @xml xml;
        exec target.GetPlanningDataSets @userID = $profileId, @xml = @xml OUTPUT
        select @xml as xmlResult
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $xml = isset($result[0]["xmlResult"]) ? $result[0]["xmlResult"] : null;
        return $xml;
    }

    
    /**
     * Set dataset On billboard
     *
     * @param int|null $profileId
     * @param int $dailyTargetID
     * @return string|null
     */
    public function setDatasetOnBillboard($profileId, $dailyTargetID)
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
}
