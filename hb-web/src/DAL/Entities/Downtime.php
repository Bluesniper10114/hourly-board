<?php
namespace DAL\Entities;
use \Core\Entity;

/**
 * A downtime reason
 */
class Downtime extends Entity
{
    /**
     * @var int $id downtime id inside the hourly interval
     */
    public $id = 0;
    
    /**
     * @var string $machine Machine name
     */
    public $machine;

    /**
     * @var string $timeInterval Friendly time interval: e.g. 15:00 - 16:00
     */
    public $timeInterval;

    /**
     * @var int $totalDuration Downtime duration inside the interval
     */
    public $totalDuration;

    /**
     * @inheritDoc
     */
    public function typify() 
    {
        $this->id = intval($this->id);
        $this->machine = strval($this->machine);
        $this->timeInterval = strval($this->timeInterval);
        $this->totalDuration = intval($this->totalDuration);
    }
}