<?php
namespace DAL\Entities;

use Core\Entity;

/**
 * Contains details about a line
 */
class Line extends Entity
{
    /** @var int $id Line id */
    public $id;    

    /** @var string $location Location */
    public $location;

    /** @var string $name Line name */
    public $name;

    /** @var string $description Description */
    public $description;

    /** @var string[] $tags List of tags avaialble for this line */
    public $tags;

    /** @var Cell[] $cells List of cells on this line */
    public $cells = [];

    /**
     * @inheritDoc
     */
    public function typify()
    {
        $this->id = intval($this->id);
        $this->location = strval($this->location);
        $this->name = strval($this->name);
        $this->description = strval($this->description);
        for ($i = 0; $i < count($this->tags); $i++) {
            $this->tags[$i] = strval($this->tags[$i]);
        }
        foreach($this->cells as $cell) {
            $cell->typify();
        }
    }
}

/**
 * A line contains several cells.
 * Each cell has its own machines
 */
class Cell extends Entity
{
    /** @var string $location Location */
    public $location;

    /** @var string $name Line name */
    public $name;

    /** @var string $description Description */
    public $description;

    /** @var Machine[] List of machines */
    public $machines = [];

    public function typify()
    {
        $this->location = strval($this->location);
        $this->name = strval($this->name);
        $this->description = strval($this->description);
        foreach ($this->machines as $machine) {
            $machine->typify();
        }
    }
}

/**
 * A line contains several cells.
 * Each cell has its own machines
 */
class Machine extends Entity
{
    public $name;
    public $description;
    public $reference;
    public $previousMachine;
    public $eol;
    public $type;
    public $capacity;
    public $routing;
    public $location;

    public function typify()
    {
        $this->name = strval($this->name);
        $this->description = strval($this->description);
        $this->reference = strval($this->reference);
        $this->previousMachine = strval($this->previousMachine);
        $this->eol = boolval($this->eol);
        $this->type = strval($this->type);
        $this->capacity = intval($this->capacity);
        $this->routing = intval($this->routing);
        $this->location = strval($this->location);
    }
}
