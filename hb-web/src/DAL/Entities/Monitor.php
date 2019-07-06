<?php
namespace DAL\Entities;
use \Core\Entity;

/**
 * A billboard monitor
 */
class Monitor extends Entity
{
    /**
     * Uniquely identifies a monitor in the DB
     * @var int $monitorId
     */
    public $id = 0;    

    /**
     * The unique IP Address of the monitor
     * @var string $ipAddress
     */
    public $ipAddress;

    /** @var int */
    public $userId;

    /** @var int */
    public $locationId;
    
    /** @var int */
    public $lineId;
    
    /** @var string */
    public $location;

    /** @var string */
    public $description;

    /** @var string */
    public $locationName;

    /** @var string */
    public $lineName;    
    
    /**
     * @inheritDoc
     */
    public function typify() 
    {
        $this->id = intval($this->id);
        $this->ipAddress = strval($this->ipAddress);
        $this->userId = intval($this->userId);
        $this->lineId = intval($this->lineId);
        $this->locationId = intval($this->locationId);
        $this->location = strval($this->location);
        $this->description = strval($this->description);
        $this->locationName = strval($this->locationName);
        $this->lineName = strval($this->lineName);
    }
}