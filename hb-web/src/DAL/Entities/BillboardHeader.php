<?php
namespace DAL\Entities;
use \Core\Entity;

/**
 * A billboard monitor
 */
class BillboardHeader extends Entity
{
    /**
     * The date for which the billboard is shown
     * @var string $date
     */
    public $date;    

    /**
     * Shift name
     * @var string $shift
     */
    public $shift;

    /**
     * Line name
     * @var string $lineName
     */
    public $lineName;

    /**
     * Location name
     * @var string $locationName
     */
    public $locationName;

    /**
     * Max hourly production
     * @var int $maxHourProduction
     */
    public $maxHourProduction;

    /**
     * Delivery Time
     * @var int $deliveryTime
     */
    public $deliveryTime;

    /**
     * Identifies the shift uniquely 
     * @var int $shiftLogSignOffId
     */
    public $shiftLogSignOffId;    
    
    /**
     * @inheritDoc
     */
    public function typify() 
    {
        $this->deliveryTime = intval($this->deliveryTime);
        $this->shiftLogSignOffId = intval($this->shiftLogSignOffId);
        $this->maxHourProduction = intval($this->maxHourProduction);
    }
}