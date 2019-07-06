<?php
namespace DAL;
use PDO;

/**
 *
 * Setting DAL class
 */
class SettingsDAL extends \Core\Data
{
    /**
     *
     * get list of application settings
     * @return array
     */
    public function getListOfSettings()
    {
        $sql = "SELECT [Key]
            ,[Value]
            ,[Note]
        FROM [global].[Setting] s";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     *
     * update setting
     * @param string $key
     * @param string $value
     * @param string $note
     * @return void
     */
    public function updateSettings($key, $value, $note)
    {
        $data = ['value' => $value, 'key' => $key, 'note' => $note];
        $sql = "UPDATE [global].[Setting] SET [value]=:value , [note]=:note WHERE [key]=:key";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
    }

    /**
     *
     * get setting for a key
     * @param string $key
     * @return string or null
     */
    public function getSettingForKey($key)
    {
        $sql = "SELECT [Key]
                ,[Value]
                ,[Note]
        FROM [global].[Setting]
        WHERE [key]='" . $key . "'";
        $stmt = $this->db->query($sql);
        $setting = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return isset($setting[0]) ? $setting[0]['Value'] : null;
    }

    /**
     * Returns the Version and ID columns from the latest row of the [dbo].[Version] table
     * @return array id, Version
     **/
    public function getDatabaseVersionInfo()
    {
        $sql = "SELECT top 1 id, [Version] FROM ver.Version ORDER BY id DESC";
        $stmt = $this->db->query($sql);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (isset($result[0])){
            return $result[0];
        }
        return ["Version" => 'unknown', "id" => 0];
    }
    /**
     *
     * Get routing list
     * @param array $filterList
     * @param string $orderByField
     * @param string $orderDirection
     *
     * @return array
     */
    public function getRoutingsList($filterList, $orderByField, $orderDirection)
    {
        $addFilter = "1=1";
        $params = [];
        $orderString = empty($orderByField) ? '' : "ORDER BY $orderByField $orderDirection";
        if (!empty($filterList)){
            foreach ($filterList as $key => $value) {
                if (empty($value)){
                    continue;
                }
                $addFilter .= " AND $key like :$key";
                $params[$key] = "%" . $value . "%";
            }
        }
        $sql = "SELECT [ID]
                    ,[PartNumber]
                    ,[Description]
                    ,[Routing]
                FROM [layout].[PartNumber] WHERE $addFilter $orderString";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}