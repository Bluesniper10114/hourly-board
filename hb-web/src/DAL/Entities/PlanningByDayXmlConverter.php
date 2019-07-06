<?php
namespace DAL\Entities;

use \Core\Entity;

/**
 * Transforms an incoming Xml object with daily targets
 * into a php object
 */
class PlanningByDayXmlConverter
{
    /**
     * Transforms the Xml string into a planing by day form object
     * @param string $xml Original xml
     * @return PlanningByDay Resulting planning by day targets 
     */
    public function fromXml($xml)
    {
        $parser = new \SimpleXMLElement($xml);
        $dataset = new PlanningByDay();
        $dataset->timeStamp = strval($parser->targets['timeStamp']);
        $dataset->timeOut = intval($parser->targets['timeOut']);
        $dataset->lineId = intval($parser->targets->forLine['id']);
        $dataset->lineName = strval($parser->targets->forLine['name']);
        $tags = explode(";", $parser->targets->forLine["tags"]);
        $dataset->tags = array_filter($tags); // remove empty entries 
        
        $dataset->shiftCapacity = intval($parser->targets->forLine['shiftCapacity']);
        $dataset->firstOpenShiftLogId = intval($parser->targets->forLine['firstOpenShiftLogId']);
        $dataset->targets = [];
        foreach($parser->targets->forLine->target as $row) {
            $datasetRow = new PlanningByDayTarget();
            $datasetRow->shiftLogId = intval($row['shiftLogId']);
            $datasetRow->day = intval($row['day']);
            $datasetRow->name = strval($row['name']);
            $datasetRow->id = isset($row['id']) ? intval($row['id']) : null;
            $datasetRow->value = empty($row[0]) ? null : intval($row[0]);            
            $dataset->targets[] = $datasetRow;
        }
        $dataset->typify();
        return $dataset;
    }

    /**
     * Transforms a planning by day object into an Xml string
     * @param PlanningByDay $planningByDay Planning by day object to convert 
     * @return string $xml Original xml
     */
    public function toXml($planningByDay)
    {
        $xml = new \SimpleXMLElement("<root></root>");

        $targetsXml = $xml->addChild("targets");
        $targetsXml->addAttribute("timeStamp", $planningByDay->timeStamp);
        $targetsXml->addAttribute("timeOut", $planningByDay->timeOut);

        $forLineXml = $targetsXml->addChild("forLine");
        $forLineXml->addAttribute("id", $planningByDay->lineId);
        $forLineXml->addAttribute("name", $planningByDay->lineName);

        $tags = implode(";", $planningByDay->tags);
        $forLineXml->addAttribute("tags", $tags);
        $forLineXml->addAttribute("shiftCapacity", $planningByDay->shiftCapacity);
        $forLineXml->addAttribute("firstOpenShiftLogId", $planningByDay->firstOpenShiftLogId);
        
        foreach ($planningByDay->targets as $target) {
            $childXml = $forLineXml->addChild("target", $target->value);
            $childXml->addAttribute("shiftLogId", $target->shiftLogId);
            $childXml->addAttribute("day", $target->day);
            $childXml->addAttribute("name", $target->name);
            $childXml->addAttribute("id", $target->id);
        }
        return $xml->asXML();
    }
}
