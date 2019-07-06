<?php
namespace DAL\Entities;

use \Core\Entity;

class Week extends Entity
{
    /** @var int $id Week number in the year */
    public $id;
    
    /** @var string $start Start date for the week */
    public $start;

    /** @var string $end End date for the week */
    public $end;

    /**
     * @inheritDoc
     */
    public function typify() 
    {
        $this->id = intval($this->id);
        $this->start = strval($this->start);
        $this->end = strval($this->end);
    }


}