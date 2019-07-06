<?php
namespace API\Planning\ByDay;

use DAL\PlanningDALv1;
use DAL\LayoutDALv1;
use DAL\Entities\PlanningByDay;
use DAL\Entities\PlanningByDayJsonConverter;
use DAL\Entities\PlanningByDayXmlConverter;

/**
 * Model for datasets planning
 **/
class ByDayModel extends \Common\Model
{    
    /** @var PlanningDALv1 */
    public $dal;

    /**
     * Gets a list of two weeks for the header of the daily planning
     * @return array List of two weeks 
     */
    public function getWeeks()
    {
        return $this->dal->getWeeks();
    }

    /**
     * Gets the weeks header and the by day plan for a given date interval
     * @param int $profileId
     * @param string $searchTags
     * @return PlanningByDay|null
     */
    public function getByDayPlan($profileId, $searchTags)
    {
        $xmlResult = $this->dal->getByDayPlan($profileId, $searchTags);
        if (is_null($xmlResult)) {
            return null;
        }
        $converter = new PlanningByDayXmlConverter();
        return $converter->fromXml($xmlResult);
    }

    /**
     * Saves a daily plan
     * @param int $profileId User making the request
     * @param string $json Original JSON string
     * @return string|null The error message
     */
    public function saveByDayPlan($profileId, $json)
    {
        $fromJsonConverter = new PlanningByDayJsonConverter();
        $planningByDay = $fromJsonConverter->fromJson($json);
        if (is_null($planningByDay)) {
            throw new \Exception("JSON format unsupported");
        }

        $toXmlConverter = new PlanningByDayXmlConverter();
        $xml = $toXmlConverter->toXml($planningByDay);
        return $this->dal->saveByDayPlan($profileId, $xml);
    }
}