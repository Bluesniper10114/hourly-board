<?php
namespace API\Planning\ByPartNumber;

use DAL\Entities\LineXmlConverter;
use DAL\PlanningDALv1;
use DAL\Entities\PlanningByPartNumberJsonConverter;
use DAL\Entities\PlanningByPartNumberXmlConverter;
use DAL\Entities\PlanningByPartNumber;
use DAL\Entities\DatasetStandardDistributionFactoryMethod;


class ByPartNumberModel extends \Common\Model
{
    /** @var PlanningDALv1 $dal The DAL object */
    public $dal;

    /**
     * Gest the routing in minutes for a part number
     * @param string $partNumber Part number 8 digits, not index
     * @return int|null Routing in minutes
     */
    public function getRouting($partNumber)
    {
        $routing = $this->dal->getRouting($partNumber);
        return intval($routing);
    }

    /**
     * Gets the part number plan dataset
     * 
     * @param int $profileId User making the request
     * @param int $lineId Line id
     * @param string $date Date for which the plan is built
     * @return PlanningByPartNumber|null
     */
    public function getPartNumberPlan($profileId, $lineId, $date)
    {
        $xmlResult = $this->dal->getPartNumberPlan($profileId, $lineId, $date);
        if (!isset($xmlResult)) {
            return null;
        }
        $converter = new PlanningByPartNumberXmlConverter();
        $result = $converter->fromXml($xmlResult);
        return $result; 
    }

    /**
     * Saves the part number plan from a JSON
     * 
     * @param int $profileId 
     * @param string $json 
     * @return string|null The error message
     */
    public function savePartNumberPlan($profileId, $json)
    {
        $fromJsonConverter = new PlanningByPartNumberJsonConverter();
        $planningByPartNumber = $fromJsonConverter->fromJson($json);
        if (is_null($planningByPartNumber)) {
            throw new \Exception("JSON format unsupported");
        }

        $toXmlConverter = new PlanningByPartNumberXmlConverter();
        $datasetGenerator = new DatasetStandardDistributionFactoryMethod();
        $xml = $toXmlConverter->toXml($planningByPartNumber, $datasetGenerator);
        return $this->dal->saveByDayPlan($profileId, $xml);
    }
}