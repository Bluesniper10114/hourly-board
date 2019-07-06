<?php
namespace DAL\Entities;
use \Core\Entity;

/**
 * A downtime reason
 */
class DowntimeReason extends Entity
{
    /**
     * @var int $id reason id inside the downtime
     */
    public $id = 0;

    /**
     * @var int $downtimeId Links to a downtime
     */
    public $downtimeId = 0;
    
    /**
     * @var string $comment Reason for downtime
     */
    public $comment;

    /**
     * Links the reason to the list of downtimes downloaded at a certain point in time.
     * If two or more users provide reasons simultaneusly for the same time interval,
     * this field will be used to mitigate the conflict.
     * @var string $timeStamp
     */
    public $timeStamp;

    /**
     * @var int $duration Downtime duration inside the interval
     */
    public $duration;

    /**
     * @inheritDoc
     */
    public function typify() 
    {
        $this->id = intval($this->id);
        $this->downtimeId = intval($this->downtimeId);
        $this->comment = strval($this->comment);
        $this->timeStamp = strval($this->timeStamp);
        $this->duration = intval($this->duration);
    }
}