<?php
namespace DAL\Entities;

use \Core\Entity;

/**
 * DTO class used to map the XML result of PartNumber planning into a php object.
 * Subsequently this object will be encoded into JSON.
 */
class PlanningByPartNumberXmlConverter
{
    /**
     * Transforms the Xml string into a planing by part number dataset
     * @return PlanningByPartNumber Resulting planning by part number dataset 
     */
    public function fromXml($xml)
    {
        $parser = new \SimpleXMLElement($xml);
        $dataset = new PlanningByPartNumber();
        $dataset->timeStamp = $parser->timeStamp;
        $dataset->timeOut = $parser->timeOut;
        $dataset->lineId = $parser->line["id"];
        $dataset->shiftCapacity = $parser->line["shiftCapacity"];
        $dataset->lineName = $parser->line->__toString();
        $dataset->firstClosedShiftId = $parser->firstClosedShift;
        $dataset->date = $parser->date;
        $dataset->rows = [];

        foreach($parser->rows->row as $row) {
            $datasetRow = new PlanningByPartNumberRow();
            $datasetRow->priority = $row["priority"];
            $datasetRow->partNumber = $row->partNumber;
            $datasetRow->initialQuantity = $row->initialQuantity;
            $datasetRow->routing = $row->routing;
            $datasetRow->totals = $row->totals;
            $datasetRow->shiftAQuantity = $row->shifts->shift[0];
            $datasetRow->shiftBQuantity = $row->shifts->shift[1];
            $datasetRow->shiftCQuantity = $row->shifts->shift[2];

            $dataset->rows[] = $datasetRow;
        }
        $dataset->typify();
        return $dataset;
    }

    /**
     * Transforms te planing by part number object into an XML string
     * @param PlanningByPartNumber $planningByPartNumber Planning object
     * @param DatasetDistributionFactoryMethodInterface $datasetDistributionFactoryMethod Distribution method
     * @return string Xml
     */
    public function toXml($planningByPartNumber, $datasetDistributionFactoryMethod)
    {
        $xml = new \SimpleXMLElement("<root></root>");

        $xml->addChild("timeStamp", $planningByPartNumber->timeStamp);
        $xml->addChild("timeOut", $planningByPartNumber->timeOut);
        $lineXml = $xml->addChild("line", $planningByPartNumber->lineName);
        $lineXml->addAttribute("id", $planningByPartNumber->lineId);
        $lineXml->addAttribute("shiftCapacity", $planningByPartNumber->shiftCapacity);
        $xml->addChild("firstClosedShift", $planningByPartNumber->firstClosedShiftId);
        $xml->addChild("date", $planningByPartNumber->date);
        $rowsXml = $xml->addChild("rows");

        $totalsShiftA = 0;
        $totalsShiftB = 0;
        $totalsShiftC = 0;

        foreach($planningByPartNumber->rows as $row) {
            $rowXml = $rowsXml->addChild("row");
            $rowXml->addAttribute("priority", $row->priority);
            $rowXml->addChild("partNumber", $row->partNumber);
            $rowXml->addChild("initialQuantity", $row->initialQuantity);
            $rowXml->addChild("routing", $row->routing);
            $rowXml->addChild("totals", $row->totals);
            $shiftsXml = $rowXml->addChild("shifts");

            //shift A
            $shiftAXml = $shiftsXml->addChild("shift", $row->shiftAQuantity);
            $shiftAXml->addAttribute("name", "A");
            $totalsShiftA += $row->shiftAQuantity;

            // shift B
            $shiftBXml = $shiftsXml->addChild("shift", $row->shiftBQuantity);
            $shiftBXml->addAttribute("name", "B");
            $totalsShiftB += $row->shiftBQuantity;

            // shift C
            $shiftCXml = $shiftsXml->addChild("shift", $row->shiftCQuantity);
            $shiftCXml->addAttribute("name", "C");
            $totalsShiftC += $row->shiftCQuantity;
        }
        
        $datasetXml = $xml->addChild("dataset");
        $shiftXml = $datasetXml->addChild("shift");
        $shiftXml->addAttribute("name", "A");
        $shiftXml->addAttribute("dailyID", "1252");
        $values = $datasetDistributionFactoryMethod->generateHourlyDataset($totalsShiftA);
        $this->createTags($values, $shiftXml);

        $shiftXml = $datasetXml->addChild("shift");
        $shiftXml->addAttribute("name", "B");
        $shiftXml->addAttribute("dailyID", "1253");
        $values = $datasetDistributionFactoryMethod->generateHourlyDataset($totalsShiftB);
        $this->createTags($values, $shiftXml);

        $shiftXml = $datasetXml->addChild("shift");
        $shiftXml->addAttribute("name", "C");
        $shiftXml->addAttribute("dailyID", "1254");
        $values = $datasetDistributionFactoryMethod->generateHourlyDataset($totalsShiftC);
        $this->createTags($values, $shiftXml);

        $xmlString = $xml->asXML();
        return $xmlString;
    }

    
    /**
     * Generates the dataset tags from an array of values
     * @param int[] $values 8 values
     * @param \SimpleXMLElement $xmlTag Base tag to connect to
     * @return void
     */
    protected function createTags($values, $xmlTag)
    {
        for ($i = 0; $i < 8; $i++) {
            $tag = $xmlTag->addChild("hour", $values[$i]);
            $tag->addAttribute("interval", $i + 1);
        }
    }
}

