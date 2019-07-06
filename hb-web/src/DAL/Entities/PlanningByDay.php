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
class PlanningByDay extends Entity
{
    /** @var string $timeStamp Timestamp used for conflict resolution on the server */
    public $timeStamp;

    /** @var int $timeOut Timeout until data can be saved, in seconds */
    public $timeOut;

    /** @var int $lineId Line id */
    public $lineId;

    /** @var int $lineName Line name */
    public $lineName;

    /** @var string[] $tags Tags to which this line belongs to */
    public $tags;

    /** @var int $shiftCapacity Cumulated capacity of all machines for this line */
    public $shiftCapacity;

    /** @var int $firstOpenShiftLogId Id of the first open shift log (date / shift combination). */
    public $firstOpenShiftLogId;

    /** @var PlanningByDayTarget[] $targets Individual targets for each shift */
    public $targets;

    /**
     * @inheritDoc
     */
    public function typify()
    {
        $this->timeStamp = strval($this->timeStamp);
        $this->timeOut = intval($this->timeOut);
        $this->lineId = intval($this->lineId);
        $this->lineName = strval($this->lineName);
            
        $this->shiftCapacity = intval($this->shiftCapacity);
        $this->firstOpenShiftLogId = intval($this->firstOpenShiftLogId);

        foreach ($this->targets as $target) {
            $target->typify();
        }
    }
}
