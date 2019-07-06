<?php
namespace DAL\Entities;
use \Core\Entity;

/**
 * A billboard monitor
 */
class Billboard extends Entity
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
        
    /**
     * @inheritDoc
     */
    public function typify() 
    {
        $this->id = intval($this->id);
        $this->ipAddress = strval($this->ipAddress);
    }
}