<?php
namespace DAL\Entities;

use \Core\Entity;
/**
 * Holds a plan for a single partnumber over 3 shifts
 */
class PlanningByPartNumberRow extends Entity
{
    /** @var int $priority Priority of plan execution. 1 is top priority. */
    public $priority;

    /** @var string $partNumber 8 digit partnumber */    
    public $partNumber;

    /** @var int $initialQuantity Quantity requested by the planner. */
    public $initialQuantity;

    /** @var int $routing Routing minutes needed to produce one such partNumber on single machine */
    public $routing;

    /** @var int $totals Total plan when adding plans from shifts A + B + C */
    public $totals;

    /** @var int $shiftAQuantity Plan for shift A */
    public $shiftAQuantity;

    /** @var int $shiftBQuantity Plan for shift B */
    public $shiftBQuantity;

    /** @var int $shiftCQuantity Plan for shift C */
    public $shiftCQuantity;

    /**
     * @inheritDoc
     */
    public function typify()
    {
        $this->priority = intval($this->priority);
        $this->partNumber = strval($this->partNumber);
        $this->initialQuantity = intval($this->initialQuantity);
        $this->routing = intval($this->routing);
        $this->totals = intval($this->totals);
        $this->shiftAQuantity = intval($this->shiftAQuantity);
        $this->shiftBQuantity = intval($this->shiftBQuantity);
        $this->shiftCQuantity = intval($this->shiftCQuantity);
    }
}
