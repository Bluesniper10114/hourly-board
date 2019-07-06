<?php
namespace DAL;

use PDO;
/**
 * Users DAL class
 **/
class BreaksDAL extends \Core\Data
{
    /**
     * Gets Breaks
     *
     * @param int|null $profileId Profile of user attempting the load
     * @param int|null $nextWeek 1 if request wants data for next week, 0 for current week
     * @return string|null
     **/
    public function getBreaks($profileId, $nextWeek)
    {
        $sql = "
            declare @xml xml;
            exec dbo.GetBreaks @userID = $profileId, @NextWeek = $nextWeek, @xml = @xml OUTPUT
            select @xml as xmlResult
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $xml = isset($result[0]["xmlResult"]) ? $result[0]["xmlResult"] : null;
        return $xml;
    }

    /**
     * Save breaks
     *
     * @param int|null $profileId Profile of user attempting the load
     * @param string $xml
     * @return array result and error
     */
    public function saveBreaks($profileId, $xml)
    {

        $result = ['success' => true];
        try {
            $sql = "
            DECLARE	@return_value int,
            @errorMessage nvarchar(max);
            exec dbo.SaveBreaks @userId = $profileId, @xml = '$xml', @errorMessage = @errorMessage OUTPUT
            select @return_value as return_value,  @errorMessage as error
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $sqlResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $returnValue = isset($sqlResult[0]["return_value"]) ? $sqlResult[0]["return_value"] : 0;
            if ($returnValue < 0) {
                $result['error'] = $sqlResult[0]["error"];
                $result['success'] = false;
            }
        } catch (\PDOException $ex) {
            $result['error'] = $ex->getMessage();
            $result['success'] = false;
        }
        return $result;
    }


}