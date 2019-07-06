<?php
namespace DAL\Entities;

use DAL\Entities\PlanningDataset;
use DAL\Entities\PlanningDatasetRow;

/**
 * Transforms an incoming Xml object with planning datasets
 * into a php object
 */
class PlanningDatasetXmlConverter
{
    /**
     * Transforms the Xml string into a planing dataset
     * @return PlanningDataset Resulting planning dataset 
     */
    public function fromXml($xml)
    {
        $parser = new \SimpleXMLElement($xml);
        $dataset = new PlanningDataset();
        $dataset->timeStamp = $parser->timeStamp;
        $dataset->timeOut = $parser->timeOut;
        $dataset->startingWith = $parser->startingWith;
        $dataset->rows = [];

        foreach($parser->row as $row) {

            $datasetRow = new PlanningDatasetRow();
            $datasetRow->lineName = $row->line;
            $datasetRow->date = $row->date;
            $datasetRow->shift = $row->shift;
            $datasetRow->planningType = $row->type;
            $datasetRow->activeOnBillboard = $row->billboard;
            $datasetRow->targetsPerHour = [];
            $datasetRow->targetsPerHour[] = $row->qtyHour_1->__toString(); 
            $datasetRow->targetsPerHour[] = $row->qtyHour_2->__toString(); 
            $datasetRow->targetsPerHour[] = $row->qtyHour_3->__toString(); 
            $datasetRow->targetsPerHour[] = $row->qtyHour_4->__toString(); 
            $datasetRow->targetsPerHour[] = $row->qtyHour_5->__toString(); 
            $datasetRow->targetsPerHour[] = $row->qtyHour_6->__toString(); 
            $datasetRow->targetsPerHour[] = $row->qtyHour_7->__toString(); 
            $datasetRow->targetsPerHour[] = $row->qtyHour_8->__toString();
            $datasetRow->totals = $row->qtyTotal;
            $datasetRow->dailyTargetId = $row["dailyTargetID"]; 
            $datasetRow->location = $row["location"]; 

            $tags = explode(";", $row["tags"]);
            $datasetRow->tags = array_filter($tags); // remove empty entries 
            $datasetRow->open = strval($row["open"]) === "Yes";            
            $dataset->rows[] = $datasetRow;
        }
        $dataset->typify();
        return $dataset;
    }
}