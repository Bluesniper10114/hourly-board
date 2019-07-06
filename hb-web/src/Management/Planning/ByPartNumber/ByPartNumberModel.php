<?php
namespace Management\Planning\ByPartNumber;

use DAL\PlanningDAL;

/**
 * Model for ByPartNumber
 */
class ByPartNumberModel extends \Common\Model
{
    /** @var string */
    public $xmlData;

    /** @var string|null */
    public $searchLine = "";

    /** @var string|null */
    public $searchDate = "";

    /** @var array|null */
    public $translationHeader;

    /** @var array|null */
    public $translationError;

    /** @var array */
    public $linesList;

    /** @var PlanningDAL */
    public $dal;

    /**
     *Connects to the DAL and sets title and help
     */
    public function __construct()
    {
        parent::__construct();
        $this->dal = new PlanningDAL();
    }

    /**
     * Loads ByPartNumberModel
     * @param int $profileId id of user
     * @return void
     */
    public function load($profileId)
    {
        $translations = $this->getLocalisationProvider()->getTranslationsForKey("PlanningByPart");
        $this->title = $translations["Title"];
        $this->translationHeader = $translations["HeaderLabels"];
        $this->translationError = $translations["Errors"];
        if (!empty($this->searchLine) && !empty($this->searchDate)) {
            $this->xmlData = $this->dal->getPartNumberDataByLineDate($profileId, $this->searchLine, $this->searchDate);
        } else {
            $this->xmlData = '';
            // load dummy data by id 289
            //$this->xmlData = $this->dal->getByPartNumberData($profileId, 289);
        }

        $this->linesList = $this->dal->getLinesList();
    }

    /**
     * Loads loadByLineDate
     * @param int $profileId id of user
     * @param int $line id of line
     * @param string $date date
     * @return string
     */
    public function loadByLineDate($profileId, $line, $date)
    {
        $data = $this->dal->getPartNumberDataByLineDate($profileId, $line, $date);
        return $data;
    }

    /**
     * Save Daily Planning
     *
     * @param int|null $profileId
     * @param string $xml
     * @return string|null
     */
    public function saveByPartNumber($profileId, $xml)
    {
        $error = $this->dal->savePlanningByPartNumber($profileId, $xml);
        return $error;
    }
    /**
     * Get Part Number Routing for supplied array
     *
     * @param array $rows
     * @return array
     */
    public function updateRoutingByPartNumber($rows)
    {
        $error = '';
        try {
            foreach ($rows as &$row) {
                $result = $this->dal->getTargetByPartNumber($row['partNumber']);
                $row['routing'] = !empty($result) ? $result : 0;
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return ['dataRows' => $rows, 'error' => $error];
    }

    /**
     * Get Part Number Routing By id
     *
     * @param int $id
     * @return array
     */
    public function getRoutingByPartNumber($id)
    {
        $value = '';
        $error = '';
        try {
            $result = $this->dal->getTargetByPartNumber($id);
            $value = !empty($result) ? $result : 0;
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return ['value' => $value, 'error' => $error];
    }
}