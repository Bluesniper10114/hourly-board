<?php
namespace DAL\Entities;
use \Core\Entity;

/**
 * A billboard monitor
 */
class Hour extends Entity
{
    /**
     * Uniquely identifies an hourly interval
     * @var int $id
     */
    public $id = 0;    

    /**
     * True if the hourly interval was closed
     * @var bool $closed
     */
    public $closed;

    /**
     * True if the hourly interval is the first open interval
     * @var bool $firstOpen
     */
    public $firstOpen;

    /**
     * Friendly string showing interval like 00:00 - 01:00
     * @var string $hourInterval
     */
    public $hourInterval;

    /**
     * Target for the interval
     * @var int $target
     */
    public $target;

    /**
     * Target for the shift up until the end of the interval
     * @var int $cumulativeTarget
     */
    public $cumulativeTarget;

    /**
     * Items operated in the interval
     * @var int $achieved
     */
    public $achieved;

    /**
     * Items operated in the shift up until the current interval
     * @var int $cumulativeAchieved
     */
    public $cumulativeAchieved;

    /**
     * Items with defects in the interval
     * @var int $defects
     */
    public $defects;

    /**
     * Total downtime in minutes inside the interval
     * @var int $achieved
     */
    public $downtime;

    /**
     * Comments used to justify the underperformance inside the interval
     * @var string $comments
     */
    public $comments;

    /**
     * Escalations raised as a plan to counteract the underperformance
     * @var string $escalated
     */
    public $escalations;

    /**
     * Name of the operator signing off the interval
     * @var string $signoff
     */
    public $signoff;
    
    /**
     * @inheritDoc
     */
    public function typify() 
    {
        $this->id = intval($this->id);
        $this->closed = boolval($this->closed);
        $this->firstOpen = boolval($this->firstOpen);
        $this->hourInterval = strval($this->hourInterval);
        $this->target = intval($this->target);
        $this->cumulativeTarget = intval($this->cumulativeTarget);
        $this->achieved = intval($this->achieved);
        $this->cumulativeAchieved = intval($this->cumulativeAchieved);
        $this->defects = intval($this->defects);
        $this->downtime = intval($this->downtime);
        $this->comments = strval($this->comments);
        $this->escalations = strval($this->escalations);        
    }
}