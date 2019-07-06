<?php
namespace DAL\Entities;

use \Core\Entity;

/**
 * DTO class used to map the XML result of Daily planning into a php object.
 * Subsequently this object will be encoded into JSON.
 * 
 * <root>
 *   <targets timeStamp="2019-02-16 23:11:23.173" timeOut="60">
 *      <forLine name="AUDI" tags="AUDI;" shiftCapacity="8066" firstOpenShiftLogId="499" id="1">
 *          ...
 *      </forLine>
 *   </targets>
 * </root>
 *
 */
class PlanningByPartNumber extends Entity
{
    /** @var string $timeStamp Timestamp used for conflict resolution on the server */
    public $timeStamp;

    /** @var int $timeOut Timeout until data can be saved, in seconds */
    public $timeOut;

    /** @var int $lineId Line id */
    public $lineId;

    /** @var int $lineName Line name */
    public $lineName;

    /** @var int $shiftCapacity Cumulated capacity of all machines for this line */
    public $shiftCapacity;

    /** @var string Date for which this plan was retrieved. Format 2019-02-24 */
    public $date;

    /** @var int $firstClosedShiftId ShiftLogId of the first closed shift (date / shift combination). */
    public $firstClosedShiftId;

    /** @var PlanningByPartNumberRow[] $rows Row containing a plan for a single part number over 3 shifts on $this->date */
    public $rows;

    /**
     * @inheritDoc
     */
    public function typify()
    {
        $this->timeStamp = strval($this->timeStamp);
        $this->timeOut = intval($this->timeOut);
        $this->lineId = intval($this->lineId);
        $this->lineName = strval($this->lineName);
        $this->date = strval($this->date);
            
        $this->shiftCapacity = intval($this->shiftCapacity);
        $this->firstClosedShiftId = intval($this->firstClosedShiftId);

        foreach ($this->rows as $row) {
            $row->typify();
        }
    }
}

