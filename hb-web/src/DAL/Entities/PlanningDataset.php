<?php
namespace DAL\Entities;

use \Core\Entity;

/**
 * DTO class used to map the XML result of GetPlanningDataset into a php object.
 * Subsequently this object will be transformed into JSON.
 * 
 *
 * <?xml version="1.0" encoding="UTF-8"?>
 * <root>
 *  <timeStamp>2019-02-15 14:08:44.203</timeStamp>
 *  <timeOut>60</timeOut>
 *  <startingWith>2019-02-18 Shift A</startingWith>
 *  <row xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" dailyTargetID="1222" location="TM" tags="AUDI;" open="Yes">
 *     ...
 *  </row>
 * </root>
 */
class PlanningDataset extends Entity
{
    /** @var string $timeStamp Timestamp identifying the request to the server. It is used for conflict resolution. */
    public $timeStamp; 

    /** @var int $timeOut Time out in seconds until the data will be refused from the server, allowing another user to edit. */
    public $timeOut;

    /** @var string $startingWith TBD */
    public $startingWith;

    /** @var PlanningDatasetRow[] $rows Dataset entries line by line */
    public $rows = [];

    /**
     * @inheritDoc
     */
    public function typify() 
    {
        $this->timeOut = intval($this->timeOut);
        $this->timeStamp = strval($this->timeStamp);
        $this->startingWith = strval($this->startingWith);
        foreach($this->rows as $row)
        {
            $row->typify();
        }
    }
}