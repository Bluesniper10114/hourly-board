<?php
namespace API\Planning\Datasets;
use DAL\LayoutDALv1;
use DAL\Entities\PlanningDataset;
use DateInterval;
use DateTime;

/**
* Model for datasets planning
*/
class DatasetsModel extends \Common\Model
{
    /** @var LayoutDALv1 */
    public $dal;

    /**
     * @inheritDoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->dal = new LayoutDALv1();
    }

    /**
     * Gets dates for +- 2 weeks from today
     * @return string[] Array of date strings in the format '2019-12-24'
     */
    public function getDates()
    {
        $dates = [];
        $date = new DateTime("now");
        $current = $date->sub(new DateInterval("P14D"));

        for ($i=0; $i < 28; $i++) { 
            $dates[] = $current->format("Y-m-d");
            $current = $current->add(new DateInterval("P1D"));
        }
        return $dates;
    }

    /**
     * Activates a planning dataset which will be visible on the billboard
     * Note: there are two datasets possible: daily or partnumber datasets 
     * 
     * @param int $profileId User making the request
     * @param int $dailyTargetId Specific date / shift combination
     * @return string|null Error message if any
     */
    public function activateDatasetOnBillboard($profileId, $dailyTargetId)
    {
        return $this->dal->activateDatasetOnBillboard($profileId, $dailyTargetId);
    }

    /**
     * Gets the datasets with plans for specific lines and dates
     * 
     * @param int $profileId User making the request
     * @param int[] $lines Ids of lines for which datasets are requested
     * @param string[] $dates Dates for which datasets are requested (format 2019-02-24)
     * @param string[] $shifts Array of shifts A/B/C
     * @return PlanningDataset|null Planning datasets
     */
    public function getPlanningDatasets($profileId, $lines, $dates, $shifts)
    {
        $xml = new \SimpleXMLElement('<root/>');
        $linesXml = $xml->addChild("lines");
        foreach ($lines as $line) {
            $linesXml->addChild("line", $line);
        }
        $datesXml = $xml->addChild("dates");
        foreach ($dates as $date) {
            $datesXml->addChild("date", $date);
        }
        $datesXml = $xml->addChild("shifts");
        foreach ($shifts as $shift) {
            $datesXml->addChild("shift", $shift);
        }
        return $this->dal->getPlanningDatasets($profileId, $xml->asXML());
    }
}