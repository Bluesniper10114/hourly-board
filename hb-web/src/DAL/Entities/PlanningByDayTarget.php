<?php
namespace DAL\Entities;

use \Core\Entity;

/**
 * DTO for an individual target in a shift
 *  <target shiftLogId="583" day="1" name="A"/>
 * or 
 *  <target shiftLogId="592" day="4" name="A" id="1222">333</target>
 */
class PlanningByDayTarget extends Entity
{
    /** @var int $shiftLogId Identifies the date / shift / line combination */
    public $shiftLogId;

    /** @var int $day Day number from 1 to 14. The planning is done in a 2 weeks window */
    public $day;

    /** @var string $name Shift name A/B/C */
    public $name;

    /** @var int|null $id TargetId Identifies a target: (date / shift / line / target type); aka dailyId */
    public $id;

    /** @var int|null $value Target for the shift */
    public $value;

    /**
     * @inheritDoc
     */
    public function typify()
    {
        $this->shiftLogId = intval($this->shiftLogId);
        $this->day = intval($this->day);
        $this->name = strval($this->name);
        if (!is_null($this->id)) {
            $this->id = intval($this->id);
        }
        if (!is_null($this->value)) {
            $this->value = intval($this->value);
        }
    }
}
